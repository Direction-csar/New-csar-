<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DemandeController extends Controller
{
    private const WORKFLOW_STATUSES = [
        'soumise', 'en_revue', 'document_attente', 'signee', 'scannee',
        'validee_dg', 'approuvee', 'rejetee', 'cloturee',
    ];

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

    public function index(Request $request)
    {
        try {
            $query = PublicRequest::query()->latest();

            if ($search = trim((string) $request->query('q', ''))) {
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            }
            if (in_array($workflow = $request->query('workflow_status'), self::WORKFLOW_STATUSES, true)) {
                $query->where('workflow_status', $workflow);
            }
            if (in_array($urgency = $request->query('urgency'), ['faible', 'moyenne', 'haute'], true)) {
                $query->where('urgency', $urgency);
            }
            if (in_array($type = $request->query('type'), ['aide_alimentaire', 'aide_urgence', 'autre'], true)) {
                $query->where('type', $type);
            }

            $demandes = $query->paginate(15)->withQueryString();

            $stats = [
                "total" => PublicRequest::count(),
                "en_attente" => PublicRequest::whereIn('workflow_status', ['soumise', 'en_revue', 'document_attente', 'signee', 'scannee'])->count(),
                "a_decider" => PublicRequest::where('workflow_status', 'scannee')->count(),
                "approuvees" => PublicRequest::whereIn('workflow_status', ['validee_dg', 'approuvee', 'cloturee'])->count(),
                "rejetees" => PublicRequest::where('workflow_status', 'rejetee')->count(),
            ];

            $filters = [
                'q' => (string) $request->query('q', ''),
                'workflow_status' => $workflow,
                'urgency' => $urgency,
                'type' => $type,
            ];

            return view("dg.demandes.index", compact("demandes", "stats", "filters"));

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
                "commentaire_admin" => $request->input('statut') === 'rejetee'
                    ? 'required|string|min:10|max:1000'
                    : 'nullable|string|max:1000',
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

    public function uploadSignature(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'signature_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'comment' => 'nullable|string|max:2000',
        ]);

        return $this->recordDocument($request, (int) $id, 'signature_file', 'signee', 'dg_signature_file', 'demandes/signatures', 'Document signé déposé par la Direction Générale');
    }

    public function uploadScan(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'scan_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'comment' => 'nullable|string|max:2000',
        ]);

        return $this->recordDocument($request, (int) $id, 'scan_file', 'scannee', 'scan_file', 'demandes/scans', 'Scan déposé par la Direction Générale');
    }

    /**
     * Dépose une pièce et fait avancer le workflow vers l'étape cible.
     * Les droits sont vérifiés par RequestWorkflowService ; la pièce et la
     * transition sont enregistrées dans une même transaction, et le fichier
     * est supprimé si la transition est refusée.
     */
    private function recordDocument(Request $request, int $id, string $input, string $target, string $column, string $directory, string $defaultComment): RedirectResponse
    {
        $actor = auth()->user();
        $service = app(RequestWorkflowService::class);

        if (!$service->canPerform($actor, $target)) {
            abort(403);
        }

        $demande = PublicRequest::findOrFail($id);

        $path = $service->findPath($demande->workflow_status, $target);
        if ($path === null) {
            return redirect()->back()->with('error', 'Impossible de passer cette demande au statut requis depuis son statut actuel.');
        }

        $stored = $request->file($input)->store($directory, 'public');
        $comment = $request->filled('comment') ? trim((string) $request->comment) : $defaultComment;

        try {
            DB::transaction(function () use (&$demande, $service, $actor, $column, $stored, $path, $target, $comment) {
                $demande->update([$column => $stored]);
                foreach ($path as $step) {
                    $demande = $service->advance($demande, $step, $step === $target ? $comment : null, $actor);
                }
            });
        } catch (ValidationException $e) {
            Storage::disk('public')->delete($stored);

            return redirect()->back()->with('error', collect($e->errors())->flatten()->first() ?? 'Action impossible');
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($stored);
            throw $e;
        }

        Log::info('Pièce DG enregistrée', ['demande_id' => $id, 'column' => $column, 'dg_id' => $actor->id]);

        return redirect()->back()->with('success', 'Document enregistré et workflow mis à jour');
    }
}
