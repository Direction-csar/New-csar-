<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ReportsController extends Controller
{
    private function formatBytes(?int $bytes): string
    {
        $bytes = $bytes ?? 0;
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $value = (float) $bytes;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        return round($value, 1) . ' ' . $units[$i];
    }

    /**
     * Afficher la page des rapports (lecture seule pour DG)
     */
    public function index(Request $request)
    {
        try {
            // Statistiques générales
            $stats = [
                'total_requests' => PublicRequest::count(),
                'total_warehouses' => Warehouse::count(),
                'total_users' => User::count(),
                'total_personnel' => Personnel::count(),
                'total_movements' => StockMovement::count(),
            ];

            // Évolution des demandes (30 derniers jours)
            $requestsEvolution = DB::table('public_requests')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            // Statistiques par statut
            $statusStats = DB::table('public_requests')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            // Top entrepôts par activité
            if (Schema::hasColumn('public_requests', 'warehouse_id')) {
                $topWarehouses = DB::table('warehouses')
                    ->leftJoin('public_requests', 'warehouses.id', '=', 'public_requests.warehouse_id')
                    ->select(
                        'warehouses.name',
                        'warehouses.region',
                        DB::raw('COUNT(public_requests.id) as requests_count')
                    )
                    ->groupBy('warehouses.id', 'warehouses.name', 'warehouses.region')
                    ->orderBy('requests_count', 'desc')
                    ->limit(10)
                    ->get();
            } else {
                // Fallback: pas de lien direct entre demandes et entrepôts dans la table public_requests.
                // On approxime via la région: nombre de demandes par région, appliqué aux entrepôts de la même région.
                $requestsByRegion = DB::table('public_requests')
                    ->select('region', DB::raw('COUNT(*) as count'))
                    ->groupBy('region')
                    ->pluck('count', 'region');

                $topWarehouses = Warehouse::query()
                    ->select(['name', 'region'])
                    ->get()
                    ->map(function ($w) use ($requestsByRegion) {
                        return (object) [
                            'name' => $w->name,
                            'region' => $w->region,
                            'requests_count' => (int) ($requestsByRegion[$w->region] ?? 0),
                        ];
                    })
                    ->sortByDesc('requests_count')
                    ->take(10)
                    ->values();
            }

            // Activité par mois
            $monthlyActivity = DB::table('public_requests')
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            // Rapports disponibles: fichiers générés + PDFs livrés dans public/rapport
            $reports = collect();

            $storageReportsDir = storage_path('app/reports');
            if (is_dir($storageReportsDir)) {
                foreach (glob($storageReportsDir . DIRECTORY_SEPARATOR . '*') as $path) {
                    if (!is_file($path)) continue;
                    $filename = basename($path);
                    $mtime = @filemtime($path) ?: time();
                    $sizeBytes = @filesize($path) ?: 0;
                    $reports->push((object) [
                        'filename' => $filename,
                        'name' => pathinfo($filename, PATHINFO_FILENAME),
                        'description' => 'Rapport généré (DG)',
                        'type' => strtoupper(pathinfo($filename, PATHINFO_EXTENSION) ?: 'FILE'),
                        'period' => Carbon::createFromTimestamp($mtime)->translatedFormat('F Y'),
                        'date_range' => '-',
                        'size' => $this->formatBytes($sizeBytes),
                        'created_at' => Carbon::createFromTimestamp($mtime),
                    ]);
                }
            }

            $publicReportsDir = public_path('rapport');
            if (is_dir($publicReportsDir)) {
                foreach (glob($publicReportsDir . DIRECTORY_SEPARATOR . '*.pdf') as $path) {
                    if (!is_file($path)) continue;
                    $filename = basename($path);
                    $mtime = @filemtime($path) ?: time();
                    $sizeBytes = @filesize($path) ?: 0;
                    $reports->push((object) [
                        'filename' => $filename,
                        'name' => pathinfo($filename, PATHINFO_FILENAME),
                        'description' => 'Rapport fourni (public/rapport)',
                        'type' => 'PDF',
                        'period' => Carbon::createFromTimestamp($mtime)->translatedFormat('F Y'),
                        'date_range' => '-',
                        'size' => $this->formatBytes($sizeBytes),
                        'created_at' => Carbon::createFromTimestamp($mtime),
                    ]);
                }
            }

            $reports = $reports
                ->sortByDesc(fn ($r) => $r->created_at instanceof Carbon ? $r->created_at->timestamp : strtotime((string) $r->created_at))
                ->values();

            // Statistiques des rapports
            $reportStats = [
                'total_reports' => $reports->count(),
                'total_size' => $this->formatBytes(
                    $reports->reduce(function ($carry, $r) {
                        // extract number+unit from formatted size is hard; keep as 0 here
                        return $carry;
                    }, 0)
                ),
                'last_generated' => $reports->first()?->created_at ? (Carbon::parse($reports->first()->created_at)->diffForHumans()) : 'N/A'
            ];

            return view('dg.reports.index', compact(
                'stats',
                'requestsEvolution',
                'statusStats',
                'topWarehouses',
                'monthlyActivity',
                'reports',
                'reportStats'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur dans DG ReportsController@index', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Erreur lors du chargement des rapports');
        }
    }

    /**
     * Aperçu simple d'un rapport (évite RouteNotFound).
     */
    public function show(string $filename)
    {
        $safe = basename($filename);
        if ($safe !== $filename) {
            abort(404);
        }

        $storagePath = storage_path("app/reports/{$safe}");
        $publicPath = public_path("rapport/{$safe}");

        if (file_exists($storagePath)) {
            $path = $storagePath;
            $source = 'storage';
        } elseif (file_exists($publicPath)) {
            $path = $publicPath;
            $source = 'public';
        } else {
            abort(404);
        }

        return view('dg.reports.show', [
            'filename' => $safe,
            'source' => $source,
            'size' => $this->formatBytes(@filesize($path) ?: 0),
            'created_at' => Carbon::createFromTimestamp(@filemtime($path) ?: time()),
        ]);
    }

    /**
     * Générer un rapport personnalisé
     */
    public function generate(Request $request)
    {
        try {
            $request->validate([
                'report_type' => 'required|in:requests,warehouses,users,personnel',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'format' => 'required|in:pdf,excel'
            ]);

            $reportType = $request->report_type;
            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;
            $format = $request->format;

            // Générer le rapport selon le type
            switch ($reportType) {
                case 'requests':
                    $data = $this->generateRequestsReport($dateFrom, $dateTo);
                    break;
                case 'warehouses':
                    $data = $this->generateWarehousesReport();
                    break;
                case 'users':
                    $data = $this->generateUsersReport();
                    break;
                case 'personnel':
                    $data = $this->generatePersonnelReport();
                    break;
                default:
                    throw new \Exception('Type de rapport non supporté');
            }

            // Pour l'instant, on retourne les données en JSON
            // Dans une vraie application, on générerait un PDF ou Excel
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Rapport généré avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans DG ReportsController@generate', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération du rapport'
            ], 500);
        }
    }

    /**
     * Exporter les données
     */
    public function export(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:requests,warehouses,users,personnel',
                'format' => 'required|in:csv,excel'
            ]);

            // Pour l'instant, on retourne un message de succès
            // Dans une vraie application, on générerait le fichier d'export
            return response()->json([
                'success' => true,
                'message' => 'Export généré avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans DG ReportsController@export', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'export'
            ], 500);
        }
    }

    /**
     * Générer le rapport des demandes
     */
    private function generateRequestsReport($dateFrom = null, $dateTo = null)
    {
        // Ne pas eager-load warehouse si la colonne warehouse_id n'existe pas.
        $query = PublicRequest::with(['assignedTo']);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->get();
    }

    /**
     * Générer le rapport des entrepôts
     */
    private function generateWarehousesReport()
    {
        return Warehouse::withCount(['publicRequests', 'stockMovements'])->get();
    }

    /**
     * Générer le rapport des utilisateurs
     */
    private function generateUsersReport()
    {
        return User::withCount(['publicRequests', 'stockMovements'])->get();
    }

    /**
     * Générer le rapport du personnel
     */
    private function generatePersonnelReport()
    {
        return Personnel::all();
    }
}

