<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockLevel;
use App\Models\StockType;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Récupérer les données avec gestion d'erreur robuste
            $mouvementsPaginated = $this->getStockMovements($request);
            $stats = $this->getStockStats();
            $chartData = $this->getChartData();
            
            // Passer les variables de filtrage à la vue
            $search = $request->get('search', '');
            $type = $request->get('type', '');
            $mouvement = $request->get('mouvement', '');
            $entrepot = $request->get('entrepot', '');
            $date_debut = $request->get('date_debut', '');
            $date_fin = $request->get('date_fin', '');
            
            return view('admin.stock.index', compact(
                'mouvementsPaginated', 
                'stats', 
                'chartData',
                'search', 
                'type', 
                'mouvement', 
                'entrepot', 
                'date_debut', 
                'date_fin'
            ));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des mouvements de stock: ' . $e->getMessage());
            return view('admin.stock.index', [
                'mouvementsPaginated' => new LengthAwarePaginator([], 0, 15),
                'stats' => $this->getDefaultStats(),
                'chartData' => $this->getDefaultChartData(),
                'search' => '',
                'type' => '',
                'mouvement' => '',
                'entrepot' => '',
                'date_debut' => '',
                'date_fin' => ''
            ])->with('error', 'Erreur lors du chargement des données.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $entrepots = Warehouse::where('is_active', true)->get();
            $stockTypes = StockType::all();
            $stocks = Stock::with('warehouse')->where('is_active', true)->get();
            
            return view('admin.stock.create', compact('entrepots', 'stockTypes', 'stocks'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire de création: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_name' => 'required|string|max:255',
            'stock_id' => 'nullable|exists:stocks,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'reference' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Générer la référence automatique si non fournie
            $reference = $request->reference ?: $this->generateUniqueReference($request->type);
            
            // Gestion du stock : produit existant ou nouveau
            if ($request->stock_id) {
                // Produit existant sélectionné
                $stock = Stock::findOrFail($request->stock_id);
                $quantityBefore = $stock->quantity;
                
                // Calculer la nouvelle quantité
                $quantityChange = $request->type === 'in' ? $request->quantity : -$request->quantity;
                $quantityAfter = $quantityBefore + $quantityChange;
                
                // Vérifier que la quantité après n'est pas négative
                if ($quantityAfter < 0) {
                    throw new \Exception('Quantité insuffisante en stock. Stock actuel: ' . $quantityBefore);
                }
                
                // Mettre à jour le stock existant
                $stock->update(['quantity' => $quantityAfter]);
            } else {
                // Nouveau produit saisi manuellement
                // Créer un nouveau stock si c'est une entrée
                if ($request->type === 'in') {
                    $stock = Stock::create([
                        'warehouse_id' => $request->warehouse_id,
                        'item_name' => $request->product_name,
                        'quantity' => $request->quantity,
                        'min_quantity' => 0,
                        'is_active' => true
                    ]);
                    $quantityBefore = 0;
                    $quantityAfter = $request->quantity;
                } else {
                    throw new \Exception('Impossible de faire une sortie sur un produit qui n\'existe pas en stock.');
                }
            }
            
            // Créer le mouvement de stock
            $mouvement = StockMovement::create([
                'warehouse_id' => $request->warehouse_id,
                'stock_id' => $stock->id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $request->reason,
                'reference' => $reference,
                'reference_number' => $this->getReferenceNumber($reference),
                'created_by' => auth()->id()
            ]);

            // Créer une notification
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Nouveau mouvement de stock',
                'message' => "Mouvement {$reference} créé avec succès",
                'type' => 'success'
            ]);

            DB::commit();

            return redirect()->route('admin.stock.index')
                ->with('success', 'Mouvement de stock créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du mouvement: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du mouvement: ' . $e->getMessage());
        }
    }

    /**
     * Download receipt for a stock movement
     */
    public function downloadReceipt($id)
    {
        try {
            $mouvement = StockMovement::with(['warehouse', 'creator'])->findOrFail($id);
            return $this->generateReceiptPdf($mouvement);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du reçu: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la génération du reçu.');
        }
    }

    // Méthodes privées

    private function getStockMovements($request, $paginate = true)
    {
        $query = StockMovement::with(['warehouse', 'creator', 'stock.stockType']);

        // Filtre de recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre par entrepôt
        if ($request->filled('entrepot')) {
            $query->where('warehouse_id', $request->entrepot);
        }

        // Filtre par date
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $query->orderBy('created_at', 'desc');

        if ($paginate) {
            $movements = $query->paginate(15)->withQueryString();
        } else {
            $movements = $query->get();
        }

        // Transformer les données pour correspondre à ce que la vue attend
        $transformedMovements = $movements->getCollection()->map(function($mouvement) {
            // Extraire le nom du produit depuis le stock ou le reason
            $produit = 'Non spécifié';
            $unite = 'unité';
            
            if ($mouvement->stock) {
                $produit = $mouvement->stock->item_name ?? 'Non spécifié';
                // Récupérer l'unité depuis le StockType si disponible
                if ($mouvement->stock->stockType && $mouvement->stock->stockType->unit) {
                    $unite = $mouvement->stock->stockType->unit;
                }
            } elseif ($mouvement->reason) {
                // Utiliser le reason comme nom de produit si pas de stock associé
                $produit = $mouvement->reason;
            }

            // Calculer le total (quantité * prix unitaire si disponible, sinon 0)
            $total = 0;
            if ($mouvement->stock && $mouvement->stock->unit_price) {
                $total = floatval($mouvement->quantity) * floatval($mouvement->stock->unit_price);
            }

            return [
                'id' => $mouvement->id,
                'reference' => $mouvement->reference ?? 'N/A',
                'type' => $mouvement->type ?? 'out',
                'produit' => $produit,
                'quantite' => floatval($mouvement->quantity ?? 0),
                'unite' => $unite,
                'entrepot_nom' => $mouvement->warehouse->name ?? 'Non spécifié',
                'responsable' => $mouvement->creator->name ?? 'Non spécifié',
                'total' => $total,
                'date_mouvement' => $mouvement->created_at ?? now(),
                'reason' => $mouvement->reason ?? '',
            ];
        });

        // Si paginé, remplacer la collection
        if ($paginate) {
            $movements->setCollection($transformedMovements);
            return $movements;
        } else {
            return $transformedMovements;
        }
    }

    private function getStockStats()
    {
        try {
            $stats = [
                'total_movements' => StockMovement::count(),
                'entrees' => StockMovement::where('type', 'in')->count(),
                'sorties' => StockMovement::where('type', 'out')->count(),
                'transferts' => StockMovement::where('type', 'transfert')->count(),
                'ajustements' => StockMovement::where('type', 'ajustement')->count(),
                'valeur_totale' => StockMovement::sum('quantity'),
                'total' => StockMovement::count()
            ];
            return $stats;
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    private function getChartData()
    {
        try {
            $types = StockMovement::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            $entrepots = StockMovement::with('warehouse')
                ->selectRaw('warehouse_id, COUNT(*) as count')
                ->groupBy('warehouse_id')
                ->get()
                ->map(function($item) {
                    return [
                        'name' => $item->warehouse->name ?? 'Inconnu',
                        'count' => $item->count
                    ];
                });

            return [
                'types' => $types,
                'entrepots' => $entrepots,
                'produits' => collect([])
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des données de graphique: ' . $e->getMessage());
            return $this->getDefaultChartData();
        }
    }

    /**
     * Générer une référence unique basée sur le dernier numéro
     */
    private function generateUniqueReference($type)
    {
        $prefix = $type === 'in' ? 'STK-IN' : 'STK-OUT';
        $year = date('Y');
        
        // Récupérer la dernière référence de ce type pour cette année
        $lastMovement = StockMovement::where('reference', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('reference', 'desc')
            ->first();
        
        if ($lastMovement && preg_match('/-(\d+)$/', $lastMovement->reference, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }
        
        return "{$prefix}-{$year}-{$number}";
    }

    /**
     * Générer une référence via API
     */
    public function generateReference(Request $request)
    {
        try {
            $type = $request->input('type', 'in');
            $reference = $this->generateUniqueReference($type);
            
            return response()->json([
                'success' => true,
                'reference' => $reference
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de la référence: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de la référence'
            ], 500);
        }
    }

    private function getReferenceNumber($reference)
    {
        return preg_replace('/[^0-9]/', '', $reference);
    }

    private function generateReceiptPdf($mouvement)
    {
        try {
            // Générer le contenu HTML du reçu
            $html = $this->generateReceiptHtml($mouvement);
            
            // Créer le PDF avec DomPDF ou fallback vers HTML
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdf->setPaper('A4', 'portrait');
                
                return $pdf->download('receipt_' . $mouvement->reference . '.pdf');
            } else {
                // Fallback vers HTML si DomPDF n'est pas disponible
                return response($html)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="receipt_' . $mouvement->reference . '.html"');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF: ' . $e->getMessage());
            
            // Fallback vers texte simple
            $content = $this->generateSimpleReceipt($mouvement);
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="receipt_' . $mouvement->reference . '.txt"');
        }
    }

    private function generateReceiptHtml($mouvement)
    {
        // Utiliser le logo CSAR disponible
        $logoPath = public_path('images/logos/LOGO CSAR vectoriel-01.png');
        $logoBase64 = '';
        
        if (file_exists($logoPath)) {
            $logoContent = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoContent);
        }
        
        $typeLabels = [
            'in' => 'ENTRÉE',
            'out' => 'SORTIE',
            'entree' => 'ENTRÉE',
            'sortie' => 'SORTIE',
            'transfert' => 'TRANSFERT',
            'ajustement' => 'AJUSTEMENT'
        ];
        
        $typeLabel = $typeLabels[$mouvement->type] ?? strtoupper($mouvement->type);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reçu de Mouvement de Stock - ' . $mouvement->reference . '</title>
            <style>
                @page {
                    margin: 10mm;
                    size: A4;
                }
                body {
                    font-family: "Segoe UI", Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background: white;
                    color: #333;
                    line-height: 1.3;
                    font-size: 11px;
                }
                .receipt-container {
                    width: 100%;
                    max-width: 100%;
                    margin: 0;
                    background: white;
                    position: relative;
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                }
                .watermark {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    opacity: 0.03;
                    z-index: 1;
                    pointer-events: none;
                }
                .watermark img {
                    width: 250px;
                    height: auto;
                }
                .content-wrapper {
                    position: relative;
                    z-index: 2;
                    padding: 15px;
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                }
                .header {
                    text-align: center;
                    border-bottom: 2px solid #1e3a8a;
                    padding-bottom: 10px;
                    margin-bottom: 15px;
                    flex-shrink: 0;
                }
                .logo {
                    max-width: 80px;
                    height: auto;
                    margin-bottom: 8px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: bold;
                    color: #1e3a8a;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                .header .subtitle {
                    margin: 3px 0 0 0;
                    font-size: 12px;
                    color: #666;
                    font-weight: 500;
                    line-height: 1.2;
                }
                .receipt-title {
                    text-align: center;
                    font-size: 16px;
                    font-weight: bold;
                    color: #1e3a8a;
                    margin: 10px 0;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    flex-shrink: 0;
                }
                .receipt-info {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 10px 0;
                    flex: 1;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    padding: 4px 0;
                    border-bottom: 1px solid #e9ecef;
                    font-size: 10px;
                }
                .info-row:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                }
                .info-label {
                    font-weight: bold;
                    color: #495057;
                    min-width: 120px;
                }
                .info-value {
                    color: #212529;
                    text-align: right;
                    flex: 1;
                }
                .signatures {
                    margin-top: 20px;
                    display: flex;
                    justify-content: space-between;
                    flex-shrink: 0;
                }
                .signature-box {
                    text-align: center;
                    width: 150px;
                }
                .signature-line {
                    border-bottom: 2px solid #333;
                    height: 30px;
                    margin-bottom: 5px;
                }
                .signature-label {
                    font-weight: bold;
                    color: #495057;
                    font-size: 11px;
                }
                .footer {
                    margin-top: 20px;
                    text-align: center;
                    border-top: 1px solid #e9ecef;
                    padding-top: 10px;
                    color: #6c757d;
                    font-size: 10px;
                    flex-shrink: 0;
                }
                .footer-title {
                    font-weight: bold;
                    color: #495057;
                    margin-bottom: 3px;
                }
                .generated-info {
                    margin-top: 10px;
                    text-align: center;
                    font-size: 9px;
                    color: #6c757d;
                    font-style: italic;
                }
                @media print {
                    body { 
                        margin: 0; 
                        padding: 0;
                        background: white;
                    }
                    .receipt-container { 
                        box-shadow: none;
                        max-width: none;
                        margin: 0;
                    }
                    .content-wrapper {
                        padding: 8mm;
                    }
                    .header {
                        margin-bottom: 8px;
                        padding-bottom: 6px;
                    }
                    .receipt-title {
                        margin: 6px 0;
                        font-size: 14px;
                    }
                    .receipt-info {
                        padding: 8px;
                        margin: 6px 0;
                    }
                    .signatures {
                        margin-top: 12px;
                    }
                    .footer {
                        margin-top: 12px;
                        padding-top: 6px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="receipt-container">
                <div class="watermark">
                    <img src="' . $logoBase64 . '" alt="CSAR Logo">
                </div>
                <div class="content-wrapper">
                    <div class="header">
                        <img src="' . $logoBase64 . '" alt="CSAR Logo" class="logo">
                        <h1>CSAR</h1>
                        <p class="subtitle">Commissariat à la Sécurité Alimentaire<br>et à la Résilience</p>
                    </div>
                    
                    <div class="receipt-title">REÇU DE MOUVEMENT DE STOCK</div>
                    
                    <div class="receipt-info">
                        <div class="info-row">
                            <span class="info-label">Référence:</span>
                            <span class="info-value">' . $mouvement->reference . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Type de Mouvement:</span>
                            <span class="info-value">' . $typeLabel . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Quantité:</span>
                            <span class="info-value">' . number_format($mouvement->quantity, 2) . ' unités</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Entrepôt:</span>
                            <span class="info-value">' . ($mouvement->warehouse->name ?? 'N/A') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Localisation:</span>
                            <span class="info-value">' . ($mouvement->warehouse->location ?? 'N/A') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Motif:</span>
                            <span class="info-value">' . $mouvement->reason . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date de Création:</span>
                            <span class="info-value">' . $mouvement->created_at->format('d/m/Y à H:i') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Créé par:</span>
                            <span class="info-value">' . ($mouvement->creator->name ?? 'Administrateur CSAR') . '</span>
                        </div>
                    </div>
                    
                    <div class="signatures">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Signature Responsable</div>
                        </div>
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Signature Agent</div>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <div class="footer-title">Centre de Secours et d\'Assistance Rapide (CSAR)</div>
                        <div>Plateforme de Gestion des Stocks</div>
                        <div class="generated-info">
                            Reçu généré le ' . now()->format('d/m/Y à H:i') . ' pour le téléchargement
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Supprimer un mouvement de stock
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $mouvement = StockMovement::findOrFail($id);
            $reference = $mouvement->reference;
            
            // Récupérer le stock associé
            $stock = $mouvement->stock;
            
            if ($stock) {
                // Restaurer la quantité précédente
                $stock->update(['quantity' => $mouvement->quantity_before]);
            }
            
            // Supprimer le mouvement
            $mouvement->delete();

            // Créer une notification
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Mouvement de stock supprimé',
                'message' => "Le mouvement {$reference} a été supprimé avec succès",
                'type' => 'warning'
            ]);

            DB::commit();

            return redirect()->route('admin.stock.index')
                ->with('success', 'Mouvement de stock supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du mouvement: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du mouvement.');
        }
    }

    private function generateSimpleReceipt($mouvement)
    {
        $typeLabels = [
            'in' => 'ENTRÉE',
            'out' => 'SORTIE',
            'entree' => 'ENTRÉE',
            'sortie' => 'SORTIE',
            'transfert' => 'TRANSFERT',
            'ajustement' => 'AJUSTEMENT'
        ];
        
        $typeLabel = $typeLabels[$mouvement->type] ?? strtoupper($mouvement->type);
        
        return "
CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience
================================================================

REÇU DE MOUVEMENT DE STOCK

Référence: {$mouvement->reference}
Type de Mouvement: {$typeLabel}
Quantité: " . number_format($mouvement->quantity, 2) . " unités
Entrepôt: " . ($mouvement->warehouse->name ?? 'N/A') . "
Localisation: " . ($mouvement->warehouse->location ?? 'N/A') . "
Motif: {$mouvement->reason}
Date de Création: " . $mouvement->created_at->format('d/m/Y à H:i') . "
Créé par: " . ($mouvement->creator->name ?? 'Administrateur CSAR') . "

Signature Responsable: _________________
Signature Agent: _________________

Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)
Plateforme de Gestion des Stocks
Reçu généré le " . now()->format('d/m/Y à H:i') . " pour le téléchargement
        ";
    }

    /**
     * Obtenir les statistiques par défaut
     */
    private function getDefaultStats()
    {
        return [
            'total_movements' => 0,
            'entrees' => 0,
            'sorties' => 0,
            'transferts' => 0,
            'ajustements' => 0,
            'valeur_totale' => 0,
            'total' => 0
        ];
    }

    /**
     * Obtenir les données de graphique par défaut
     */
    private function getDefaultChartData()
    {
        return [
            'types' => [
                'entree' => 0,
                'sortie' => 0,
                'transfert' => 0,
                'ajustement' => 0
            ],
            'entrepots' => collect([]),
            'produits' => collect([])
        ];
    }
}
