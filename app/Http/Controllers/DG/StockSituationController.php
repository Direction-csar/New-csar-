<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Services\Magasin\WarehouseStockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /** Export CSV de la situation (ouvrable dans Excel, séparateur « ; », BOM UTF-8). */
    public function exportCsv(): StreamedResponse
    {
        $s = $this->stock->situation();
        $filename = 'situation-stocks-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($s) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['CSAR — Situation des stocks au ' . $s['generated_at']], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['Magasin', 'Région', 'Produit', 'Format', 'Unité', 'Contenants', 'Quantité'], ';');

            foreach ($s['warehouses'] as $w) {
                foreach ($w['products'] as $p) {
                    fputcsv($out, [
                        $w['name'], $w['region'] ?? '', $p['name'], $p['format'],
                        $p['unit'], $p['contenants'], $p['quantite'],
                    ], ';');
                }
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['TOTAL', '', '', '', '', '', ''], ';');
            foreach ($s['totals'] as $t) {
                fputcsv($out, ['', '', $t['name'], '', $t['unit'], $t['contenants'], $t['quantite']], ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Export PDF de la situation des stocks. */
    public function exportPdf()
    {
        $s = $this->stock->situation();
        $pdf = Pdf::loadView('dg.stocks.situation-pdf', ['s' => $s])
            ->setPaper('a4', 'landscape');

        return $pdf->download('situation-stocks-' . now()->format('Ymd-His') . '.pdf');
    }
}
