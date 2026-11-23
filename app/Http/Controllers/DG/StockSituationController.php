<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Services\Magasin\WarehouseStockService;

/** Situation des stocks de tous les magasins en temps réel (lecture seule, source : mouvements des magasiniers). */
class StockSituationController extends Controller
{
    public function __construct(private WarehouseStockService $stock) {}

    public function index()
    {
        return view('dg.stocks.situation', ['situation' => $this->stock->situation()]);
    }

    public function data()
    {
        return response()->json($this->stock->situation())->header('Cache-Control', 'no-store, private');
    }
}
