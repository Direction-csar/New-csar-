<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\PublicRequest;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemandesController extends Controller
{
    /**
     * Afficher la liste des demandes
     */
    public function index(Request $request)
    {
        try {
            // Utiliser la table public_requests au lieu de demandes
            $query = PublicRequest::query();

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('statut')) {
                $query->where('status', $request->statut);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('region')) {
                $query->where('region', $request->region);
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $demandes = $query->paginate(15);

            // Statistiques
            $stats = $this->getDemandesStats();

            // Données pour les graphiques
            $chartData = $this->getChartData();

            return view('admin.demandes.index', compact('demandes', 'stats', 'chartData'));
        } catch (\Exception $e) {
            Log::error('Erreur dans DemandesController@index: ' . $e->getMessage());
            Log::error('Détails de l\'erreur: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Erreur lors du chargement des demandes.');
        }
    }

    /**
     * Afficher une demande spécifique
     */
    public function show($id)
    {
        try {
            $demande = PublicRequest::find($id);
            if (!$demande) {
                return redirect()->route('admin.demandes.index')
                    ->with('error', 'Demande non trouvée.');
            }
            return view('admin.demandes.show', compact('demande'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la demande: ' . $e->getMessage());
            return redirect()->route('admin.demandes.index')
                ->with('error', 'Demande non trouvée.');
        }
    }

    /**
     * Télécharger le PDF d'une demande
     */
    public function downloadPdf($id)
    {
        try {
            $demande = PublicRequest::find($id);
            if (!$demande) {
                return redirect()->route('admin.demandes.index')
                    ->with('error', 'Demande non trouvée.');
            }
            
            return $this->generateDemandePdf($demande);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du PDF.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $demande = PublicRequest::findOrFail($id);
            $users = User::where('role', '!=', 'admin')->get();
            return view('admin.demandes.edit', compact('demande', 'users'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'édition de la demande: ' . $e->getMessage());
            return redirect()->route('admin.demandes.index')
                ->with('error', 'Demande non trouvée.');
        }
    }

    /**
     * Mettre à jour une demande
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|string|in:pending,approved,rejected,completed',
            'admin_comment' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $demande = PublicRequest::findOrFail($id);
            $oldStatut = $demande->status;

            $updateData = [];
            if ($request->filled('status')) {
                $updateData['status'] = $request->status;
                $updateData['updated_at'] = now();
            }
            if ($request->filled('admin_comment')) {
                $updateData['admin_comment'] = $request->admin_comment;
            }
            if ($request->filled('assigned_to')) {
                $updateData['assigned_to'] = $request->assigned_to;
            }

            // Synchroniser le workflow_status si le statut legacy change
            if (isset($updateData['status']) && $oldStatut !== $updateData['status']) {
                $workflowMap = [
                    'pending' => 'en_revue',
                    'approved' => 'approuvee',
                    'rejected' => 'rejetee',
                    'completed' => 'cloturee',
                ];
                if (isset($workflowMap[$updateData['status']]) && $demande->workflow_status !== $workflowMap[$updateData['status']]) {
                    $demande->advanceWorkflow($workflowMap[$updateData['status']], 'Synchronisé depuis le changement de statut admin');
                }
            }

            $demande->update($updateData);

            // Créer une notification si le statut a changé
            if (isset($updateData['status']) && $oldStatut !== $updateData['status']) {
                Notification::create([
                    'type' => 'demande_updated',
                    'title' => 'Demande mise à jour',
                    'message' => "La demande {$demande->tracking_code} a été mise à jour",
                    'user_id' => null
                ]);
            }

            DB::commit();

            return redirect()->route('admin.demandes.index')
                ->with('success', 'Demande mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la demande: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de la demande.');
        }
    }

    /**
     * Supprimer une demande
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $demande = PublicRequest::findOrFail($id);
            $codeSuivi = $demande->tracking_code ?? "ID-{$id}";
            $demande->delete();

            // Créer une notification
            Notification::create([
                'type' => 'demande_deleted',
                'title' => 'Demande supprimée',
                'message' => "La demande {$codeSuivi} a été supprimée",
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.demandes.index')
                ->with('success', 'Demande supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la demande: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la demande.');
        }
    }

    /**
     * Approuver une demande
     */
    public function approve($id)
    {
        try {
            DB::beginTransaction();
            
            $demande = PublicRequest::findOrFail($id);
            $demande->update([
                'status' => 'approved',
                'processed_date' => now(),
                'updated_at' => now()
            ]);
            
            // Créer une notification
            Notification::create([
                'type' => 'demande_approved',
                'title' => 'Demande approuvée',
                'message' => "La demande {$demande->tracking_code} de {$demande->full_name} a été approuvée",
                'icon' => 'check-circle',
                'read' => false,
                'user_id' => null
            ]);
            
            DB::commit();
            
            Log::info("Demande approuvée", [
                'demande_id' => $id,
                'tracking_code' => $demande->tracking_code,
                'admin_id' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('success', 'Demande approuvée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'approbation: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'approbation de la demande.');
        }
    }

    /**
     * Supprimer plusieurs demandes en masse
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'demande_ids' => 'required|array',
            'demande_ids.*' => 'exists:public_requests,id'
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            foreach ($request->demande_ids as $id) {
                $demande = PublicRequest::findOrFail($id);
                $codeSuivi = $demande->tracking_code ?? "ID-{$id}";
                $demande->delete();
                $deletedCount++;
            }

            // Créer une notification
            Notification::create([
                'type' => 'demandes_bulk_deleted',
                'title' => 'Demandes supprimées en masse',
                'message' => "{$deletedCount} demande(s) ont été supprimée(s)",
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.demandes.index')
                ->with('success', "{$deletedCount} demande(s) supprimée(s) avec succès.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression en masse: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression des demandes.');
        }
    }

    /**
     * Rejeter une demande
     */
    public function reject($id)
    {
        try {
            DB::beginTransaction();
            
            $demande = PublicRequest::findOrFail($id);
            $demande->update([
                'status' => 'rejected',
                'processed_date' => now(),
                'updated_at' => now()
            ]);
            
            // Créer une notification
            Notification::create([
                'type' => 'demande_rejected',
                'title' => 'Demande rejetée',
                'message' => "La demande {$demande->tracking_code} de {$demande->full_name} a été rejetée",
                'icon' => 'times-circle',
                'read' => false,
                'user_id' => null
            ]);
            
            DB::commit();
            
            Log::info("Demande rejetée", [
                'demande_id' => $id,
                'tracking_code' => $demande->tracking_code,
                'admin_id' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('success', 'Demande rejetée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du rejet: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors du rejet de la demande.');
        }
    }

    /**
     * Obtenir les statistiques des demandes
     */
    private function getDemandesStats()
    {
        // Utiliser PublicRequest au lieu de DB::table('demandes')
        return [
            'total' => PublicRequest::count(),
            'en_attente' => PublicRequest::where('status', 'pending')->count(),
            'en_cours' => PublicRequest::where('status', 'processing')->count(),
            'approuvees' => PublicRequest::where('status', 'approved')->count(),
            'rejetees' => PublicRequest::where('status', 'rejected')->count(),
            'terminees' => PublicRequest::where('status', 'approved')->count(),
            'ce_mois' => PublicRequest::whereMonth('created_at', now()->month)->count(),
            'cette_semaine' => PublicRequest::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'non_vues' => PublicRequest::where('is_viewed', false)->count(),
            'pending' => PublicRequest::where('status', 'pending')->count(), // Alias pour compatibilité
            'approved' => PublicRequest::where('status', 'approved')->count(), // Alias pour compatibilité
            'rejected' => PublicRequest::where('status', 'rejected')->count() // Alias pour compatibilité
        ];
    }

    /**
     * Obtenir les données pour les graphiques
     */
    private function getChartData()
    {
        return [
            'statuts' => [
                'en_attente' => PublicRequest::where('status', 'pending')->count(),
                'en_cours' => PublicRequest::where('status', 'processing')->count(),
                'approuvees' => PublicRequest::where('status', 'approved')->count(),
                'rejetees' => PublicRequest::where('status', 'rejected')->count(),
                'terminees' => PublicRequest::where('status', 'approved')->count()
            ],
            'types' => PublicRequest::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'regions' => PublicRequest::select('region', DB::raw('count(*) as count'))
                ->whereNotNull('region')
                ->groupBy('region')
                ->pluck('count', 'region')
                ->toArray(),
            'evolution' => PublicRequest::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray()
        ];
    }

    /**
     * Exporter les demandes
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'excel');
            $filters = $request->only(['statut', 'workflow_status', 'is_duplicate', 'region', 'type_demande', 'date_from', 'date_to', 'search']);

            // Construire la requête avec les mêmes filtres que l'index
            $query = PublicRequest::query();

            // Appliquer les filtres
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if (!empty($filters['statut'])) {
                $query->where('status', $filters['statut']);
            }

            if (!empty($filters['workflow_status'])) {
                $query->where('workflow_status', $filters['workflow_status']);
            }

            if (isset($filters['is_duplicate']) && $filters['is_duplicate'] !== '') {
                $query->where('is_duplicate', $filters['is_duplicate']);
            }

            if (!empty($filters['type_demande'])) {
                $query->where('type', $filters['type_demande']);
            }

            if (!empty($filters['region'])) {
                $query->where('region', $filters['region']);
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }

            // Récupérer toutes les demandes (sans pagination pour l'export)
            $demandes = $query->orderBy('created_at', 'desc')->get();

            // Vérifier s'il y a des données à exporter
            if ($demandes->isEmpty()) {
                return redirect()->back()->with('error', 'Aucune donnée à exporter pour le moment.');
            }

            // Exporter selon le format
            if ($format === 'excel') {
                return $this->exportToExcel($demandes);
            } elseif ($format === 'csv') {
                return $this->exportToCsv($demandes);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export des demandes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'export des demandes.');
        }
    }

    /**
     * Exporter vers Excel (PublicRequest avec colonnes workflow)
     */
    private function exportToExcel($demandes)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Demandes');

        // En-têtes
        $headers = [
            'Code de Suivi', 'Nom Complet', 'Email', 'Téléphone', 'Type', 'Objet',
            'Statut Legacy', 'Statut Workflow', 'Région', 'Adresse', 'Description',
            'Urgence', 'Référence Courrier', 'Date Courrier',
            'Document Signé', 'Document Scanné', 'Validée par DG', 'Date Validation DG',
            'Commentaire Admin', 'Date de Création', 'Date de Mise à Jour'
        ];

        // Style en-têtes
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2c5282']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];

        $col = 1;
        foreach ($headers as $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($header);
            $cell->getStyle()->applyFromArray($headerStyle);
            $col++;
        }

        // Ajouter les données
        $row = 2;
        foreach ($demandes as $demande) {
            $sheet->setCellValue('A' . $row, $demande->tracking_code ?? 'N/A');
            $sheet->setCellValue('B' . $row, $demande->full_name ?? 'N/A');
            $sheet->setCellValue('C' . $row, $demande->email ?? 'N/A');
            $sheet->setCellValue('D' . $row, $demande->phone ?? 'N/A');
            $sheet->setCellValue('E' . $row, $demande->type ?? 'N/A');
            $sheet->setCellValue('F' . $row, $demande->subject ?? 'N/A');
            $sheet->setCellValue('G' . $row, $demande->status ?? 'N/A');
            $sheet->setCellValue('H' . $row, $demande->workflow_status_label ?? 'N/A');
            $sheet->setCellValue('I' . $row, $demande->region ?? 'N/A');
            $sheet->setCellValue('J' . $row, $demande->address ?? 'N/A');
            $sheet->setCellValue('K' . $row, $demande->description ?? 'N/A');
            $sheet->setCellValue('L' . $row, $demande->urgency ?? 'N/A');
            $sheet->setCellValue('M' . $row, $demande->courier_reference ?? 'N/A');
            $sheet->setCellValue('N' . $row, $demande->courier_date ? $demande->courier_date->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('O' . $row, $demande->dg_signature_file ? 'Oui' : 'Non');
            $sheet->setCellValue('P' . $row, $demande->scan_file ? 'Oui' : 'Non');
            $sheet->setCellValue('Q' . $row, $demande->dgApprover?->name ?? 'N/A');
            $sheet->setCellValue('R' . $row, $demande->dg_approved_at ? $demande->dg_approved_at->format('d/m/Y H:i') : 'N/A');
            $sheet->setCellValue('S' . $row, $demande->admin_comment ?? 'N/A');
            $sheet->setCellValue('T' . $row, $demande->created_at ? $demande->created_at->format('d/m/Y H:i') : 'N/A');
            $sheet->setCellValue('U' . $row, $demande->updated_at ? $demande->updated_at->format('d/m/Y H:i') : 'N/A');
            $row++;
        }

        // Ajuster la largeur des colonnes
        foreach (range('A', 'U') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Figer la première ligne
        $sheet->freezePane('A2');

        // Créer le fichier
        $filename = 'demandes_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Exporter vers CSV
     */
    private function exportToCsv($demandes)
    {
        $filename = 'demandes_export_' . date('Y-m-d_H-i-s') . '.csv';

        $callback = function() use ($demandes) {
            $file = fopen('php://output', 'w');

            // BOM pour UTF-8
            fwrite($file, "\xEF\xBB\xBF");

            // En-têtes
            $headers = [
                'Code de Suivi', 'Nom Complet', 'Email', 'Téléphone', 'Type', 'Objet',
                'Statut Legacy', 'Statut Workflow', 'Région', 'Adresse', 'Description',
                'Urgence', 'Référence Courrier', 'Date Courrier',
                'Document Signé', 'Document Scanné', 'Validée par DG', 'Date Validation DG',
                'Commentaire Admin', 'Date de Création', 'Date de Mise à Jour'
            ];
            fputcsv($file, $headers, ';');

            // Données
            foreach ($demandes as $demande) {
                $rowData = [
                    $demande->tracking_code ?? 'N/A',
                    $demande->full_name ?? 'N/A',
                    $demande->email ?? 'N/A',
                    $demande->phone ?? 'N/A',
                    $demande->type ?? 'N/A',
                    $demande->subject ?? 'N/A',
                    $demande->status ?? 'N/A',
                    $demande->workflow_status_label ?? 'N/A',
                    $demande->region ?? 'N/A',
                    $demande->address ?? 'N/A',
                    $demande->description ?? 'N/A',
                    $demande->urgency ?? 'N/A',
                    $demande->courier_reference ?? 'N/A',
                    $demande->courier_date ? $demande->courier_date->format('d/m/Y') : 'N/A',
                    $demande->dg_signature_file ? 'Oui' : 'Non',
                    $demande->scan_file ? 'Oui' : 'Non',
                    $demande->dgApprover?->name ?? 'N/A',
                    $demande->dg_approved_at ? $demande->dg_approved_at->format('d/m/Y H:i') : 'N/A',
                    $demande->admin_comment ?? 'N/A',
                    $demande->created_at ? $demande->created_at->format('d/m/Y H:i') : 'N/A',
                    $demande->updated_at ? $demande->updated_at->format('d/m/Y H:i') : 'N/A',
                ];
                fputcsv($file, $rowData, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Générer le PDF d'une demande
     */
    private function generateDemandePdf($demande)
    {
        try {
            $html = $this->generateDemandeHtml($demande);
            
            // Créer le PDF avec DomPDF ou fallback vers HTML
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait');
                
                return $pdf->download('demande_' . $demande->tracking_code . '.pdf');
            } else {
                // Fallback vers HTML si DomPDF n'est pas disponible
                return response($html)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="demande_' . $demande->tracking_code . '.html"');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF: ' . $e->getMessage());
            
            // Fallback vers texte simple
            $content = $this->generateSimpleDemande($demande);
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="demande_' . $demande->tracking_code . '.txt"');
        }
    }

    /**
     * Générer le HTML d'une demande
     */
    private function generateDemandeHtml($demande)
    {
        // Utiliser le logo CSAR disponible
        $logoPath = public_path('images/logos/LOGO CSAR vectoriel-01.png');
        $logoBase64 = '';
        
        if (file_exists($logoPath)) {
            $logoContent = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $statutLabels = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'traitee' => 'Traitée',
            'rejetee' => 'Rejetée'
        ];
        
        $statutLabel = $statutLabels[$demande->statut] ?? 'En attente';
        $statutColor = match($demande->statut) {
            'traitee' => '#28a745',
            'rejetee' => '#dc3545',
            'en_cours' => '#17a2b8',
            default => '#ffc107'
        };
        
        $createdAt = \Carbon\Carbon::parse($demande->created_at);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Demande - ' . $demande->tracking_code . '</title>
            <style>
                @page {
                    margin: 20mm;
                    size: A4;
                }
                body {
                    font-family: "Segoe UI", Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background: white;
                    color: #333;
                    line-height: 1.4;
                }
                .demande-container {
                    width: 100%;
                    max-width: 210mm;
                    margin: 0 auto;
                    background: white;
                    position: relative;
                    min-height: 297mm;
                }
                .watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    opacity: 0.05;
                    z-index: 1;
                    pointer-events: none;
                }
                .watermark img {
                    width: 400px;
                    height: auto;
                }
                .content-wrapper {
                    position: relative;
                    z-index: 2;
                    padding: 20px;
                }
                .header {
                    text-align: center;
                    border-bottom: 3px solid #1e3a8a;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .logo {
                    max-width: 120px;
                    height: auto;
                    margin-bottom: 15px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                    font-weight: bold;
                    color: #1e3a8a;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .header .subtitle {
                    margin: 5px 0 0 0;
                    font-size: 16px;
                    color: #666;
                    font-weight: 500;
                }
                .demande-title {
                    text-align: center;
                    font-size: 20px;
                    font-weight: bold;
                    color: #1e3a8a;
                    margin: 20px 0;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .tracking-code {
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    color: #fff;
                    background: #1e3a8a;
                    padding: 10px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .demande-info {
                    background: #f8f9fa;
                    border: 2px solid #e9ecef;
                    border-radius: 8px;
                    padding: 25px;
                    margin: 20px 0;
                }
                .section-title {
                    font-size: 16px;
                    font-weight: bold;
                    color: #1e3a8a;
                    margin-bottom: 15px;
                    border-bottom: 2px solid #1e3a8a;
                    padding-bottom: 5px;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 12px;
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                }
                .info-row:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                }
                .info-label {
                    font-weight: bold;
                    color: #495057;
                    min-width: 150px;
                }
                .info-value {
                    color: #212529;
                    text-align: right;
                    flex: 1;
                }
                .statut-badge {
                    display: inline-block;
                    padding: 5px 15px;
                    border-radius: 20px;
                    color: white;
                    font-weight: bold;
                    background: ' . $statutColor . ';
                }
                .description-section {
                    background: #fff;
                    border: 2px solid #e9ecef;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .description-title {
                    font-weight: bold;
                    color: #1e3a8a;
                    margin-bottom: 10px;
                }
                .description-content {
                    color: #495057;
                    line-height: 1.6;
                    white-space: pre-wrap;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    border-top: 2px solid #e9ecef;
                    padding-top: 20px;
                    color: #6c757d;
                    font-size: 12px;
                }
                .footer-title {
                    font-weight: bold;
                    color: #495057;
                    margin-bottom: 5px;
                }
                .generated-info {
                    margin-top: 20px;
                    text-align: center;
                    font-size: 11px;
                    color: #6c757d;
                    font-style: italic;
                }
                @media print {
                    body { margin: 0; }
                    .demande-container { box-shadow: none; }
                }
            </style>
        </head>
        <body>
            <div class="demande-container">
                <div class="watermark">
                    <img src="' . $logoBase64 . '" alt="CSAR Logo">
                </div>
                <div class="content-wrapper">
                    <div class="header">
                        <img src="' . $logoBase64 . '" alt="CSAR Logo" class="logo">
                        <h1>CSAR</h1>
                        <p class="subtitle">Commissariat à la Sécurité Alimentaire<br>et à la Résilience</p>
                    </div>
                    
                    <div class="demande-title">FICHE DE DEMANDE</div>
                    
                    <div class="tracking-code">
                        Code de Suivi: ' . $demande->tracking_code . '
                    </div>
                    
                    <div class="demande-info">
                        <div class="section-title">Informations du Demandeur</div>
                        <div class="info-row">
                            <span class="info-label">Nom complet:</span>
                            <span class="info-value">' . ($demande->nom ?? '') . ' ' . ($demande->prenom ?? '') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">' . ($demande->email ?? 'N/A') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Téléphone:</span>
                            <span class="info-value">' . ($demande->telephone ?? 'N/A') . '</span>
                        </div>
                    </div>
                    
                    <div class="demande-info">
                        <div class="section-title">Détails de la Demande</div>
                        <div class="info-row">
                            <span class="info-label">Type de demande:</span>
                            <span class="info-value">' . ($demande->type_demande ?? 'N/A') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Objet:</span>
                            <span class="info-value">' . ($demande->objet ?? 'N/A') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Statut:</span>
                            <span class="info-value"><span class="statut-badge">' . $statutLabel . '</span></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date de soumission:</span>
                            <span class="info-value">' . $createdAt->format('d/m/Y à H:i') . '</span>
                        </div>
                    </div>
                    
                    <div class="description-section">
                        <div class="description-title">Description de la demande:</div>
                        <div class="description-content">' . ($demande->description ?? 'Aucune description fournie.') . '</div>
                    </div>
                    
                    ' . (isset($demande->reponse) && !empty($demande->reponse) ? '
                    <div class="description-section">
                        <div class="description-title">Réponse de l\'administration:</div>
                        <div class="description-content">' . $demande->reponse . '</div>
                        ' . (isset($demande->date_traitement) ? '<div style="margin-top: 10px; font-size: 12px; color: #6c757d;">Date de traitement: ' . \Carbon\Carbon::parse($demande->date_traitement)->format('d/m/Y à H:i') . '</div>' : '') . '
                    </div>
                    ' : '') . '
                    
                    <div class="footer">
                        <div class="footer-title">Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</div>
                        <div>Plateforme de Gestion des Demandes</div>
                        <div class="generated-info">
                            Document généré le ' . now()->format('d/m/Y à H:i') . '
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Générer le texte simple d'une demande
     */
    private function generateSimpleDemande($demande)
    {
        $statutLabels = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'traitee' => 'Traitée',
            'rejetee' => 'Rejetée'
        ];
        
        $statutLabel = $statutLabels[$demande->statut] ?? 'En attente';
        $createdAt = \Carbon\Carbon::parse($demande->created_at);
        
        return "
CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience
================================================================

FICHE DE DEMANDE

Code de Suivi: {$demande->tracking_code}

INFORMATIONS DU DEMANDEUR
-------------------------
Nom complet: " . ($demande->nom ?? '') . " " . ($demande->prenom ?? '') . "
Email: " . ($demande->email ?? 'N/A') . "
Téléphone: " . ($demande->telephone ?? 'N/A') . "

DÉTAILS DE LA DEMANDE
----------------------
Type de demande: " . ($demande->type_demande ?? 'N/A') . "
Objet: " . ($demande->objet ?? 'N/A') . "
Statut: {$statutLabel}
Date de soumission: " . $createdAt->format('d/m/Y à H:i') . "

DESCRIPTION
-----------
" . ($demande->description ?? 'Aucune description fournie.') . "

" . (isset($demande->reponse) && !empty($demande->reponse) ? "
RÉPONSE DE L'ADMINISTRATION
----------------------------
{$demande->reponse}
" . (isset($demande->date_traitement) ? "Date de traitement: " . \Carbon\Carbon::parse($demande->date_traitement)->format('d/m/Y à H:i') : '') . "
" : '') . "

Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)
Plateforme de Gestion des Demandes
Document généré le " . now()->format('d/m/Y à H:i') . "
        ";
    }

    /**
     * Avancer le workflow d'une demande à l'étape suivante
     */
    public function advanceWorkflow(Request $request, $id)
    {
        $request->validate([
            'workflow_status' => 'required|string|in:soumise,en_revue,document_attente,signee,scannee,validee_dg,approuvee,rejetee,cloturee',
            'comment' => 'nullable|string|max:2000',
        ]);

        try {
            $demande = PublicRequest::findOrFail($id);
            $oldStatus = $demande->workflow_status;
            $newStatus = $request->workflow_status;

            $demande->advanceWorkflow($newStatus, $request->comment);

            // Synchroniser le statut legacy
            $statusMap = [
                'soumise' => 'pending',
                'en_revue' => 'pending',
                'document_attente' => 'pending',
                'signee' => 'pending',
                'scannee' => 'pending',
                'validee_dg' => 'pending',
                'approuvee' => 'approved',
                'rejetee' => 'rejected',
                'cloturee' => 'completed',
            ];
            if (isset($statusMap[$newStatus])) {
                $demande->update(['status' => $statusMap[$newStatus]]);
            }

            // Notifier le demandeur du changement de statut
            try {
                \App\Services\RequestNotificationService::notifyWorkflowUpdate($demande, $oldStatus, $newStatus);
            } catch (\Exception $e) {
                Log::error('Erreur notification workflow: ' . $e->getMessage());
            }

            // Notifications par rôle selon le nouveau statut
            if ($newStatus === 'document_attente') {
                try {
                    \App\Services\RequestNotificationService::notifySignataires($demande);
                } catch (\Exception $e) {
                    Log::error('Erreur notification signataires: ' . $e->getMessage());
                }
            }

            if ($newStatus === 'signee') {
                try {
                    \App\Services\RequestNotificationService::notifyScanneurs($demande);
                } catch (\Exception $e) {
                    Log::error('Erreur notification scanneurs: ' . $e->getMessage());
                }
            }

            if ($newStatus === 'scannee') {
                try {
                    \App\Services\RequestNotificationService::notifyDgForApproval($demande);
                } catch (\Exception $e) {
                    Log::error('Erreur notification DG: ' . $e->getMessage());
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Workflow avancé: ' . $oldStatus . ' → ' . $newStatus,
                    'workflow_status' => $demande->workflow_status,
                    'workflow_status_label' => $demande->workflow_status_label,
                    'workflow_status_badge' => $demande->workflow_status_badge,
                ]);
            }

            return redirect()->back()->with('success', 'Workflow avancé avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur avancement workflow: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de l\'avancement du workflow'], 500);
            }
            return redirect()->back()->with('error', 'Erreur lors de l\'avancement du workflow');
        }
    }

    /**
     * Uploader le document signé
     */
    public function uploadSignature(Request $request, $id)
    {
        $request->validate([
            'signature_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'courier_reference' => 'nullable|string|max:255',
            'courier_date' => 'nullable|date',
            'comment' => 'nullable|string|max:2000',
        ]);

        try {
            $demande = PublicRequest::findOrFail($id);

            if ($request->hasFile('signature_file')) {
                $path = $request->file('signature_file')->store('demandes/signatures', 'public');
                $demande->update(['dg_signature_file' => $path]);
            }

            $updateData = ['processed_by' => auth()->id()];
            if ($request->filled('courier_reference')) {
                $updateData['courier_reference'] = $request->courier_reference;
            }
            if ($request->filled('courier_date')) {
                $updateData['courier_date'] = $request->courier_date;
            }
            if ($request->filled('comment')) {
                $updateData['document_notes'] = $request->comment;
            }
            $demande->update($updateData);

            // Avancer automatiquement si pas encore signée
            if (in_array($demande->workflow_status, ['soumise', 'en_revue', 'document_attente'])) {
                $demande->advanceWorkflow('signee', 'Document signé uploadé par ' . auth()->user()->name);
            }

            return redirect()->back()->with('success', 'Document signé enregistré avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur upload signature: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement du document signé');
        }
    }

    /**
     * Uploader le scan du document
     */
    public function uploadScan(Request $request, $id)
    {
        $request->validate([
            'scan_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
            'comment' => 'nullable|string|max:2000',
        ]);

        try {
            $demande = PublicRequest::findOrFail($id);

            if ($request->hasFile('scan_file')) {
                $path = $request->file('scan_file')->store('demandes/scans', 'public');
                $demande->update(['scan_file' => $path]);
            }

            if ($request->filled('comment')) {
                $demande->update(['document_notes' => $request->comment]);
            }

            // Avancer automatiquement si pas encore scannée
            if (in_array($demande->workflow_status, ['soumise', 'en_revue', 'document_attente', 'signee'])) {
                $demande->advanceWorkflow('scannee', 'Scan uploadé par ' . auth()->user()->name);
            }

            return redirect()->back()->with('success', 'Scan enregistré avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur upload scan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement du scan');
        }
    }

    /**
     * Validation par le DG
     */
    public function validateDg(Request $request, $id)
    {
        $request->validate([
            'dg_approved' => 'required|boolean',
            'dg_comment' => 'nullable|string|max:2000',
        ]);

        try {
            $demande = PublicRequest::findOrFail($id);

            $oldStatus = $demande->workflow_status;

            if ($request->boolean('dg_approved')) {
                $demande->update([
                    'dg_approved_by' => auth()->id(),
                    'dg_approved_at' => now(),
                ]);
                $demande->advanceWorkflow('validee_dg', $request->dg_comment ?? 'Validée par la Direction Générale');
                $message = 'Demande validée par le DG';
            } else {
                $demande->advanceWorkflow('rejetee', $request->dg_comment ?? 'Rejetée par la Direction Générale');
                $demande->update(['status' => 'rejected']);
                $message = 'Demande rejetée par le DG';
            }

            // Notifier le demandeur
            try {
                \App\Services\RequestNotificationService::notifyWorkflowUpdate($demande, $oldStatus, $demande->workflow_status);
            } catch (\Exception $e) {
                Log::error('Erreur notification DG validation: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Erreur validation DG: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la validation DG');
        }
    }

    /**
     * Marquer une demande comme doublon et la fusionner
     */
    public function markDuplicate(Request $request, $id)
    {
        $request->validate([
            'original_id' => 'required|exists:public_requests,id',
            'comment' => 'nullable|string|max:2000',
        ]);

        try {
            $demande = PublicRequest::findOrFail($id);
            $original = PublicRequest::findOrFail($request->original_id);

            $demande->update([
                'is_duplicate' => true,
                'duplicate_of' => $request->original_id,
                'status' => 'rejected',
                'workflow_status' => 'rejetee',
                'admin_comment' => ($request->comment ?? '') . ' [Doublon de ' . $original->tracking_code . ']',
            ]);

            $demande->logWorkflowAction('Marquée comme doublon', 'Fusionnée avec ' . $original->tracking_code);

            return redirect()->back()->with('success', 'Demande marquée comme doublon et fusionnée avec ' . $original->tracking_code);
        } catch (\Exception $e) {
            Log::error('Erreur marquage doublon: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du marquage du doublon');
        }
    }

    /**
     * Liste des demandes identifiées comme doublons
     */
    public function duplicates(Request $request)
    {
        try {
            $query = PublicRequest::where('is_duplicate', true)
                ->orWhere(function ($q) {
                    $q->whereNotNull('requester_id')
                      ->whereRaw('requester_id IN (SELECT requester_id FROM public_requests GROUP BY requester_id HAVING COUNT(*) > 1)')
                      ->where('is_duplicate', false);
                });

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('tracking_code', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $duplicates = $query->with('originalRequest')->orderBy('created_at', 'desc')->paginate(15);
            $stats = [
                'total_duplicates' => PublicRequest::where('is_duplicate', true)->count(),
                'potential_duplicates' => PublicRequest::whereNotNull('requester_id')
                    ->where('is_duplicate', false)
                    ->whereIn('requester_id', function($q) {
                        $q->select('requester_id')
                          ->from('public_requests')
                          ->groupBy('requester_id')
                          ->havingRaw('COUNT(*) > 1');
                    })->count(),
            ];

            return view('admin.demandes.duplicates', compact('duplicates', 'stats'));
        } catch (\Exception $e) {
            Log::error('Erreur liste doublons: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des doublons');
        }
    }

    /**
     * Vue DG : demandes scannées en attente de validation
     */
    public function dgPending(Request $request)
    {
        try {
            $demandes = PublicRequest::where('workflow_status', 'scannee')
                ->where('is_duplicate', false)
                ->orderBy('created_at', 'asc')
                ->paginate(20);

            $pendingCount = PublicRequest::where('workflow_status', 'scannee')->count();
            $validatedToday = PublicRequest::where('workflow_status', 'validee_dg')
                ->whereDate('dg_approved_at', today())
                ->count();
            $rejectedToday = PublicRequest::where('workflow_status', 'rejetee')
                ->whereDate('updated_at', today())
                ->count();

            return view('admin.demandes.dg-pending', compact('demandes', 'pendingCount', 'validatedToday', 'rejectedToday'));
        } catch (\Exception $e) {
            Log::error('Erreur vue DG pending: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des validations DG');
        }
    }

    /**
     * Avancer le workflow en masse pour plusieurs demandes
     */
    public function bulkWorkflow(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:public_requests,id',
                'status' => 'required|string|in:soumise,en_revue,document_attente,signee,scannee,validee_dg,approuvee,rejetee,cloturee',
            ]);

            $ids = $request->input('ids', []);
            $newStatus = $request->input('status');
            $updated = 0;

            foreach ($ids as $id) {
                $demande = PublicRequest::find($id);
                if (!$demande) continue;

                $oldStatus = $demande->workflow_status;

                // Synchroniser le statut legacy
                $legacyMap = [
                    'soumise' => 'pending',
                    'en_revue' => 'pending',
                    'document_attente' => 'pending',
                    'signee' => 'pending',
                    'scannee' => 'pending',
                    'validee_dg' => 'approved',
                    'approuvee' => 'approved',
                    'rejetee' => 'rejected',
                    'cloturee' => 'completed',
                ];

                $demande->update([
                    'workflow_status' => $newStatus,
                    'status' => $legacyMap[$newStatus] ?? $demande->status,
                    'updated_at' => now(),
                ]);

                $demande->logWorkflowAction("Transition en masse: {$oldStatus} → {$newStatus}", 'Action groupée');

                // Notification au demandeur
                try {
                    \App\Services\RequestNotificationService::notifyWorkflowUpdate($demande, $oldStatus, $newStatus);
                } catch (\Exception $e) {
                    Log::error('Notification workflow bulk failed: ' . $e->getMessage());
                }

                $updated++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$updated} demande(s) avancée(s) avec succès vers le statut « {$newStatus} »",
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur bulk workflow: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement en masse : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dashboard DG / Signataire / Scanneur dédié
     * Affiche les demandes à signer, scanner et valider
     */
    public function dgDashboard(Request $request)
    {
        try {
            $user = auth()->user();
            $role = $user->role ?? 'dg';

            // À signer (attente document)
            $toSign = PublicRequest::where('workflow_status', 'document_attente')
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get();

            // À scanner (signée)
            $toScan = PublicRequest::where('workflow_status', 'signee')
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get();

            // À valider (scannée)
            $toValidate = PublicRequest::where('workflow_status', 'scannee')
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get();

            // Stats
            $signCount = $toSign->count();
            $scanCount = $toScan->count();
            $validateCount = $toValidate->count();

            // Déterminer quels onglets sont visibles selon le rôle
            // Le DG peut tout faire : signer, scanner et valider
            $canSign = in_array($role, ['admin', 'super_admin', 'signataire', 'dg', 'directeur_general']);
            $canScan = in_array($role, ['admin', 'super_admin', 'scanneur', 'dg', 'directeur_general']);
            $canValidate = in_array($role, ['admin', 'super_admin', 'dg', 'directeur_general']);

            return view('admin.demandes.dg-dashboard', compact(
                'toSign', 'toScan', 'toValidate',
                'signCount', 'scanCount', 'validateCount',
                'canSign', 'canScan', 'canValidate', 'role'
            ));
        } catch (\Exception $e) {
            Log::error('Erreur DG dashboard: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du tableau de bord DG.');
        }
    }

    /**
     * Historique global des actions workflow (journal d'audit)
     */
    public function workflowHistory(Request $request)
    {
        try {
            $query = PublicRequest::query();

            // Filtres
            if ($request->filled('tracking_code')) {
                $query->where('tracking_code', 'like', "%{$request->tracking_code}%");
            }
            if ($request->filled('action_type')) {
                // Filtrer par type d'action dans l'historique JSON
            }
            if ($request->filled('date_from')) {
                $query->whereDate('updated_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('updated_at', '<=', $request->date_to);
            }

            $requests = $query->orderBy('updated_at', 'desc')
                ->limit(200)
                ->get(['id', 'tracking_code', 'full_name', 'subject', 'workflow_status', 'workflow_history', 'dg_signature_file', 'scan_file', 'dg_approved_by', 'dg_approved_at', 'updated_at']);

            // Construire la timeline
            $timeline = [];
            foreach ($requests as $req) {
                $history = $req->workflow_history ?? [];
                foreach ($history as $entry) {
                    $timeline[] = [
                        'timestamp' => $entry['timestamp'] ?? $req->updated_at,
                        'action' => $entry['action'] ?? '-',
                        'comment' => $entry['comment'] ?? '-',
                        'user_id' => $entry['user_id'] ?? null,
                        'tracking_code' => $req->tracking_code,
                        'full_name' => $req->full_name,
                        'request_id' => $req->id,
                        'current_status' => $req->workflow_status,
                        'has_signature' => !empty($req->dg_signature_file),
                        'has_scan' => !empty($req->scan_file),
                        'dg_approved_at' => $req->dg_approved_at,
                    ];
                }
            }

            // Trier par date décroissante
            usort($timeline, function ($a, $b) {
                return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
            });

            // Pagination manuelle
            $perPage = 50;
            $page = $request->get('page', 1);
            $total = count($timeline);
            $timeline = array_slice($timeline, ($page - 1) * $perPage, $perPage);

            // Stats
            $totalSignatures = PublicRequest::whereNotNull('dg_signature_file')->count();
            $totalScans = PublicRequest::whereNotNull('scan_file')->count();
            $totalValidated = PublicRequest::whereIn('workflow_status', ['validee_dg', 'approuvee', 'cloturee'])->count();
            $totalRejected = PublicRequest::where('workflow_status', 'rejetee')->count();

            return view('admin.demandes.workflow-history', compact(
                'timeline', 'total', 'page', 'perPage',
                'totalSignatures', 'totalScans', 'totalValidated', 'totalRejected'
            ));
        } catch (\Exception $e) {
            Log::error('Erreur historique workflow: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement de l\'historique.');
        }
    }
}