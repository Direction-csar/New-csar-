<?php

namespace App\Http\Controllers\Comptable;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Portail Comptable des matières : validation comptable des mouvements
 * de stock saisis par les magasiniers (web + mobile), en particulier les
 * justificatifs rattachés aux missions de sortie de stock du DTL.
 *
 * Passage de statut : draft (ou NULL) → validated | rejected.
 * Un mouvement validé est gelé ; un rejeté exige un motif.
 */
class MouvementController extends Controller
{
    /** La colonne status n'existe qu'après la migration 2026_10_21_010000. */
    private function hasValidationColumns(): bool
    {
        return Schema::hasColumn('stock_movements', 'status');
    }

    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse:id,name', 'creator:id,name,role'])
            ->orderBy('created_at', 'desc');

        if ($this->hasValidationColumns()) {
            $status = $request->get('status', 'draft');
            if (in_array($status, ['draft', 'validated', 'rejected'], true)) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('type')) {
            $query->whereIn('type', [$request->type, $request->type === 'exit' ? 'out' : $request->type]);
        }
        if ($request->filled('linked')) {
            $request->linked === 'mission'
                ? $query->whereNotNull('request_mission_id')
                : $query->whereNull('request_mission_id');
        }

        $mouvements = $query->paginate(20)->withQueryString();
        $warehouses = \App\Models\Warehouse::orderBy('name')->get(['id', 'name']);

        $stats = ['draft' => null, 'validated' => null, 'rejected' => null];
        if ($this->hasValidationColumns()) {
            $stats = [
                'draft'     => StockMovement::where('status', 'draft')->count(),
                'validated' => StockMovement::where('status', 'validated')->count(),
                'rejected'  => StockMovement::where('status', 'rejected')->count(),
            ];
        }

        return view('comptable.mouvements.index', [
            'mouvements' => $mouvements,
            'warehouses' => $warehouses,
            'stats'      => $stats,
            'validationEnabled' => $this->hasValidationColumns(),
            'direction'  => 'comptable',
        ]);
    }

    public function show($id)
    {
        $mouvement = StockMovement::with([
            'warehouse:id,name,region',
            'creator:id,name,role',
        ])->findOrFail($id);

        $mission = null;
        if ($this->hasValidationColumns() && Schema::hasColumn('stock_movements', 'request_mission_id')) {
            $mission = $mouvement->request_mission_id
                ? \App\Models\RequestMission::with('request:id,tracking_code,full_name')->find($mouvement->request_mission_id)
                : null;
        }

        return view('comptable.mouvements.show', [
            'mouvement' => $mouvement,
            'mission'   => $mission,
            'validationEnabled' => $this->hasValidationColumns(),
            'direction' => 'comptable',
        ]);
    }

    /** Valider comptablement le mouvement (gel de l'écriture). */
    public function validateMovement($id)
    {
        if (!$this->hasValidationColumns()) {
            return redirect()->back()->with('error', 'Colonnes de validation absentes : exécutez les migrations.');
        }

        $mouvement = StockMovement::findOrFail($id);

        if ($mouvement->status === 'validated') {
            return redirect()->back()->with('error', 'Mouvement déjà validé.');
        }

        DB::transaction(function () use ($mouvement) {
            $mouvement->update([
                'status'           => 'validated',
                'validated_by'     => Auth::id(),
                'validated_at'     => now(),
                'rejection_reason' => null,
            ]);

            $this->logMissionValidation($mouvement, 'Justificatif validé');
        });

        return redirect()->back()->with('success', "Mouvement {$mouvement->reference} validé comptablement.");
    }

    /** Rejeter le mouvement avec un motif obligatoire. */
    public function reject(Request $request, $id)
    {
        if (!$this->hasValidationColumns()) {
            return redirect()->back()->with('error', 'Colonnes de validation absentes : exécutez les migrations.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ]);

        $mouvement = StockMovement::findOrFail($id);

        if ($mouvement->status === 'validated') {
            return redirect()->back()->with('error', 'Un mouvement validé ne peut pas être rejeté.');
        }

        DB::transaction(function () use ($mouvement, $request) {
            $mouvement->update([
                'status'           => 'rejected',
                'validated_by'     => Auth::id(),
                'validated_at'     => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            $this->logMissionValidation($mouvement, 'Justificatif rejeté', $request->rejection_reason);
        });

        return redirect()->back()->with('success', "Mouvement {$mouvement->reference} rejeté.");
    }

    /** Trace la décision comptable dans l'historique workflow de la demande liée. */
    private function logMissionValidation(StockMovement $mouvement, string $action, ?string $reason = null): void
    {
        try {
            $request = null;
            if ($mouvement->public_request_id) {
                $request = \App\Models\PublicRequest::find($mouvement->public_request_id);
            }

            $comment = "Mouvement {$mouvement->reference} par " . Auth::user()->name
                . ($reason ? " — Motif : {$reason}" : '');

            $request?->logWorkflowAction($action, $comment, Auth::id());
        } catch (\Exception $e) {
            Log::warning('Traçabilité validation comptable échouée: ' . $e->getMessage());
        }
    }
}
