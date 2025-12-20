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
        try {
            $demande = PublicRequest::findOrFail($id);
            
            $request->validate([
                "statut" => "required|in:en_attente,en_cours,approuvee,rejetee,terminee",
                "commentaire_admin" => "nullable|string|max:1000"
            ]);

            $dbStatus = $this->mapStatusFromUi($request->statut);
            
            $demande->update([
                "status" => $dbStatus,
                "admin_comment" => $request->commentaire_admin,
                "assigned_to" => $demande->assigned_to ?: auth()->id(),
                "processed_date" => in_array($dbStatus, ['approved', 'rejected', 'completed']) ? now()->toDateString() : $demande->processed_date,
            ]);
            
            Log::info("Demande mise à jour par DG", [
                "demande_id" => $id,
                "statut" => $request->statut,
                "dg_id" => auth()->id()
            ]);
            
            return redirect()->back()->with("success", "Demande mise à jour avec succès");
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour de la demande DG", [
                "demande_id" => $id,
                "error" => $e->getMessage()
            ]);
            
            return redirect()->back()->with("error", "Erreur lors de la mise à jour");
        }
    }
}