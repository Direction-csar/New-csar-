<?php
/**
 * Interface web simple pour gérer les produits du stock
 * Accédez à ce fichier via: http://localhost/csar/gestion_produits.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\StockType;
use Illuminate\Support\Facades\DB;

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            // Ajouter un nouveau produit
            $stock = Stock::create([
                'warehouse_id' => $_POST['warehouse_id'],
                'stock_type_id' => $_POST['stock_type_id'],
                'item_name' => $_POST['item_name'],
                'description' => $_POST['description'],
                'quantity' => $_POST['quantity'] ?? 0,
                'min_quantity' => $_POST['min_quantity'] ?? 10,
                'max_quantity' => $_POST['max_quantity'] ?? 1000,
                'unit_price' => $_POST['unit_price'] ?? 0,
                'is_active' => true
            ]);
            
            $message = "✅ Produit '{$_POST['item_name']}' ajouté avec succès!";
            $messageType = 'success';
        } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
            // Supprimer un produit
            $stock = Stock::find($_POST['stock_id']);
            if ($stock) {
                $stock->delete();
                $message = "✅ Produit supprimé avec succès!";
                $messageType = 'success';
            }
        }
    } catch (\Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Récupérer les données
$warehouses = Warehouse::where('is_active', true)->get();
$stockTypes = StockType::all();
$stocks = Stock::with(['warehouse', 'stockType'])->where('is_active', true)->orderBy('item_name')->get();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - CSAR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 968px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
            color: #555;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏪 Gestion des Produits</h1>
            <p>Ajoutez et gérez les produits disponibles pour les mouvements de stock</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Produits</h3>
                <div class="number"><?= $stocks->count() ?></div>
            </div>
            <div class="stat-card">
                <h3>Entrepôts Actifs</h3>
                <div class="number"><?= $warehouses->count() ?></div>
            </div>
            <div class="stat-card">
                <h3>Types de Stock</h3>
                <div class="number"><?= $stockTypes->count() ?></div>
            </div>
        </div>
        
        <div class="grid">
            <!-- Formulaire d'ajout -->
            <div class="card">
                <h2>➕ Ajouter un Produit</h2>
                
                <?php if ($warehouses->isEmpty()): ?>
                    <div class="empty-state">
                        <p>⚠️ Aucun entrepôt disponible!</p>
                        <p style="font-size: 12px; margin-top: 10px;">Veuillez créer un entrepôt d'abord.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label>Nom du Produit *</label>
                            <input type="text" name="item_name" required placeholder="Ex: Riz blanc">
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" placeholder="Ex: Riz de qualité supérieure - sac de 50kg"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Entrepôt *</label>
                            <select name="warehouse_id" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse->id ?>"><?= $warehouse->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Type de Stock *</label>
                            <select name="stock_type_id" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($stockTypes as $type): ?>
                                    <option value="<?= $type->id ?>"><?= $type->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Quantité Initiale</label>
                            <input type="number" name="quantity" value="0" step="0.01" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label>Quantité Minimale (Alerte)</label>
                            <input type="number" name="min_quantity" value="10" step="0.01" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label>Prix Unitaire (FCFA)</label>
                            <input type="number" name="unit_price" value="0" step="0.01" min="0">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">➕ Ajouter le Produit</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Liste des produits -->
            <div class="card">
                <h2>📦 Liste des Produits (<?= $stocks->count() ?>)</h2>
                
                <?php if ($stocks->isEmpty()): ?>
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p>Aucun produit dans le stock</p>
                        <p style="font-size: 12px; margin-top: 10px;">Ajoutez votre premier produit avec le formulaire ci-contre.</p>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Type</th>
                                    <th>Entrepôt</th>
                                    <th>Quantité</th>
                                    <th>Prix Unit.</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stocks as $stock): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($stock->item_name) ?></strong>
                                            <?php if ($stock->description): ?>
                                                <br><small style="color: #888;"><?= htmlspecialchars(substr($stock->description, 0, 50)) ?><?= strlen($stock->description) > 50 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($stock->stockType): ?>
                                                <span class="badge badge-info"><?= $stock->stockType->name ?></span>
                                            <?php else: ?>
                                                <span class="badge">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $stock->warehouse ? $stock->warehouse->name : 'N/A' ?></td>
                                        <td>
                                            <?php
                                            $quantity = number_format($stock->quantity, 2, ',', ' ');
                                            $isLow = $stock->quantity <= $stock->min_quantity;
                                            ?>
                                            <span class="badge <?= $isLow ? 'badge-warning' : 'badge-success' ?>">
                                                <?= $quantity ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($stock->unit_price, 0, ',', ' ') ?> FCFA</td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="stock_id" value="<?= $stock->id ?>">
                                                <button type="submit" class="btn btn-danger">🗑️ Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <h2>ℹ️ Instructions</h2>
            <ol style="line-height: 1.8; color: #555;">
                <li>Utilisez le formulaire ci-dessus pour ajouter de nouveaux produits</li>
                <li>Les produits ajoutés apparaîtront automatiquement dans le dropdown "Produit/Stock" lors de la création d'un mouvement</li>
                <li>La quantité initiale peut être mise à 0, elle sera mise à jour avec les mouvements de stock</li>
                <li>Le prix unitaire est optionnel mais recommandé pour le suivi financier</li>
                <li>Pour créer un mouvement de stock: <strong>Admin > Gestion des Stocks > Nouveau Mouvement</strong></li>
            </ol>
        </div>
    </div>
</body>
</html>







































