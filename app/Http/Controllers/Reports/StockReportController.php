<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\StockReportExport;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Reports\StockReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Module « Rapports et statistiques » — portails DG et DTL.
 * Génération PDF (Dompdf) et XLSX (PhpSpreadsheet), historique journalisé.
 */
class StockReportController extends Controller
{
    public function __construct(private StockReportService $reports) {}

    private function portal(): string
    {
        return request()->routeIs('dg.*') ? 'dg' : 'dtl';
    }

    private function user()
    {
        return Auth::guard($this->portal())->user() ?? Auth::user();
    }

    /** Catalogue + historique. */
    public function index()
    {
        $portal = $this->portal();
        $catalog = $this->reports->catalog($portal);
        $history = StockReportExport::with('user:id,name')
            ->where('portal', $portal)->latest('id')->limit(50)->get();

        $regions = Warehouse::where('status', 'active')->whereNotNull('region')
            ->distinct()->orderBy('region')->pluck('region');
        $warehouses = Warehouse::where('status', 'active')->orderBy('name')->get(['id', 'name', 'region']);
        $inspectors = $portal === 'dtl'
            ? User::where('role', 'inspecteur')->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('reports.stock', [
            'portal' => $portal,
            'portalLabel' => $portal === 'dg' ? 'Direction Générale' : 'DTL — Pilotage',
            'catalog' => $catalog,
            'history' => $history,
            'regions' => $regions,
            'warehouses' => $warehouses,
            'inspectors' => $inspectors,
            'produits' => [],
            'statuts' => \App\Models\StockOperation::STATUTS,
        ]);
    }

    /** Génère et enregistre un export (PDF ou XLSX), puis télécharge. */
    public function generate(Request $request)
    {
        $portal = $this->portal();
        $data = $request->validate([
            'report_type' => 'required|string|max:60',
            'format' => 'required|in:pdf,xlsx',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'region' => 'nullable|string|max:120',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'produit' => 'nullable|string|max:120',
            'statut' => 'nullable|string|max:40',
            'inspecteur_id' => 'nullable|integer|exists:users,id',
        ]);

        abort_unless($this->reports->isValid($portal, $data['report_type']), 422, 'Type de rapport non autorisé.');

        $filters = array_filter([
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
            'region' => $data['region'] ?? null,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'produit' => $data['produit'] ?? null,
            'statut' => $data['statut'] ?? null,
            'inspecteur_id' => $data['inspecteur_id'] ?? null,
        ]);

        [$columns, $rows] = $this->reports->dataset($data['report_type'], $filters);
        $title = $this->reports->catalog($portal)[$data['report_type']];
        $reference = 'RAP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $user = $this->user();

        $filename = Str::slug($title) . '-' . $reference . '.' . $data['format'];
        $path = 'reports/' . $portal . '/' . $filename;

        if ($data['format'] === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', [
                'title' => $title,
                'reference' => $reference,
                'generatedAt' => now(),
                'filters' => $filters,
                'columns' => $columns,
                'rows' => $rows,
                'portalLabel' => $portal === 'dg' ? 'Direction Générale' : 'DTL — Pilotage opérationnel',
            ])->setPaper('a4', 'landscape');
            Storage::disk('local')->put($path, $pdf->output());
        } else {
            $this->writeXlsx($columns, $rows, $title, $reference, $filters, $path);
        }

        StockReportExport::create([
            'reference' => $reference,
            'user_id' => $user->id,
            'portal' => $portal,
            'report_type' => $data['report_type'],
            'filters' => $filters ?: null,
            'format' => $data['format'],
            'filename' => $filename,
            'path' => $path,
        ]);

        return Storage::disk('local')->download($path, $filename);
    }

    /** Retélécharge un rapport généré (même portail, fichier existant). */
    public function download(int $export)
    {
        $portal = $this->portal();
        $record = StockReportExport::where('portal', $portal)->findOrFail($export);

        abort_unless(Storage::disk('local')->exists($record->path), 404);

        return Storage::disk('local')->download($record->path, $record->filename);
    }

    private function writeXlsx(array $columns, array $rows, string $title, string $reference, array $filters, string $path): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rapport');

        $sheet->setCellValue('A1', 'Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->setCellValue('A2', $title);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->setCellValue('A3', 'Référence : ' . $reference . ' — Généré le ' . now()->format('d/m/Y H:i'));
        if ($filters) {
            $sheet->setCellValue('A4', 'Filtres : ' . json_encode($filters, JSON_UNESCAPED_UNICODE));
        }

        $colLetter = fn (int $i) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        $row = 6;
        foreach ($columns as $i => $col) {
            $sheet->setCellValue($colLetter($i) . $row, $col);
        }
        $sheet->getStyle('A' . $row . ':' . $colLetter(count($columns) - 1) . $row)->getFont()->setBold(true);

        foreach ($rows as $r) {
            $row++;
            foreach (array_values($r) as $i => $val) {
                $sheet->setCellValue($colLetter($i) . $row, $val);
            }
        }

        foreach (range(1, count($columns)) as $i) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($tmp);
        Storage::disk('local')->put($path, file_get_contents($tmp));
        @unlink($tmp);
    }
}
