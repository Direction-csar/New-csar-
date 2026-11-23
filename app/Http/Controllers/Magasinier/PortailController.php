<?php

namespace App\Http\Controllers\Magasinier;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Services\Magasin\WarehouseStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Portail web « Magasinier » : même périmètre que l'APK CSAR Magasin
 * (magasins, fiche de stock, mouvements, transferts, inventaires,
 * état par produit, missions DTL, reçus). Toute la logique métier
 * passe par WarehouseStockService — partagée avec l'API mobile.
 */
class PortailController extends Controller
{
    public function __construct(
        private readonly WarehouseStockService $stock,
    ) {}

    private function user()
    {
        return Auth::user();
    }

    private function guardWarehouse(int $warehouseId)
    {
        abort_unless($this->stock->canAccess($this->user(), $warehouseId), 403, 'Magasin non autorisé.');
        return Warehouse::findOrFail($warehouseId);
    }

    /** Tableau de bord : magasins, soldes, alertes, missions. */
    public function dashboard()
    {
        $user = $this->user();
        $warehouses = $this->stock->accessibleWarehouses($user);
        $stats = $this->stock->stats($user);
        $alerts = $this->stock->alerts($user);
        $missions = $this->stock->missions($user);

        $balances = $warehouses->map(function ($w) {
            return ['warehouse' => $w, 'balance' => $this->stock->balance($w->id)];
        });

        $recent = \App\Models\StockMovement::with('warehouse:id,name')
            ->when($user->role === 'magasinier', function ($q) use ($warehouses) {
                $q->whereIn('warehouse_id', $warehouses->pluck('id'));
            })
            ->orderBy('created_at', 'desc')->limit(10)->get();

        return view('magasinier.dashboard', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + compact('warehouses', 'balances', 'stats', 'alerts', 'missions', 'recent'));
    }

    /** Liste des magasins accessibles. */
    public function warehouses()
    {
        $warehouses = $this->stock->accessibleWarehouses($this->user());
        $balances = $warehouses->map(fn ($w) => ['warehouse' => $w, 'balance' => $this->stock->balance($w->id)]);

        return view('magasinier.warehouses.index', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + compact('balances'));
    }

    /** Fiche de stock d'un magasin : mouvements + solde + actions. */
    public function warehouseShow(Request $request, int $warehouse)
    {
        $wh = $this->guardWarehouse($warehouse);
        $sheet = $this->stock->stockSheet(
            $wh->id,
            $request->input('date_from'),
            $request->input('date_to'),
            50
        );

        return view('magasinier.warehouses.show', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + [
            'warehouse' => $wh,
            'movements' => $sheet['movements'],
            'balance' => $sheet['balance'],
            'status' => $this->stock->stockStatus($wh->id),
        ]);
    }

    /** Formulaire nouveau mouvement (entrée/sortie/ajustement/report). */
    public function movementCreate(int $warehouse)
    {
        $wh = $this->guardWarehouse($warehouse);
        $balance = $this->stock->balance($wh->id);
        $products = $this->stock->products();

        return view('magasinier.mouvements.create', [
            'direction' => 'magasinier',
            'directionLabel' => 'Magasin & Gestion des stocks',
            'warehouse' => $wh,
            'balance' => $balance,
            'products' => $products,
        ]);
    }

    public function movementStore(Request $request, int $warehouse)
    {
        $wh = $this->guardWarehouse($warehouse);

        $data = $request->validate([
            'movement_date' => 'required|date',
            'label' => 'required|string|max:200',
            'product' => 'required|string|max:100',
            'format' => 'required|numeric|min:0.01',
            'position' => 'nullable|string|max:50',
            'movement_type' => 'required|in:entry,exit,adjustment,report',
            'sacs' => 'required|numeric|min:0',
            'kg' => 'required|numeric|min:0',
            'observation' => 'nullable|string|max:1000',
        ]);

        $produit = collect($this->stock->products())->firstWhere('name', $data['product']);
        abort_unless($produit, 422, 'Produit inconnu.');
        $format = rtrim(rtrim(number_format((float) $data['format'], 2, '.', ''), '0'), '.');
        // Même convention que l'APK : « Produit [50kg] — motif ».
        $data['label'] = "{$produit['name']} [{$format}{$produit['unit']}] — {$data['label']}";
        unset($data['product'], $data['format']);

        $result = $this->stock->recordMovement($this->user(), $data + ['warehouse_id' => $wh->id]);

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->with('error', $result['message'] ?? 'Erreur.');
        }

