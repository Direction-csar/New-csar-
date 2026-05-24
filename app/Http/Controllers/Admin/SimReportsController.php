<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SimReportsController extends Controller
{
    /**
     * Afficher la page des rapports SIM
     */
    public function index(Request $request)
    {
        try {
            // Statistiques
            $stats = $this->getReportsStats();
            
            // Construire la requête
            $query = \App\Models\SimReport::query();
            
            // Filtres
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('type')) {
                $query->where('report_type', $request->get('type'));
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }
            
            if ($request->filled('period')) {
                $period = $request->get('period');
                $now = now();
                
                switch ($period) {
                    case 'week':
                        $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereBetween('created_at', [$now->startOfMonth(), $now->endOfMonth()]);
                        break;
                    case 'quarter':
                        $query->whereBetween('created_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                        break;
                    case 'year':
                        $query->whereBetween('created_at', [$now->startOfYear(), $now->endOfYear()]);
                        break;
                }
            }
            
            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $reports = $query->paginate(15);
            
            return view('admin.sim-reports.index', compact('reports', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@index', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            // Créer un paginator vide au lieu d'une collection
            $reports = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                15,
                1
            );
            
            return view('admin.sim-reports.index', [
                'reports' => $reports,
                'stats' => $this->getDefaultStats()
            ]);
        }
    }
    
    /**
     * Uploader un document de rapport
     */
    public function uploadDocument(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'category' => 'required|in:rapport,bulletin',
                'report_type' => 'required|string|max:100',
                'document' => 'required|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx|mimetypes:application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet|max:51200', // 50 MB max
                'is_public' => 'boolean',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|mimetypes:image/jpeg,image/png,image/gif|max:10240' // 10 MB max pour l'image
            ]);

            // Traitement du document principal
            $documentFile = $request->file('document');
            $documentPath = $documentFile->store('sim-reports/documents', 'public');

            // Traitement de l'image de couverture si fournie
            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                $coverImageFile = $request->file('cover_image');
                $coverImagePath = $coverImageFile->store('sim-reports/covers', 'public');
            }

            // Créer le rapport dans la base de données
            $report = \App\Models\SimReport::create([
                'title' => $request->title,
                'description' => $request->description,
                'report_type' => $request->report_type,
                'category' => $request->category,
                'status' => $request->boolean('is_public') ? 'published' : 'completed',
                'document_file' => $documentPath,
                'cover_image' => $coverImagePath,
                'is_public' => $request->boolean('is_public', false),
                'file_size' => $documentFile->getSize(),
                'created_by' => auth()->id(),
                'generated_by' => auth()->id(),
                'generated_at' => now(),
                'published_at' => $request->boolean('is_public') ? now() : null,
                'metadata' => [
                    'original_filename' => $documentFile->getClientOriginalName(),
                    'mime_type' => $documentFile->getMimeType(),
                    'uploaded_at' => now()->toISOString()
                ]
            ]);

            // Scanner le document uploadé avec ClamAV
            \App\Jobs\ScanUploadedFileJob::dispatch('public', $report->document_file, \App\Models\SimReport::class, $report->id);

            // Créer une notification
            $this->createNotification(
                'Document uploadé',
                "Le document '{$report->title}' a été uploadé avec succès",
                'success'
            );

            return response()->json([
                'success' => true,
                'message' => 'Document uploadé avec succès',
                'data' => [
                    'id' => $report->id,
                    'title' => $report->title,
                    'type' => $report->report_type,
                    'description' => $report->description,
                    'file_size' => $report->formatted_file_size,
                    'is_public' => $report->is_public,
                    'download_url' => $report->download_url,
                    'created_at' => $report->created_at
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@uploadDocument', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->except(['document', 'cover_image'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload du document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un nouveau rapport
     */
    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'report_name' => 'required|string|max:255',
                'report_type' => 'required|string|max:100',
                'report_description' => 'nullable|string|max:1000',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'report_format' => 'required|in:pdf,excel,csv',
                'report_schedule' => 'required|in:now,schedule,recurring',
                'schedule_date' => 'nullable|date|after:now',
                'recurrence' => 'nullable|in:daily,weekly,monthly'
            ]);
            
            // Utiliser le service SimReportService pour générer le rapport
            $reportService = new \App\Services\SimReportService();
            
            $reportData = [
                'title' => $request->report_name,
                'description' => $request->report_description,
                'report_type' => $request->report_type,
                'period_start' => Carbon::parse($request->date_from),
                'period_end' => Carbon::parse($request->date_to),
                'region' => null, // À adapter selon vos besoins
                'market_sector' => 'all',
                'data_sources' => ['database', 'api'],
                'document_file' => null,
                'is_public' => $request->has('is_public') ? true : false,
                'cover_image' => null
            ];
            
            // Générer le rapport avec de vraies données
            $report = $reportService->generateReport($reportData);
            
            // Générer le fichier selon le format demandé
            $reportFile = $this->generateReportFile($report, $request->report_format, $request->report_type);
            
            // Créer une notification
            $this->createNotification(
                'Rapport généré',
                "Le rapport '{$report->title}' a été généré avec succès",
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Rapport généré avec succès',
                'data' => [
                    'id' => $report->id,
                    'name' => $report->title,
                    'type' => $report->report_type,
                    'description' => $report->description,
                    'date_from' => $report->period_start,
                    'date_to' => $report->period_end,
                    'format' => $request->report_format,
                    'status' => $report->status,
                    'download_url' => $reportFile['url'],
                    'filename' => $reportFile['filename'],
                    'created_at' => $report->created_at,
                    'updated_at' => $report->updated_at
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@generateReport', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer le fichier de rapport
     */
    private function generateReportFile($report, $format, $type)
    {
        $filename = "rapport_sim_{$type}_" . date('Y-m-d_H-i-s') . ".{$format}";
        $filePath = storage_path("app/reports/{$filename}");

        // Créer le dossier s'il n'existe pas
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        // Préparer les données pour l'export
        $data = [
            'period' => [
                'from' => $report->period_start->format('Y-m-d'),
                'to' => $report->period_end->format('Y-m-d'),
                'type' => $type
            ],
            'summary' => $report->summary ?? 'Résumé non disponible',
            'details' => $report->indicators_data ?? [],
            'recommendations' => $report->recommendations ?? 'Recommandations non disponibles'
        ];

        switch ($format) {
            case 'pdf':
                $this->generatePdfReport($data, $filePath, $type, $report);
                break;
            case 'excel':
                $this->generateExcelReport($data, $filePath, $type, $report);
                break;
            case 'csv':
                $this->generateCsvReport($data, $filePath, $type, $report);
                break;
        }

        return [
            'filename' => $filename,
            'path' => $filePath,
            'url' => route('admin.sim-reports.download', ['id' => $report->id])
        ];
    }

    /**
     * Générer un rapport PDF
     */
    private function generatePdfReport($data, $filePath, $type, $report)
    {
        $html = view('admin.reports.sim-pdf-template', compact('data', 'type', 'report'))->render();
        file_put_contents($filePath, $html);
    }

    /**
     * Générer un rapport Excel
     */
    private function generateExcelReport($data, $filePath, $type, $report)
    {
        $csv = $this->arrayToCsv($data, $report);
        file_put_contents($filePath, $csv);
    }

    /**
     * Générer un rapport CSV
     */
    private function generateCsvReport($data, $filePath, $type, $report)
    {
        $csv = $this->arrayToCsv($data, $report);
        file_put_contents($filePath, $csv);
    }

    /**
     * Convertir un tableau en CSV
     */
    private function arrayToCsv($data, $report)
    {
        $csv = "Rapport SIM CSAR - " . date('Y-m-d H:i:s') . "\n";
        $csv .= "Titre: {$report->title}\n";
        $csv .= "Période: {$data['period']['from']} à {$data['period']['to']}\n";
        $csv .= "Type: {$data['period']['type']}\n\n";
        
        $csv .= "RÉSUMÉ\n";
        $csv .= $data['summary'] . "\n\n";
        
        $csv .= "RECOMMANDATIONS\n";
        $csv .= $data['recommendations'] . "\n\n";
        
        $csv .= "DONNÉES DÉTAILLÉES\n";
        $csv .= json_encode($data['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return $csv;
    }
    
    /**
     * Afficher les détails d'un rapport
     */
    public function show($id)
    {
        try {
            // Récupérer le rapport depuis la base de données
            $report = \App\Models\SimReport::findOrFail($id);
            $this->authorize('view', $report);
            
            // Transformer en tableau pour la vue
            $reportData = [
                'id' => $report->id,
                'title' => $report->title,
                'name' => $report->title, // Alias pour compatibilité
                'type' => $report->report_type,
                'description' => $report->description,
                'summary' => $report->summary,
                'content' => $report->content ?? $report->summary ?? 'Contenu non disponible',
                'status' => $report->status,
                'is_public' => $report->is_public,
                'is_published' => $report->is_published,
                'published_at' => $report->published_at,
                'generated_at' => $report->generated_at,
                'generated_by' => $report->generated_by ? \App\Models\User::find($report->generated_by)->name ?? 'Utilisateur inconnu' : 'Système',
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
                'view_count' => $report->view_count ?? 0,
                'download_count' => $report->download_count ?? 0,
                'document_file' => $report->document_file,
                'cover_image' => $report->cover_image,
                'file_size' => $report->file_size,
                'formatted_file_size' => $report->formatted_file_size ?? 'N/A',
                'download_url' => $report->download_url ?? '#',
                'cover_image_url' => $report->cover_image_url ?? null,
                'progress' => $report->status === 'completed' ? 100 : ($report->status === 'generating' ? 50 : 0),
                'file_path' => $report->document_file ? storage_path('app/public/' . $report->document_file) : null
            ];
            
            return view('admin.sim-reports.show', ['report' => $reportData]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Rapport SIM non trouvé', [
                'report_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.sim-reports.index')
                ->with('error', 'Rapport non trouvé');
                
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@show', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'report_id' => $id
            ]);
            
            return redirect()->route('admin.sim-reports.index')
                ->with('error', 'Erreur lors du chargement du rapport');
        }
    }
    
    /**
     * Télécharger un rapport
     */
    public function download($id)
    {
        try {
            // Récupérer le rapport depuis la base de données
            $report = \App\Models\SimReport::findOrFail($id);
            
            // Vérifier si le fichier existe
            if (!$report->document_file || !file_exists(storage_path('app/' . $report->document_file))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier de rapport non trouvé'
                ], 404);
            }

            // Incrémenter le compteur de téléchargements
            $report->increment('download_count');

            // Retourner le fichier pour téléchargement
            return response()->download(
                storage_path('app/' . $report->document_file),
                "rapport_sim_{$report->report_type}_{$report->id}.pdf"
            );
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapport non trouvé'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@download', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'report_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement'
            ], 500);
        }
    }
    
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $report = \App\Models\SimReport::findOrFail($id);
            $this->authorize('update', $report);
            return view('admin.sim-reports.edit', compact('report'));
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@edit', [
                'error' => $e->getMessage(),
                'report_id' => $id
            ]);
            
            return redirect()->route('admin.sim-reports.index')
                           ->with('error', 'Rapport non trouvé');
        }
    }
    
    /**
     * Mettre à jour un rapport
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'summary' => 'nullable|string|max:2000',
                'report_type' => 'required|string|max:100',
                'category' => 'nullable|in:rapport,bulletin',
                'is_public' => 'boolean',
                'status' => 'required|in:draft,generating,completed,published'
            ]);

            $report = \App\Models\SimReport::findOrFail($id);
            $this->authorize('update', $report);

            $isPublic = $request->boolean('is_public');
            $status = $request->status;
            // Si public coché et status non-publié → forcer publié
            if ($isPublic && $status !== 'published') {
                $status = 'published';
            }

            $report->update([
                'title' => $request->title,
                'description' => $request->description,
                'summary' => $request->summary,
                'report_type' => $request->report_type,
                'category' => $request->category ?? $report->category ?? 'rapport',
                'is_public' => $isPublic,
                'status' => $status,
                'published_at' => $status === 'published' ? ($report->published_at ?? now()) : null
            ]);
            
            // Créer une notification
            $this->createNotification(
                'Rapport mis à jour',
                "Le rapport '{$report->title}' a été mis à jour avec succès",
                'success'
            );
            
            return redirect()->route('admin.sim-reports.index')
                           ->with('success', 'Rapport mis à jour avec succès');
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@update', [
                'error' => $e->getMessage(),
                'report_id' => $id
            ]);
            
            return redirect()->back()
                           ->with('error', 'Erreur lors de la mise à jour du rapport')
                           ->withInput();
        }
    }
    
    /**
     * Supprimer un rapport
     */
    public function destroy($id)
    {
        try {
            // Récupérer le rapport depuis la base de données
            $report = \App\Models\SimReport::findOrFail($id);
            $this->authorize('delete', $report);
            $reportName = $report->title;
            
            // Supprimer le fichier physique s'il existe
            if ($report->document_file && file_exists(storage_path('app/' . $report->document_file))) {
                unlink(storage_path('app/' . $report->document_file));
            }
            
            // Supprimer l'image de couverture s'il existe
            if ($report->cover_image && file_exists(storage_path('app/public/' . $report->cover_image))) {
                unlink(storage_path('app/public/' . $report->cover_image));
            }
            
            // Supprimer le rapport de la base de données
            $report->delete();
            
            // Créer une notification
            $this->createNotification(
                'Rapport supprimé',
                "Le rapport '{$reportName}' a été supprimé avec succès",
                'warning'
            );
            
            // Log de l'action
            Log::info('Rapport SIM supprimé', [
                'report_id' => $id,
                'report_title' => $reportName,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Rapport supprimé avec succès'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rapport non trouvé'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@destroy', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'report_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rapport'
            ], 500);
        }
    }
    
    /**
     * Programmer un rapport
     */
    public function schedule(Request $request, $id)
    {
        try {
            $request->validate([
                'schedule_date' => 'required|date|after:now',
                'recurrence' => 'nullable|in:daily,weekly,monthly'
            ]);
            
            // Simulation de programmation
            $report = [
                'id' => $id,
                'schedule_date' => Carbon::parse($request->schedule_date),
                'recurrence' => $request->recurrence,
                'status' => 'scheduled',
                'updated_at' => now()
            ];
            
            // Créer une notification
            $this->createNotification(
                'Rapport programmé',
                "Le rapport a été programmé avec succès",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Rapport programmé avec succès',
                'data' => $report
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@schedule', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'report_id' => $id,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la programmation du rapport'
            ], 500);
        }
    }
    
    /**
     * Exporter tous les rapports
     */
    public function exportAll(Request $request)
    {
        try {
            $format = $request->get('format', 'zip');
            
            // Simulation d'export
            $reports = []; // Liste des rapports
            
            // Créer une notification
            $this->createNotification(
                'Export des rapports',
                "L'export de tous les rapports a été initié",
                'info'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Export des rapports initié',
                'download_url' => '/download/reports-export.zip'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@exportAll', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des rapports'
            ], 500);
        }
    }
    
    /**
     * Obtenir le statut d'un rapport
     */
    public function getStatus($id)
    {
        try {
            // Simulation de récupération du statut
            $status = [
                'id' => $id,
                'status' => 'completed',
                'progress' => 100,
                'message' => 'Rapport terminé avec succès'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@getStatus', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'report_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du statut'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques des rapports
     */
    public function getStats()
    {
        try {
            $stats = $this->getReportsStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans SimReportsController@getStats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
    
    /**
     * Obtenir les statistiques des rapports
     */
    private function getReportsStats()
    {
        try {
            if (!\Schema::hasTable('sim_reports')) {
                return $this->getDefaultStats();
            }

            return [
                'total_reports' => \App\Models\SimReport::count(),
                'completed_reports' => \App\Models\SimReport::where('status', 'completed')->count(),
                'pending_reports' => \App\Models\SimReport::where('status', 'generating')->count(),
                'scheduled_reports' => \App\Models\SimReport::where('status', 'scheduled')->count(),
                'published_reports' => \App\Models\SimReport::where('status', 'published')->count(),
                'total_downloads' => \App\Models\SimReport::sum('download_count') ?? 0,
                'total_views' => \App\Models\SimReport::sum('view_count') ?? 0
            ];
        } catch (\Exception $e) {
            Log::warning('Erreur lors de la collecte des statistiques de rapports', ['error' => $e->getMessage()]);
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Statistiques par défaut
     */
    private function getDefaultStats()
    {
        return [
            'total_reports' => 0,
            'completed_reports' => 0,
            'pending_reports' => 0,
            'scheduled_reports' => 0
        ];
    }
    
    /**
     * Créer une notification
     */
    private function createNotification($title, $message, $type = 'info')
    {
        try {
            if (class_exists('App\Models\Notification')) {
                \App\Models\Notification::create([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'user_id' => auth()->id(),
                    'read' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de notification', [
                'error' => $e->getMessage()
            ]);
        }
    }
}