<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\PublicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    /** Coordonnées des régions (chefs-lieux Sénégal) pour la carte des zones à forte demande */
    private function getRegionCoordinates(): array
    {
        return config('regions_senegal.coordinates', []);
    }

    /**
     * Afficher la carte interactive (lecture seule pour DG)
     * Carte des entrepôts + carte des demandes par zone (géolocalisation des zones à forte demande)
     */
    public function index()
    {
        try {
            $warehouses = Warehouse::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('is_active', 1)
                ->get();

            // Statistiques demandes par région (pour tableau + carte zones)
            $demandesByRegion = $this->getDemandesByRegion();
            $regionCoords = $this->getRegionCoordinates();

            $stats = [
                'total_warehouses' => $warehouses->count(),
                'total_requests' => PublicRequest::count(),
                'pending_requests' => PublicRequest::where('status', 'pending')->count(),
                'approved_requests' => PublicRequest::where('status', 'approved')->count(),
            ];

            return view('dg.map.index', compact('warehouses', 'stats', 'demandesByRegion', 'regionCoords'));
        } catch (\Exception $e) {
            \Log::error('Erreur dans DG MapController@index', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return redirect()->back()->with('error', 'Erreur lors du chargement de la carte');
        }
    }

    /**
     * Statistiques des demandes d'aide alimentaire par région (toutes demandes, pas seulement liées à un entrepôt)
     */
    private function getDemandesByRegion(): array
    {
        $rows = DB::table('public_requests')
            ->select('region', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"), DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"), DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected"))
            ->whereNotNull('region')->where('region', '!=', '')
            ->groupBy('region')
            ->orderByDesc('total')
            ->get();

        $coords = $this->getRegionCoordinates();
        return $rows->map(function ($row) use ($coords) {
            $region = $row->region;
            $lat = null;
            $lng = null;
            foreach ($coords as $name => $c) {
                if (stripos($name, $region) !== false || stripos($region, $name) !== false) {
                    $lat = $c['lat'];
                    $lng = $c['lng'];
                    break;
                }
            }
            if ($lat === null && isset($coords[$region])) {
                $lat = $coords[$region]['lat'];
                $lng = $coords[$region]['lng'];
            }
            return [
                'region' => $region,
                'total' => (int) $row->total,
                'pending' => (int) $row->pending,
                'approved' => (int) $row->approved,
                'rejected' => (int) $row->rejected,
                'lat' => $lat,
                'lng' => $lng,
            ];
        })->toArray();
    }

    /**
     * Récupérer les données pour la carte (API)
     */
    public function getData(Request $request)
    {
        try {
            $regionCoords = $this->getRegionCoordinates();

            // Entrepôts
            $warehouses = Warehouse::whereNotNull('latitude')->whereNotNull('longitude')->where('is_active', 1)
                ->select('id', 'name', 'latitude', 'longitude', 'region', 'address')
                ->get()
                ->map(function ($w) {
                    $requestsCount = \Schema::hasColumn((new PublicRequest)->getTable(), 'warehouse_id')
                        ? PublicRequest::where('warehouse_id', $w->id)->count() : 0;
                    $pendingRequests = \Schema::hasColumn((new PublicRequest)->getTable(), 'warehouse_id')
                        ? PublicRequest::where('warehouse_id', $w->id)->where('status', 'pending')->count() : 0;
                    return [
                        'id' => $w->id,
                        'name' => $w->name,
                        'latitude' => (float) $w->latitude,
                        'longitude' => (float) $w->longitude,
                        'region' => $w->region,
                        'address' => $w->address,
                        'requests_count' => $requestsCount,
                        'pending_requests' => $pendingRequests,
                        'status' => $pendingRequests > 0 ? 'warning' : 'success',
                    ];
                });

            // Demandes avec coordonnées (priorité: lat/lng de la demande, sinon entrepôt)
            $recentRequests = PublicRequest::with(['warehouse', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($req) {
                    $lat = $req->latitude ? (float) $req->latitude : null;
                    $lng = $req->longitude ? (float) $req->longitude : null;
                    if (($lat === null || $lng === null) && $req->warehouse) {
                        $lat = $req->warehouse->latitude ? (float) $req->warehouse->latitude : null;
                        $lng = $req->warehouse->longitude ? (float) $req->warehouse->longitude : null;
                    }
                    return [
                        'id' => $req->id,
                        'type' => $req->type,
                        'status' => $req->status,
                        'region' => $req->region,
                        'created_at' => $req->created_at->format('d/m/Y H:i'),
                        'user_name' => $req->full_name ?? ($req->assignedTo->name ?? 'N/A'),
                        'warehouse_name' => $req->warehouse->name ?? '—',
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ];
                });

            // Statistiques par région (demandes, pas par entrepôt)
            $demandesByRegion = $this->getDemandesByRegion();
            $regionStats = collect($demandesByRegion)->map(function ($r) use ($regionCoords) {
                $lat = $r['lat'];
                $lng = $r['lng'];
                return [
                    'region' => $r['region'],
                    'total_requests' => $r['total'],
                    'pending_requests' => $r['pending'],
                    'approved_requests' => $r['approved'],
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            })->values();

            return response()->json([
                'warehouses' => $warehouses,
                'recent_requests' => $recentRequests,
                'region_stats' => $regionStats,
                'demandes_by_region' => $demandesByRegion,
                'success' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans DG MapController@getData', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return response()->json(['error' => 'Erreur lors du chargement des données de la carte', 'success' => false], 500);
        }
    }
}

