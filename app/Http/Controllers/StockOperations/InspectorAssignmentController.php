<?php

namespace App\Http\Controllers\StockOperations;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InspectorWarehouseAssignment;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Gestion des affectations inspecteur ↔ magasins.
 * Réservé à la direction (dtl / dg / admin) : consultation globale,
 * affectation et révocation — avec journal d'audit et historique complet
 * (une affectation révoquée reste en base, jamais supprimée).
 */
class InspectorAssignmentController extends Controller
{
    private const MANAGE_ROLES = ['dtl', 'dg', 'admin', 'super_admin'];

    private function user()
    {
        return Auth::user();
    }

    private function authorizeManager(): void
    {
        abort_unless(in_array($this->user()->role, self::MANAGE_ROLES, true), 403);
    }

    /** Portail courant pour les routes/retours (dtl, dg, admin). */
    private function portal(Request $request): string
    {
        $path = $request->path();
        return str_starts_with($path, 'dtl/') ? 'dtl'
            : (str_starts_with($path, 'admin') ? 'admin' : 'dg');
    }

    private function indexRoute(string $portal): string
    {
        return match ($portal) {
            'dtl' => 'dtl.inspector-assignments.index',
            'admin' => 'admin.inspector-assignments.index',
            default => 'dg.inspector-assignments.index',
        };
    }

    public function index(Request $request)
    {
        $this->authorizeManager();
        $portal = $this->portal($request);

        $inspectors = User::where('role', 'inspecteur')->where('is_active', true)
            ->orderBy('name')->get(['id', 'name', 'email']);
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get(['id', 'name', 'region']);

        $active = InspectorWarehouseAssignment::with(['inspector:id,name', 'warehouse:id,name', 'assignedBy:id,name'])
            ->where('is_active', true)->orderBy('inspector_id')->orderBy('warehouse_id')->get();

        $history = InspectorWarehouseAssignment::with(['inspector:id,name', 'warehouse:id,name', 'assignedBy:id,name', 'revokedBy:id,name'])
            ->where('is_active', false)->orderByDesc('revoked_at')->limit(50)->get();

        // Matrice inspecteur → ids des magasins actifs
        $matrix = $active->groupBy('inspector_id')
            ->map(fn ($rows) => $rows->pluck('warehouse_id')->map('intval')->all());

        return view('stock-operations.assignments', [
            'direction' => $portal,
            'directionLabel' => 'Affectations inspecteurs',
            'portal' => $portal,
            'inspectors' => $inspectors,
            'warehouses' => $warehouses,
            'matrix' => $matrix,
            'coverage' => app(\App\Services\Magasin\InspectorCoverageService::class)->report(),
            'active' => $active,
            'history' => $history,
            'indexRoute' => $this->indexRoute($portal),
        ]);
    }

    /** Synchronise les affectations d'un inspecteur (cases cochées). */
    public function store(Request $request)
    {
        $this->authorizeManager();
        $portal = $this->portal($request);

        $data = $request->validate([
            'inspector_id' => 'required|exists:users,id',
            'warehouse_ids' => 'nullable|array',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'note' => 'nullable|string|max:255',
        ]);

        $inspector = User::findOrFail($data['inspector_id']);
        abort_unless($inspector->role === 'inspecteur', 422, 'L\'utilisateur doit avoir le rôle inspecteur.');

        $wanted = array_map('intval', $data['warehouse_ids'] ?? []);
        $existing = InspectorWarehouseAssignment::where('inspector_id', $inspector->id)->get()->keyBy('warehouse_id');

        foreach ($wanted as $wid) {
            $row = $existing->get($wid);
            if ($row && $row->is_active) {
                continue; // déjà actif
            }
            if ($row) {
                $row->update(['is_active' => true, 'revoked_at' => null, 'revoked_by' => null,
                    'assigned_by' => $this->user()->id, 'note' => $data['note'] ?? $row->note]);
            } else {
                InspectorWarehouseAssignment::create([
                    'inspector_id' => $inspector->id,
                    'warehouse_id' => $wid,
                    'assigned_by' => $this->user()->id,
                    'note' => $data['note'] ?? null,
                    'is_active' => true,
                ]);
            }
            AuditLog::create([
                'user_id' => $this->user()->id,
                'action' => 'inspector_assignment.assign',
                'model_type' => InspectorWarehouseAssignment::class,
                'model_id' => $row?->id,
                'level' => 'info',
                'status' => 'success',
                'description' => "Affectation inspecteur {$inspector->name} ↔ magasin #{$wid} par {$this->user()->name}",
            ]);
        }

        foreach ($existing as $wid => $row) {
            if ($row->is_active && !in_array((int) $wid, $wanted, true)) {
                $row->update(['is_active' => false, 'revoked_at' => now(), 'revoked_by' => $this->user()->id]);
                AuditLog::create([
                    'user_id' => $this->user()->id,
                    'action' => 'inspector_assignment.revoke',
                    'model_type' => InspectorWarehouseAssignment::class,
                    'model_id' => $row->id,
                    'level' => 'info',
                    'status' => 'success',
                    'description' => "Révocation affectation inspecteur {$inspector->name} ↔ magasin #{$wid} par {$this->user()->name}",
                ]);
            }
        }

        return redirect()->route($this->indexRoute($portal))
            ->with('success', "Affectations de {$inspector->name} mises à jour.");
    }

    /** Révocation unitaire d'une affectation. */
    public function revoke(Request $request, InspectorWarehouseAssignment $assignment)
    {
        $this->authorizeManager();
        $portal = $this->portal($request);

        if ($assignment->is_active) {
            $assignment->update(['is_active' => false, 'revoked_at' => now(), 'revoked_by' => $this->user()->id]);
            AuditLog::create([
                'user_id' => $this->user()->id,
                'action' => 'inspector_assignment.revoke',
                'model_type' => InspectorWarehouseAssignment::class,
                'model_id' => $assignment->id,
                'level' => 'info',
                'status' => 'success',
                'description' => "Révocation affectation {$assignment->inspector?->name} ↔ {$assignment->warehouse?->name} par {$this->user()->name}",
            ]);
        }

        return redirect()->route($this->indexRoute($portal))
            ->with('success', 'Affectation révoquée.');
    }
}
