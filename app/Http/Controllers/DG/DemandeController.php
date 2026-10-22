<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DemandeController extends Controller
{
    private function mapStatusToUi(?string $status): string
    {
        return match ($status) {
            'pending' => 'en_attente',
            'approved' => 'approuvee',
            'rejected' => 'rejetee',
            'completed' => 'terminee',
            default => $status ?: 'en_attente',
        };
    }

    private function mapStatusFromUi(?string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'pending',
            'approuvee' => 'approved',
            'rejetee' => 'rejected',
            'terminee' => 'completed',
            // Support UI value "en_cours" even if DB doesn't; keep as pending for now.
            'en_cours' => 'pending',
            default => $statut ?: 'pending',
        };
    }

    public function index()
    {
        try {
            $demandes = PublicRequest::latest()->paginate(10);

            // Adapter le statut pour les vues DG (qui attendent en_attente/approuvee/rejetee/terminee)
            $demandes->getCollection()->transform(function ($demande) {
                $demande->status = $this->mapStatusToUi($demande->status);
                return $demande;
            });

            $stats = [
                "total" => PublicRequest::count(),
                "en_attente" => PublicRequest::where("status", "pending")->count(),
                "approuvees" => PublicRequest::where("status", "approved")->count(),
                "rejetees" => PublicRequest::where("status", "rejected")->count(),
            ];

            return view("dg.demandes.index", compact("demandes", "stats"));

        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement des demandes DG", [
                "error" => $e->getMessage()
            ]);

            return redirect()->back()->with("error", "Erreur lors du chargement des demandes");
        }
    }

    public function show($id)
    {
        try {
            $demande = PublicRequest::with(['assignedTo'])->findOrFail($id);
            $demande->status = $this->mapStatusToUi($demande->status);
            return view("dg.demandes.show", compact("demande"));

        } catch (\Exception $e) {
            return redirect()->back()->with("error", "Demande non trouvée");
        }
    }

    public function update(Request $request, $id)
    {
        // Accepter "statut" (vues index/show) ou "status" (anciens appels JS)
        if (!$request->filled('statut') && $request->filled('status')) {
            $request->merge(['statut' => $request->input('status')]);
        }
        if (!$request->filled('commentaire_admin') && $request->filled('admin_comment')) {
            $request->merge(['commentaire_admin' => $request->input('admin_comment')]);
        }

        try {
            $demande = PublicRequest::findOrFail($id);

            $request->validate([
                "statut" => "required|in:en_attente,en_cours,approuvee,rejetee,terminee",
                "commentaire_admin" => "nullable|string|max:1000"
            ]);

            $actor = auth()->user();
            $comment = $request->commentaire_admin;
            $service = app(\App\Services\RequestWorkflowService::class);
            $uiStatus = $request->statut;

            // Toute décision DG passe par le workflow officiel (trace + règles)
            switch ($uiStatus) {
                case 'approuvee':
                    $demande = $service->dgValidate($demande, true, $comment, $actor);
                    break;
                case 'rejetee':
                    if (empty(trim((string) $comment))) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'commentaire_admin' => 'Un motif est obligatoire pour rejeter une demande.',
                        ]);
                    }
                    $demande = $service->dgValidate($demande, false, $comment, $actor);
                    break;
                case 'en_cours':
                case 'en_attente':
                    $demande = $service->advance($demande, 'en_revue', $comment, $actor);
                    break;
                case 'terminee':
                    $demande = $service->advance($demande, 'cloturee', $comment, $actor);
                    break;
            }

            $demande->update([
                "admin_comment" => $comment ?? $demande->admin_comment,
                "assigned_to" => $demande->assigned_to ?: $actor->id,
            ]);

            Log::info("Décision DG enregistrée via workflow", [
                "demande_id" => $id,
                "statut" => $uiStatus,
                "workflow_status" => $demande->workflow_status,
                "dg_id" => $actor->id
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Décision enregistrée avec succès',
                    'workflow_status' => $demande->workflow_status,
                ]);
            }

            return redirect()->back()->with("success", "Demande mise à jour avec succès");

        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Action impossible';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->back()->withInput()->with('error', $message);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour de la demande DG", [
                "demande_id" => $id,
                "error" => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
            }
            return redirect()->back()->with("error", "Erreur lors de la mise à jour");
        }
    }
}
