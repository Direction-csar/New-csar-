<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gestion administrative des magasins : CRUD réel.
 * La suppression est refusée dès qu'un magasin a des mouvements ou des
 * utilisateurs rattachés — il faut alors le désactiver (is_active = false).
 */
class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::query()
            ->withCount(['users'])
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('region', 'like', '%' . $request->q . '%')
                  ->orWhere('city', 'like', '%' . $request->q . '%');
            }))
            ->when($request->filled('statut'), fn ($q) => $request->statut === 'actif'
                ? $q->where('is_active', true)->where('status', 'active')
                : $q->where(function ($w) { $w->where('is_active', false)->orWhere('status', '!=', 'active'); }))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        // Mouvements par magasin (total + en attente de vérification)
        $movements = StockMovement::select('warehouse_id', 'status', DB::raw('COUNT(*) as n'))
            ->groupBy('warehouse_id', 'status')->get()
            ->groupBy('warehouse_id');

        return view('admin.warehouses.index', [
            'warehouses' => $warehouses,
            'movements' => $movements,
        ]);
    }

    public function create()
    {
        return view('admin.warehouses.form', ['warehouse' => new Warehouse()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $warehouse = Warehouse::create($data + [
            'status' => $data['is_active'] ? 'active' : 'inactive',
            'current_stock' => 0,
        ]);
        $this->audit('warehouse.create', $warehouse, null, $data);

        return redirect()->route('admin.warehouses.index')->with('success', 'Magasin « ' . $warehouse->name . ' » créé.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['users:id,name,email,role', 'stocks']);
        $warehouse->movements_count = StockMovement::where('warehouse_id', $warehouse->id)->count();
        $warehouse->pending_count = StockMovement::where('warehouse_id', $warehouse->id)->where('status', 'en_attente')->count();

        return view('admin.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('admin.warehouses.form', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $this->validated($request);
        $old = $warehouse->only(array_keys($data));
        $warehouse->update($data + ['status' => $data['is_active'] ? 'active' : 'inactive']);
        $this->audit('warehouse.update', $warehouse, $old, $warehouse->fresh()->only(array_keys($data)));

        return redirect()->route('admin.warehouses.index')->with('success', 'Magasin mis à jour.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $hasMovements = StockMovement::where('warehouse_id', $warehouse->id)->exists();
        $hasUsers = User::where('warehouse_id', $warehouse->id)->exists();
        $hasStocks = DB::table('stocks')->where('warehouse_id', $warehouse->id)->exists();

        if ($hasMovements || $hasUsers || $hasStocks) {
            return redirect()->route('admin.warehouses.index')->with('error',
                'Suppression impossible : ce magasin a des mouvements, stocks ou utilisateurs rattachés. Désactivez-le plutôt (is_active = non).');
        }

        $snapshot = $warehouse->only(['name', 'region', 'city']);
        $warehouse->delete();
        $this->audit('warehouse.delete', null, $snapshot, null, "Magasin supprimé : {$warehouse->name}");

        return redirect()->route('admin.warehouses.index')->with('success', 'Magasin supprimé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'region' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'capacity' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function audit(string $action, ?Warehouse $warehouse, ?array $old, ?array $new, ?string $description = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'level' => 'warning',
                'status' => 'success',
                'model_type' => Warehouse::class,
                'model_id' => $warehouse?->id,
                'description' => $description ?? "Magasin {$warehouse?->name} — {$action}",
                'old_values' => $old,
                'new_values' => $new,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Audit warehouse échoué : ' . $e->getMessage());
        }
    }
}
