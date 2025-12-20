<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WarehouseController extends Controller
{
    public function index()
    {
        try {
            // Récupérer les entrepôts
            $warehouses = Warehouse::latest()->get();
            
            // Statistiques des entrepôts
            $stats = [
                'total' => Warehouse::count(),
                'active' => Warehouse::where('is_active', true)->count(),
                'capacity' => Warehouse::sum('capacity') ?? 0,
                'regions' => Warehouse::distinct('region')->count(),
            ];
            
            Log::info('Accès aux entrepôts DG', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);
            
            return view('dg.warehouses.index', compact('warehouses', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des entrepôts DG', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des entrepôts');
        }
    }
    
    public function show($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            Log::info('Consultation entrepôt DG', [
                'user_id' => auth()->id(),
                'warehouse_id' => $id,
                'timestamp' => Carbon::now()
            ]);
            
            return view('dg.warehouses.show', compact('warehouse'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la consultation de l\'entrepôt DG', [
                'user_id' => auth()->id(),
                'warehouse_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Entrepôt non trouvé');
        }
    }
}



































