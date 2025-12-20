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
            'statut' => 'nullable|string',
            'commentaire_admin' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $demande = PublicRequest::findOrFail($id);
            $oldStatut = $demande->status;

            $updateData = [];
            if ($request->filled('statut')) {
                $updateData['status'] = $request->statut;
                $updateData['updated_at'] = now();
            }
            if ($request->filled('commentaire_admin')) {
                $updateData['admin_notes'] = $request->commentaire_admin;
            }
            if ($request->filled('assignee_id')) {
                $updateData['assigned_to'] = $request->assignee_id;
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
            $filters = $request->only(['statut', 'region', 'type_demande', 'date_from', 'date_to', 'search']);

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
     * Exporter vers Excel
     */
    private function exportToExcel($demandes)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Demandes');

        // En-têtes
        $headers = [
            'Code de Suivi', 'Nom Demandeur', 'Email', 'Téléphone', 'Type', 'Statut',
            'Région', 'Commune', 'Département', 'Adresse', 'Description', 'Priorité',
            'Assigné à', 'Date de Demande', 'Date de Traitement', 'Commentaire Admin',
            'SMS Envoyé', 'Date de Création', 'Date de Mise à Jour'
        ];

        // Ajouter les en-têtes
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }

        // Ajouter les données
        $row = 2;
        foreach ($demandes as $demande) {
            $sheet->setCellValue('A' . $row, $demande->code_suivi ?? 'N/A');
            $sheet->setCellValue('B' . $row, $demande->nom_demandeur ?? 'N/A');
            $sheet->setCellValue('C' . $row, $demande->email ?? 'N/A');
            $sheet->setCellValue('D' . $row, $demande->telephone ?? 'N/A');
            $sheet->setCellValue('E' . $row, $demande->type_francais ?? 'N/A');
            $sheet->setCellValue('F' . $row, $demande->statut_francais ?? 'N/A');
            $sheet->setCellValue('G' . $row, $demande->region ?? 'N/A');
            $sheet->setCellValue('H' . $row, $demande->commune ?? 'N/A');
            $sheet->setCellValue('I' . $row, $demande->departement ?? 'N/A');
            $sheet->setCellValue('J' . $row, $demande->adresse ?? 'N/A');
            $sheet->setCellValue('K' . $row, $demande->description ?? 'N/A');
            $sheet->setCellValue('L' . $row, $demande->priorite_francais ?? 'N/A');
            $sheet->setCellValue('M' . $row, $demande->assignee->name ?? 'N/A');
            $sheet->setCellValue('N' . $row, $demande->date_demande ? $demande->date_demande->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('O' . $row, $demande->date_traitement ? $demande->date_traitement->format('d/m/Y') : 'N/A');
            $sheet->setCellValue('P' . $row, $demande->commentaire_admin ?? 'N/A');
            $sheet->setCellValue('Q' . $row, $demande->sms_envoye ? 'Oui' : 'Non');
            $sheet->setCellValue('R' . $row, $demande->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('S' . $row, $demande->updated_at->format('d/m/Y H:i'));
            $row++;
        }

        // Ajuster la largeur des colonnes
        foreach (range('A', 'S') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

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
                'Code de Suivi', 'Nom Demandeur', 'Email', 'Téléphone', 'Type', 'Statut',
                'Région', 'Commune', 'Département', 'Adresse', 'Description', 'Priorité',
                'Assigné à', 'Date de Demande', 'Date de Traitement', 'Commentaire Admin',
                'SMS Envoyé', 'Date de Création', 'Date de Mise à Jour'
            ];
            fputcsv($file, $headers, ';');
            
            // Données
            foreach ($demandes as $demande) {
                $rowData = [
                    $demande->code_suivi ?? 'N/A',
                    $demande->nom_demandeur ?? 'N/A',
                    $demande->email ?? 'N/A',
                    $demande->telephone ?? 'N/A',
                    $demande->type_francais ?? 'N/A',
                    $demande->statut_francais ?? 'N/A',
                    $demande->region ?? 'N/A',
                    $demande->commune ?? 'N/A',
                    $demande->departement ?? 'N/A',
                    $demande->adresse ?? 'N/A',
                    $demande->description ?? 'N/A',
                    $demande->priorite_francais ?? 'N/A',
                    $demande->assignee->name ?? 'N/A',
                    $demande->date_demande ? $demande->date_demande->format('d/m/Y') : 'N/A',
                    $demande->date_traitement ? $demande->date_traitement->format('d/m/Y') : 'N/A',
                    $demande->commentaire_admin ?? 'N/A',
                    $demande->sms_envoye ? 'Oui' : 'Non',
                    $demande->created_at->format('d/m/Y H:i'),
                    $demande->updated_at->format('d/m/Y H:i')
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
}