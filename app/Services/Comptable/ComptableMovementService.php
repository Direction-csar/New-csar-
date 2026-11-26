<?php

namespace App\Services\Comptable;

use App\Models\EquipmentAssignment;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Règles métier « Comptabilité des matières » partagées entre le portail
 * web /comptable (session) et l'API mobile /api/comptable/v1 (Sanctum) :
 * décisions comptables, agrégats de tableau de bord et soldes carburant.
 * Une seule source de vérité — aucune duplication web/API.
 */
class ComptableMovementService
{
    /**
     * Valider comptablement un mouvement (draft → validated, gel).
     * @return array{ok: bool, message: string}
     */
    public function validate(StockMovement $mouvement, User $actor): array
    {
        if ($mouvement->status === 'validated') {
            return ['ok' => false, 'message' => 'Mouvement déjà validé.'];
        }
        if ($mouvement->status === 'rejected') {
            return ['ok' => false, 'message' => 'Un mouvement rejeté ne peut pas être validé.'];
        }

        DB::transaction(function () use ($mouvement, $actor) {
            $mouvement->update([
                'status'           => 'validated',
                'validated_by'     => $actor->id,
                'validated_at'     => now(),
                'rejection_reason' => null,
            ]);
            $this->logDecision($mouvement, 'Justificatif validé', null, $actor);
        });

        return ['ok' => true, 'message' => "Mouvement {$mouvement->reference} validé comptablement."];
    }

    /**
     * Rejeter un mouvement (draft → rejected) ; motif ≥ 10 car. obligatoire.
     * @return array{ok: bool, message: string}
     */
    public function reject(StockMovement $mouvement, string $reason, User $actor): array
    {
        if ($mouvement->status === 'rejected') {
            return ['ok' => false, 'message' => 'Mouvement déjà rejeté.'];
        }
        if ($mouvement->status === 'validated') {
            return ['ok' => false, 'message' => 'Un mouvement validé ne peut pas être rejeté.'];
        }

        DB::transaction(function () use ($mouvement, $reason, $actor) {
            $mouvement->update([
                'status'           => 'rejected',
                'validated_by'     => $actor->id,
                'validated_at'     => now(),
                'rejection_reason' => $reason,
            ]);
            $this->logDecision($mouvement, 'Justificatif rejeté', $reason, $actor);
        });

        return ['ok' => true, 'message' => "Mouvement {$mouvement->reference} rejeté."];
    }

    /**
     * Requête de liste filtrée, identique web et API :
     * status, warehouse_id, type, linked, from/to, q (référence/motif).
     */
    public function filteredMovementsQuery(array $filters): Builder
    {
        $query = StockMovement::with(['warehouse:id,name', 'creator:id,name,role'])
            ->orderBy('created_at', 'desc');

        $status = $filters['status'] ?? null;
        if (in_array($status, ['draft', 'validated', 'rejected'], true)) {
            $query->where('status', $status);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['type'])) {
            $types = ['exit' => ['exit', 'out'], 'entry' => ['entry', 'in']];
            $query->whereIn('type', $types[$filters['type']] ?? [$filters['type']]);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        if (!empty($filters['q'])) {
            $like = '%' . $filters['q'] . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('reference', 'like', $like)
                    ->orWhere('reason', 'like', $like);
            });
        }
        if (!empty($filters['linked'])) {
            $filters['linked'] === 'mission'
                ? $query->whereNotNull('request_mission_id')
                : $query->whereNull('request_mission_id');
        }

        return $query;
    }

    /** Compteurs par statut (tableau de bord web + API). */
    public function statusCounts(): array
    {
        return [
            'draft'     => StockMovement::where('status', 'draft')->count(),
            'validated' => StockMovement::where('status', 'validated')->count(),
            'rejected'  => StockMovement::where('status', 'rejected')->count(),
        ];
    }

    /**
     * Magasins sous le seuil d'alerte (20 % de la capacité).
     */
    public function lowStockWarehouses(int $thresholdPercent = 20): Collection
    {
        return Warehouse::orderBy('name')
            ->get(['id', 'name', 'region', 'capacity', 'current_stock'])
            ->filter(fn ($w) => $w->capacity > 0 && ($w->current_stock / $w->capacity) < ($thresholdPercent / 100))
            ->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'region' => $w->region,
                'current_stock' => $w->current_stock,
                'capacity' => $w->capacity,
                'ratio' => round($w->current_stock / max($w->capacity, 1) * 100, 1),
            ])
            ->values();
    }

    /**
     * Soldes carburant : lignes stock_movements dont unite = 'L',
     * agrégées par produit et magasin (entrées in/entry, sorties out/exit).
     */
    public function fuelSummary(): Collection
    {
        $rows = StockMovement::selectRaw(
                "COALESCE(NULLIF(produit,''),'Carburant') as produit,
                 warehouse_id,
                 SUM(CASE WHEN type IN ('in','entry') THEN quantity ELSE 0 END) as total_in,
                 SUM(CASE WHEN type IN ('out','exit') THEN quantity ELSE 0 END) as total_out"
            )
            ->where('unite', 'L')
            ->groupBy('produit', 'warehouse_id')
            ->get();

        $warehouses = Warehouse::whereIn('id', $rows->pluck('warehouse_id')->filter()->unique())
            ->pluck('name', 'id');

        return $rows->map(fn ($r) => [
            'produit' => $r->produit,
            'warehouse_id' => $r->warehouse_id,
            'warehouse_name' => $warehouses[$r->warehouse_id] ?? null,
            'total_in' => (float) $r->total_in,
            'total_out' => (float) $r->total_out,
            'balance' => round((float) $r->total_in - (float) $r->total_out, 2),
        ])->values();
    }

    /** Affectations de matériel encore actives (badge dashboard). */
    public function activeAssignmentsCount(): int
    {
        return EquipmentAssignment::where('status', EquipmentAssignment::STATUS_AFFECTEE)->count();
    }

    /** Traçabilité : journal workflow de la demande publique liée. */
    private function logDecision(StockMovement $mouvement, string $action, ?string $reason, User $actor): void
    {
        try {
            $demande = $mouvement->public_request_id
                ? \App\Models\PublicRequest::find($mouvement->public_request_id)
                : null;

            $comment = "Mouvement {$mouvement->reference} par {$actor->name}"
                . ($reason ? " — Motif : {$reason}" : '');

            $demande?->logWorkflowAction($action, $comment, $actor->id);
        } catch (\Exception $e) {
            Log::warning('Traçabilité décision comptable échouée: ' . $e->getMessage());
        }
    }
}