        return redirect()->route('magasinier.receipts.show', $result['reference'])
            ->with('success', $result['message'] . ' Réf : ' . $result['reference']);
    }

    /** Formulaire de transfert inter-magasins. */
    public function transferCreate()
    {
        return view('magasinier.transferts.create', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + [
            'warehouses' => $this->stock->accessibleWarehouses($this->user()),
            'products' => $this->stock->products(),
        ]);
    }

    public function transferStore(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:warehouse_id',
            'movement_date' => 'required|date',
            'label' => 'required|string|max:200',
            'product' => 'required|string|max:100',
            'format' => 'required|numeric|min:0.01',
            'sacs' => 'required|numeric|min:0.01',
            'kg' => 'required|numeric|min:0.01',
            'observation' => 'nullable|string|max:1000',
        ]);

        $produit = collect($this->stock->products())->firstWhere('name', $data['product']);
        abort_unless($produit, 422, 'Produit inconnu.');
        $format = rtrim(rtrim(number_format((float) $data['format'], 2, '.', ''), '0'), '.');
        $data['label'] = "{$produit['name']} [{$format}{$produit['unit']}] — {$data['label']}";
        unset($data['product'], $data['format']);

        $result = $this->stock->recordTransfer($this->user(), $data);

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->with('error', $result['message'] ?? 'Erreur.');
        }

        return redirect()->route('magasinier.warehouses.show', $data['warehouse_id'])
            ->with('success', $result['message'] . ' N° ' . ($result['numero_transfert'] ?? ''));
    }

    /** Formulaire d'inventaire physique. */
    public function inventoryCreate()
    {
        return view('magasinier.inventaires.create', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + [
            'warehouses' => $this->stock->accessibleWarehouses($this->user()),
        ]);
    }

    public function inventoryStore(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'movement_date' => 'required|date',
            'label' => 'required|string|max:255',
            'theoretical_sacs' => 'required|numeric|min:0',
            'actual_sacs' => 'required|numeric|min:0',
            'observation' => 'nullable|string|max:1000',
        ]);

        $result = $this->stock->recordInventory($this->user(), $data);

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->with('error', $result['message'] ?? 'Erreur.');
        }

        return redirect()->route('magasinier.receipts.show', $result['reference'])
            ->with('success', $result['message'] . ' Réf : ' . $result['reference']);
    }

    /** État du stock par produit/format pour un magasin. */
    public function stockStatus(int $warehouse)
    {
        $wh = $this->guardWarehouse($warehouse);
        $status = $this->stock->stockStatus($wh->id);

        return view('magasinier.warehouses.stock-status', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + compact('status'));
    }

    /** Missions de sortie de stock (DTL → magasinier). */
    public function missions(Request $request)
    {
        $missions = $this->stock->missions($this->user(), $request->input('status'));

        return view('magasinier.missions.index', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + compact('missions'));
    }

    public function missionReport(Request $request, int $id)
    {
        $data = $request->validate([
            'movement_reference' => 'nullable|string|max:100',
            'report' => 'required|string|min:10|max:5000',
        ]);

        $result = $this->stock->reportMission($this->user(), $id, $data['report'], $data['movement_reference'] ?? null);

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->with('error', $result['message'] ?? 'Erreur.');
        }

        return redirect()->route('magasinier.missions.index')->with('success', $result['message']);
    }

    /** Reçu / détail d'un mouvement. */
    public function receipt(string $reference)
    {
        $data = $this->stock->receipt($reference);
        abort_unless($data, 404);

        // Magasinier : le reçu ne concerne que ses magasins
        if ($this->user()->role === 'magasinier') {
            $movement = \App\Models\StockMovement::where('reference', $reference)->first();
            abort_unless($movement && $this->stock->canAccess($this->user(), (int) $movement->warehouse_id), 403);
        }

        return view('magasinier.receipts.show', [ 'direction' => 'magasinier', 'directionLabel' => 'Magasin & Gestion des stocks' ] + $data);
    }
}
