<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\PublicRequest;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    private const TYPES = ['requests', 'warehouses', 'users', 'personnel', 'stocks'];

    private const REPORT_PREFIX = 'csar-dg-';

    private const RETENTION_DAYS = 30;

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
            $stats = [
                'total_requests' => PublicRequest::count(),
                'total_warehouses' => Warehouse::count(),
                'total_users' => User::count(),
                'total_personnel' => Personnel::count(),
                'total_movements' => StockMovement::count(),
            ];

            // Évolution des demandes (30 derniers jours) : DATE() fonctionne en SQLite et MySQL
            $requestsEvolution = DB::table('public_requests')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $statusStats = DB::table('public_requests')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            // Top entrepôts : pas de lien direct demandes -> entrepôts, approximation par région
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

            // Fichiers disponibles : générés (storage/app/reports) + PDFs fournis (public/rapport)
            $reports = collect();
            $totalBytes = 0;

            $storageReportsDir = storage_path('app/reports');
            if (is_dir($storageReportsDir)) {
                foreach (glob($storageReportsDir . DIRECTORY_SEPARATOR . '*') as $path) {
                    if (!is_file($path)) continue;
                    $reports->push($this->describeFile($path, 'Rapport généré (DG)', $totalBytes));
                }
            }

            $publicReportsDir = public_path('rapport');
            if (is_dir($publicReportsDir)) {
                foreach (glob($publicReportsDir . DIRECTORY_SEPARATOR . '*.pdf') as $path) {
                    if (!is_file($path)) continue;
                    $reports->push($this->describeFile($path, 'Rapport fourni (public/rapport)', $totalBytes));
                }
            }

            $reports = $reports
                ->sortByDesc(fn ($r) => $r->created_at->timestamp)
                ->values();

            $reportStats = [
                'total_reports' => $reports->count(),
                'total_size' => $this->formatBytes($totalBytes),
                'last_generated' => $reports->first()?->created_at?->diffForHumans() ?? 'N/A',
            ];

            return view('dg.reports.index', compact(
                'stats',
                'requestsEvolution',
                'statusStats',
                'topWarehouses',
                'reports',
                'reportStats'
            ));
        } catch (\Throwable $e) {
            Log::error('Erreur dans DG ReportsController@index', [
                'exception' => $e,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('dg.dashboard')->with('error', 'Le module rapports est temporairement indisponible. Veuillez réessayer plus tard.');
        }
    }

    private function describeFile(string $path, string $description, int &$totalBytes): object
    {
        $filename = basename($path);
        $mtime = @filemtime($path) ?: time();
        $sizeBytes = @filesize($path) ?: 0;
        $totalBytes += $sizeBytes;

        return (object) [
            'filename' => $filename,
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'description' => $description,
            'type' => strtoupper(pathinfo($filename, PATHINFO_EXTENSION) ?: 'FILE'),
            'period' => \Carbon\Carbon::createFromTimestamp($mtime)->translatedFormat('F Y'),
            'date_range' => '-',
            'size' => $this->formatBytes($sizeBytes),
            'created_at' => \Carbon\Carbon::createFromTimestamp($mtime),
        ];
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
            'created_at' => \Carbon\Carbon::createFromTimestamp(@filemtime($path) ?: time()),
        ]);
    }

    /**
     * Génère un rapport réel : PDF (DomPDF) ou CSV, en téléchargement direct.
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'report_type' => 'required|in:' . implode(',', self::TYPES),
            'format' => 'required|in:pdf,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $report = $this->buildReport($data['report_type'], $data['date_from'] ?? null, $data['date_to'] ?? null);
        $extension = $data['format'];
        $filename = $this->filename($data['report_type'], $extension);

        $content = $extension === 'pdf'
            ? Pdf::loadView('dg.reports.pdf', ['report' => $report, 'generatedAt' => now()])->setPaper('a4', 'landscape')->output()
            : $this->csvContent($report);

        $this->persist($filename, $content);

        Log::info('Rapport DG généré', ['file' => $filename, 'rows' => count($report['rows']), 'user_id' => auth()->id()]);

        return response($content, 200, [
            'Content-Type' => $extension === 'pdf' ? 'application/pdf' : 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Stockage privé (hors du dossier public) : téléchargement uniquement via les routes DG authentifiées.
     * Les rapports générés sont purgés après RETENTION_DAYS jours.
     */
    private function persist(string $filename, string $content): void
    {
        $dir = storage_path('app/reports');
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $content, LOCK_EX);

        $threshold = now()->subDays(self::RETENTION_DAYS)->getTimestamp();
        foreach (glob($dir . DIRECTORY_SEPARATOR . self::REPORT_PREFIX . '*') ?: [] as $path) {
            if (is_file($path) && filemtime($path) < $threshold) {
                @unlink($path);
            }
        }
    }

    /**
     * Exporte les données brutes d'un type en CSV (compatible Excel).
     */
    public function export(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', self::TYPES),
            'format' => 'nullable|in:csv',
        ]);

        $report = $this->buildReport($data['type'], null, null);

        Log::info('Export DG généré', ['type' => $data['type'], 'rows' => count($report['rows']), 'user_id' => auth()->id()]);

        return $this->csvDownload($report, $this->filename($data['type'], 'csv'));
    }

    private function filename(string $type, string $extension): string
    {
        return self::REPORT_PREFIX . $type . '-' . now()->format('Ymd-His') . '-' . Str::lower(Str::random(6)) . '.' . $extension;
    }

    /**
     * Jeu de données d'un rapport. Seuls les champs nécessaires sont exposés
     * (pas de CNI, de secret 2FA, de mot de passe ou de coordonnées personnelles).
     *
     * @return array{title: string, headers: array<int,string>, rows: array<int,array<int,mixed>>}
     */
    private function buildReport(string $type, ?string $dateFrom, ?string $dateTo): array
    {
        return match ($type) {
            'requests' => $this->requestsReport($dateFrom, $dateTo),
            'warehouses' => $this->warehousesReport(),
            'users' => $this->usersReport(),
            'personnel' => $this->personnelReport(),
            'stocks' => $this->stocksReport($dateFrom, $dateTo),
        };
    }

    private function requestsReport(?string $dateFrom, ?string $dateTo): array
    {
        $query = PublicRequest::query()->with('assignedTo')->orderBy('created_at');
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        return [
            'title' => 'Rapport des demandes',
            'headers' => ['Code de suivi', 'Objet', 'Type', 'Région', 'Urgence', 'Étape workflow', 'Assigné à', 'Date'],
            'rows' => $query->get()->map(fn ($r) => [
                $r->tracking_code,
                $r->subject,
                $r->type,
                $r->region,
                $r->urgency,
                $r->workflow_status,
                $r->assignedTo?->name,
                $r->created_at?->format('d/m/Y H:i'),
            ])->all(),
        ];
    }

    private function warehousesReport(): array
    {
        return [
            'title' => 'Rapport des entrepôts',
            'headers' => ['Nom', 'Région', 'Ville', 'Capacité', 'Actif', 'Mouvements de stock'],
            'rows' => Warehouse::withCount('stockMovements')->orderBy('name')->get()->map(fn ($w) => [
                $w->name,
                $w->region,
                $w->city,
                $w->capacity,
                $w->is_active ? 'Oui' : 'Non',
                $w->stock_movements_count,
            ])->all(),
        ];
    }

    private function usersReport(): array
    {
        return [
            'title' => 'Rapport des utilisateurs',
            'headers' => ['Nom', 'Email', 'Rôle', 'Actif', 'Créé le'],
            'rows' => User::query()->orderBy('name')->get(['name', 'email', 'role', 'is_active', 'created_at'])->map(fn ($u) => [
                $u->name,
                $u->email,
                $u->role,
                $u->is_active ? 'Oui' : 'Non',
                $u->created_at?->format('d/m/Y'),
            ])->all(),
        ];
    }

    private function personnelReport(): array
    {
        return [
            'title' => 'Rapport du personnel',
            'headers' => ['Matricule', 'Nom et prénoms', 'Poste', 'Direction / service', 'Statut de validation'],
            'rows' => Personnel::query()->orderBy('prenoms_nom')->get([
                'matricule', 'prenoms_nom', 'poste_actuel', 'direction_service', 'statut_validation',
            ])->map(fn ($p) => [
                $p->matricule,
                $p->prenoms_nom,
                $p->poste_actuel,
                $p->direction_service,
                $p->statut_validation,
            ])->all(),
        ];
    }

    private function stocksReport(?string $dateFrom, ?string $dateTo): array
    {
        $query = StockMovement::query()->with('warehouse')->orderBy('created_at');
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        return [
            'title' => 'Rapport des mouvements de stock',
            'headers' => ['Entrepôt', 'Produit', 'Type', 'Quantité', 'Statut', 'Date'],
            'rows' => $query->get()->map(fn ($m) => [
                $m->warehouse?->name,
                $m->produit,
                $m->type,
                $m->quantity,
                $m->status,
                $m->created_at?->format('d/m/Y H:i'),
            ])->all(),
        ];
    }

    /**
     * CSV avec BOM UTF-8 et séparateur « ; » (ouverture directe dans Excel).
     * Les cellules commençant par = + - @ sont neutralisées (injection de formules).
     */
    private function csvDownload(array $report, string $filename): StreamedResponse
    {
        $content = $this->csvContent($report);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvContent(array $report): string
    {
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $report['headers'], ';');
        foreach ($report['rows'] as $row) {
            fputcsv($out, array_map(function ($value) {
                return is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
            }, $row), ';');
        }
        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content;
    }
}
