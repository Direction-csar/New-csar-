<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarteDemandesController extends Controller
{
    /**
     * Afficher la carte des demandes d'aide géolocalisées
     */
    public function index(Request $request)
    {
        return view('admin.carte-demandes.index');
    }

    /**
     * API - Données pour la carte (demandes + agrégation par région)
     */
    public function getMapData(Request $request)
    {
        try {
            $filters = $request->only(['region', 'departement', 'commune', 'type', 'status', 'year', 'month', 'address']);

            $query = PublicRequest::query();

            // Filtrer les demandes d'aide (aide_alimentaire, aide, etc.)
            $query->where(function ($q) {
                $q->where('type', 'like', '%aide%')
                  ->orWhere('type', 'aide_alimentaire');
            });

            if (!empty($filters['region'])) {
                $query->where('region', $filters['region']);
            }
            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['year'])) {
                $query->whereYear('created_at', $filters['year']);
            }
            if (!empty($filters['month'])) {
                $query->whereMonth('created_at', $filters['month']);
            }

            // Demandes avec coordonnées (points individuels)
            $demandesPoints = $query->clone()
                ->select('id', 'full_name', 'latitude', 'longitude', 'region', 'status', 'type', 'created_at', 'address', 'tracking_code')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($d) {
                    return [
                        'type' => 'demande',
                        'id' => $d->id,
                        'lat' => (float) $d->latitude,
                        'lng' => (float) $d->longitude,
                        'name' => $d->full_name ?? 'Demande #' . $d->id,
                        'region' => $d->region ?? 'Non spécifié',
                        'status' => $d->status,
                        'demande_type' => $d->type,
                        'created_at' => $d->created_at->format('d/m/Y H:i'),
                        'address' => $d->address ?? 'Non spécifié',
                        'tracking_code' => $d->tracking_code ?? '',
                    ];
                })
                ->toArray();

            // Agrégation par région (cercles numérotés comme SmartWork)
            $regionCoords = config('regions_senegal.coordinates', []);
            $demandesByRegion = $query->clone()
                ->select('region', DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending"),
                    DB::raw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved"),
                    DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected"))
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->groupBy('region')
                ->orderByDesc('total')
                ->get()
                ->map(function ($row) use ($regionCoords) {
                    $region = $row->region;
                    $lat = $regionCoords[$region]['lat'] ?? null;
                    $lng = $regionCoords[$region]['lng'] ?? null;
                    foreach ($regionCoords as $name => $c) {
                        if (stripos($name, $region) !== false || stripos($region, $name) !== false) {
                            $lat = $c['lat'];
                            $lng = $c['lng'];
                            break;
                        }
                    }
                    return [
                        'type' => 'zone',
                        'region' => $region,
                        'total' => (int) $row->total,
                        'pending' => (int) $row->pending,
                        'approved' => (int) $row->approved,
                        'rejected' => (int) $row->rejected,
                        'lat' => $lat,
                        'lng' => $lng,
                    ];
                })
                ->filter(fn ($r) => $r['lat'] !== null && $r['lng'] !== null)
                ->values()
                ->map(function ($r) {
                    return [
                        'type' => 'zone',
                        'id' => 'Z-' . $r['region'],
                        'lat' => $r['lat'],
                        'lng' => $r['lng'],
                        'name' => $r['region'],
                        'region' => $r['region'],
                        'total' => $r['total'],
                        'approved' => $r['approved'],
                        'pending' => $r['pending'],
                        'rejected' => $r['rejected'],
                    ];
                })
                ->toArray();

            $regions = PublicRequest::whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct()
                ->pluck('region')
                ->sort()
                ->values()
                ->toArray();

            $types = PublicRequest::where('type', 'like', '%aide%')
                ->distinct()
                ->pluck('type')
                ->toArray();

            $totalDemandes = $query->clone()->count();

            return response()->json([
                'success' => true,
                'demandes' => $demandesPoints,
                'zones' => $demandesByRegion,
                'regions' => $regions,
                'types' => $types,
                'stats' => [
                    'total' => $totalDemandes,
                    'with_coords' => count($demandesPoints),
                    'zones_count' => count($demandesByRegion),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('CarteDemandesController getMapData', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
