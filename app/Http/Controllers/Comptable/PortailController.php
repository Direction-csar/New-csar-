<?php

namespace App\Http\Controllers\Comptable;

use App\Http\Controllers\Controller;
use App\Models\EquipmentAssignment;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\Comptable\ComptableMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Portail web « Comptable des matières » : tableau de bord, état des
 * stocks, carburant et affectations de matériel. Les agrégats viennent
 * du même ComptableMovementService que l'API mobile — mêmes données,
 * mêmes règles.
 */
class PortailController extends Controller
{
    public function __construct(
        private readonly ComptableMovementService $service,
    ) {}

    /** Tableau de bord : volumes, alertes stock, carburant, affectations. */
    public function dashboard()
    {
        $stats = Schema::hasColumn('stock_movements', 'status')
            ? $this->service->statusCounts()
            : ['draft' => null, 'validated' => null, 'rejected' => null];

        $lowStock = $this->service->lowStockWarehouses();
        $fuel = $this->service->fuelSummary();
        $activeAssignments = $this->service->activeAssignmentsCount();

        $recent = StockMovement::with(['warehouse:id,name', 'creator:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $warehouses = Warehouse::orderBy('name')->get(['id', 'name', 'region', 'capacity', 'current_stock']);

        return view('comptable.dashboard', [
            'stats' => $stats,
            'lowStock' => $lowStock,
            'fuel' => $fuel,
            'activeAssignments' => $activeAssignments,
            'recent' => $recent,
            'warehouses' => $warehouses,
            'direction' => 'comptable',
        ]);
    }

    /** État des stocks par magasin (capacité vs stock courant + articles). */
    public function stocks()
    {
        $warehouses = Warehouse::with(['stocks' => function ($q) {
            $q->where('is_active', true)->orderBy('item_name');
        }])->orderBy('name')->get();

        return view('comptable.stocks', [
            'warehouses' => $warehouses,
            'direction' => 'comptable',
        ]);
    }

    /** Soldes carburant par produit et magasin (règle partagée API). */
    public function carburant()
    {
        return view('comptable.carburant', [
            'fuel' => $this->service->fuelSummary(),
            'direction' => 'comptable',
        ]);
    }

    /** Affectations de matériel : liste filtrée + formulaire de saisie. */
    public function materiel(Request $request)
    {
        $query = EquipmentAssignment::with(['warehouse:id,name', 'assigner:id,name'])
            ->orderBy('assigned_at', 'desc');

        if (in_array($request->status, ['affectee', 'retournee', 'perdue'], true)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('agent_name', 'like', $q)
                    ->orWhere('produit', 'like', $q)
                    ->orWhere('numero_bon', 'like', $q);
            });
        }

        $assignments = $query->paginate(20)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);

        return view('comptable.materiel', [
            'assignments' => $assignments,
            'warehouses' => $warehouses,
            'direction' => 'comptable',
        ]);
    }

    /** Enregistrer une affectation de matériel à un agent. */
    public function storeMateriel(Request $request)
    {
        $data = $request->validate([
            'produit' => 'required|string|max:255',
            'agent_name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'unite' => 'nullable|string|max:20',
            'quantity' => 'nullable|numeric|min:0.01|max:100000',
            'numero_bon' => 'nullable|string|max:100',
        ]);

        EquipmentAssignment::create([
            'produit' => $data['produit'],
            'agent_name' => $data['agent_name'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'unite' => $data['unite'] ?? 'pièce',
            'quantity' => $data['quantity'] ?? 1,
            'numero_bon' => $data['numero_bon'] ?? null,
            'status' => EquipmentAssignment::STATUS_AFFECTEE,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);

        return redirect()->route('comptable.materiel')
            ->with('success', 'Affectation enregistrée.');
    }

    /** Clôturer une affectation : retour ou perte déclarée (tracée). */
    public function cloturerMateriel(Request $request, $id)
    {
        $data = $request->validate([
            'outcome' => 'required|in:retournee,perdue',
            'return_note' => 'nullable|string|max:1000',
        ]);

        $assignment = EquipmentAssignment::findOrFail($id);

        if ($assignment->status !== EquipmentAssignment::STATUS_AFFECTEE) {
            return redirect()->back()->with('error', 'Affectation déjà clôturée.');
        }
        if ($data['outcome'] === 'perdue' && empty($data['return_note'])) {
            return redirect()->back()->with('error', 'Un motif est obligatoire pour déclarer une perte.');
        }

        $assignment->update([
            'status' => $data['outcome'],
            'returned_at' => now(),
            'returned_by' => Auth::id(),
            'return_note' => $data['return_note'] ?? null,
        ]);

        return redirect()->route('comptable.materiel')->with(
            'success',
            $data['outcome'] === 'retournee' ? 'Retour enregistré.' : 'Perte déclarée.'
        );
    }
}
