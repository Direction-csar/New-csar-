<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockController extends Controller
{
    public function index()
    {
        try {
            // Récupérer les stocks
            $stocks = StockMovement::with('warehouse')->latest()->get();
            
            // Statistiques des stocks
            $stats = [
                'total_items' => StockMovement::count(),
                'in_stock' => StockMovement::where('quantity', '>', 0)->count(),
                'low_stock' => StockMovement::where('quantity', '<=', 10)->count(),
                'out_of_stock' => StockMovement::where('quantity', 0)->count(),
            ];
            
            Log::info('Accès aux stocks DG', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);
            
            return view('dg.stocks.index', compact('stocks', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des stocks DG', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des stocks');
        }
    }
    
    public function show($id)
    {
        try {
            $stock = StockMovement::with('warehouse')->findOrFail($id);
            
            Log::info('Consultation stock DG', [
                'user_id' => auth()->id(),
                'stock_id' => $id,
                'timestamp' => Carbon::now()
            ]);
            
            return view('dg.stocks.show', compact('stock'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la consultation du stock DG', [
                'user_id' => auth()->id(),
                'stock_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Stock non trouvé');
        }
    }
}








































