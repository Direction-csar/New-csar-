<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    /**
     * Afficher la page publique des rapports SIM
     */
    public function index(Request $request)
    {
        try {
            // Récupérer les rapports SIM publiés depuis la base de données
            $reports = \App\Models\SimReport::where('status', 'published')
                ->where('is_public', true)
                ->where('category', 'rapport')
                ->orderBy('published_at', 'desc')
                ->get()
                ->filter(function($report) {
                    return $report->document_file && Storage::disk('public')->exists($report->document_file);
                })
                ->values()
                ->map(function($report) {
                    return (object)[
                        'id' => $report->id,
                        'title' => $report->title,
                        'description' => $report->description,
                        'cover_image' => $report->cover_image ? asset('storage/' . $report->cover_image) : asset('images/default-report.jpg'),
                        'document_file' => $report->document_file,
                        'download_url' => $report->public_download_url,
                        'published_at' => $report->published_at,
                        'download_count' => $report->download_count,
                        'view_count' => $report->view_count
                    ];
                });

            // Si aucun rapport n'est trouvé, retourner une collection vide
            if ($reports->isEmpty()) {
                $reports = collect([]);
            }

            // Statistiques
            $stats = [
                'total' => $reports->count(),
                'total_downloads' => $reports->sum('download_count'),
                'total_views' => $reports->sum('view_count'),
                'latest_report' => $reports->first()
            ];

            Log::info('Accès à la page rapports SIM publique', [
                'ip' => request()->ip(),
                'reports_count' => $reports->count()
            ]);

            return view('public.rapports', compact('reports', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des rapports SIM publics', [
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return view('public.rapports', [
                'reports' => collect(),
                'stats' => ['total' => 0, 'total_downloads' => 0, 'total_views' => 0, 'latest_report' => null]
            ]);
        }
    }

    /**
     * Télécharger un rapport SIM
     */
    public function download($id)
    {
        try {
            $report = \App\Models\SimReport::where('status', 'published')
                ->where('is_public', true)
                ->findOrFail($id);

            if (!$report->document_file || !file_exists(storage_path('app/public/' . $report->document_file))) {
                return redirect()->back()->with('error', 'Fichier de rapport non trouvé.');
            }

            // Incrémenter le compteur de téléchargements
            $report->increment('download_count');

            Log::info('Téléchargement rapport SIM public', [
                'report_id' => $id,
                'ip' => request()->ip()
            ]);

            return response()->download(
                storage_path('app/public/' . $report->document_file),
                "rapport_sim_{$report->title}_{$report->id}.pdf"
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du rapport SIM', [
                'report_id' => $id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()->with('error', 'Erreur lors du téléchargement du rapport.');
        }
    }
}
