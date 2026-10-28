<?php

namespace App\Http\Controllers\Comptable;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Services\Comptable\ComptableMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function __construct(
        private readonly ComptableMovementService $service,
    ) {}

    /** La colonne status n'existe qu'après la migration 2026_10_21_010000. */
    private function hasValidationColumns(): bool
    {
        return Schema::hasColumn('stock_movements', 'status');
    }

    private function filters(Request $request): array
    {
        return $request->only(['status', 'warehouse_id', 'type', 'linked', 'from', 'to', 'q']);
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        if (!$this->hasValidationColumns()) {
            unset($filters['status']);
        }

        $mouvements = $this->service->filteredMovementsQuery($filters)
            ->paginate(20)->withQueryString();
        $warehouses = \App\Models\Warehouse::orderBy('name')->get(['id', 'name']);

        $stats = ['draft' => null, 'validated' => null, 'rejected' => null];
        if ($this->hasValidationColumns()) {
            $stats = $this->service->statusCounts();
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

        $result = $this->service->validate(StockMovement::findOrFail($id), Auth::user());

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
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

        $result = $this->service->reject(
            StockMovement::findOrFail($id),
            $request->rejection_reason,
            Auth::user()
        );

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Export CSV du journal des mouvements (mêmes filtres que la liste).
     * Encodage UTF-8 avec BOM pour une ouverture directe dans Excel.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $rows = $this->service->filteredMovementsQuery($this->filters($request))
            ->limit(10000)
            ->get();

        $filename = 'mouvements-comptable-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($out, [
                'ID', 'Référence', 'Date', 'Magasin', 'Type', 'Produit',
                'Quantité', 'Unité', 'Stock avant', 'Stock après',
                'Statut', 'Saisi par', 'Validé par', 'Validé le',
                'Motif de rejet', 'Mission liée',
            ], ';');

            foreach ($rows as $m) {
                fputcsv($out, [
                    $m->id,
                    $m->reference,
                    $m->created_at?->format('d/m/Y H:i'),
                    $m->warehouse?->name,
                    $m->type,
                    $m->produit,
                    (float) $m->quantity,
                    $m->unite,
                    $m->quantity_before !== null ? (float) $m->quantity_before : null,
                    $m->quantity_after !== null ? (float) $m->quantity_after : null,
                    $m->status ?? 'draft',
                    $m->creator?->name,
                    $m->validated_by,
                    $m->validated_at?->format('d/m/Y H:i'),
                    $m->rejection_reason,
                    $m->request_mission_id,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
