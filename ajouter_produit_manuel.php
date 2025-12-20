<?php
/**
 * Formulaire simple pour ajouter UN produit manuellement
 * Accédez à : http://localhost/csar/ajouter_produit_manuel.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\StockType;

// Traitement du formulaire
$message = '';
$messageType = '';
$produitAjoute = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $produitAjoute = Stock::create([
            'warehouse_id' => $_POST['warehouse_id'],
            'stock_type_id' => $_POST['stock_type_id'],
            'item_name' => trim($_POST['item_name']),
            'description' => trim($_POST['description']),
            'quantity' => $_POST['quantity'] ?? 0,
            'min_quantity' => $_POST['min_quantity'] ?? 10,
            'max_quantity' => $_POST['max_quantity'] ?? 1000,
            'unit_price' => $_POST['unit_price'] ?? 0,
            'is_active' => true
        ]);
        
        $message = "✅ Produit ajouté avec succès !";
        $messageType = 'success';
    } catch (\Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
        $messageType = 'error';
    }
}

// Récupérer les entrepôts et types de stock
$warehouses = Warehouse::where('is_active', true)->get();
$stockTypes = StockType::all();
$totalProduits = Stock::where('is_active', true)->count();

// Créer un entrepôt par défaut si aucun n'existe
if ($warehouses->isEmpty()) {
    $warehouse = Warehouse::create([
        'name' => 'Entrepôt Principal',
        'code' => 'EP-001',
        'address' => 'Adresse de l\'entrepôt',
        'manager' => 'Gestionnaire',
        'phone' => '00000000',
        'capacity' => 10000,
        'is_active' => true
    ]);
    $warehouses = collect([$warehouse]);
    $message = "ℹ️ Un entrepôt par défaut a été créé automatiquement.";
    $messageType = 'info';
}

// Créer les types de stock par défaut si aucun n'existe
if ($stockTypes->isEmpty()) {
    $types = [
        ['name' => 'Denrées alimentaires', 'code' => 'ALIM', 'description' => 'Produits alimentaires'],
        ['name' => 'Matériel humanitaire', 'code' => 'MAT', 'description' => 'Équipements humanitaires'],
        ['name' => 'Carburant', 'code' => 'CARB', 'description' => 'Carburants et lubrifiants'],
        ['name' => 'Médicaments', 'code' => 'MED', 'description' => 'Produits médicaux'],
    ];
    
    foreach ($types as $type) {
        StockType::create($type);
    }
    
    $stockTypes = StockType::all();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit - CSAR</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
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
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .success-box {
            background: #f0f9ff;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .success-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .success-box .produit-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        
        .success-box .produit-info strong {
            color: #333;
            font-size: 16px;
        }
        
        .success-box p {
            color: #555;
            line-height: 1.6;
            margin: 5px 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group label .required {
            color: #dc3545;
        }
        
        .form-group label .help {
            font-weight: 400;
            color: #666;
            font-size: 12px;
            display: block;
            margin-top: 3px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .btn {
            display: inline-block;
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 15px;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        }
        
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .stats p {
            color: #666;
            font-size: 13px;
        }
        
        .stats .number {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            display: block;
        }
        
        .exemples {
            background: #fff9e6;
            border: 1px solid #ffe08a;
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
        }
        
        .exemples h3 {
            color: #856404;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .exemples ul {
            margin-left: 20px;
            color: #856404;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group .form-control {
            flex: 1;
        }
        
        .input-group .unit {
            padding: 12px 15px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-weight: 600;
            color: #666;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>➕ Ajouter un Produit</h1>
                <p>Remplissez le formulaire pour ajouter un nouveau produit dans votre stock</p>
            </div>
            
            <div class="content">
                <?php if ($message): ?>
                    <div class="message <?= $messageType ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($produitAjoute): ?>
                    <div class="success-box">
                        <h3>🎉 Produit ajouté avec succès !</h3>
                        <div class="produit-info">
                            <strong><?= htmlspecialchars($produitAjoute->item_name) ?></strong>
                            <p><?= htmlspecialchars($produitAjoute->description) ?></p>
                            <p style="font-size: 12px; color: #888; margin-top: 10px;">
                                Quantité: <?= $produitAjoute->quantity ?> | 
                                Prix: <?= number_format($produitAjoute->unit_price, 0, ',', ' ') ?> FCFA
                            </p>
                        </div>
                        <p style="color: #28a745; font-weight: 600; text-align: center; margin-top: 15px;">
                            ✅ Ce produit est maintenant disponible pour les mouvements de stock !
                        </p>
                    </div>
                <?php endif; ?>
                
                <div class="stats">
                    <span class="number"><?= $totalProduits ?></span>
                    <p>Produit(s) actuellement dans le système</p>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>
                            Nom du Produit <span class="required">*</span>
                            <span class="help">Ex: Riz blanc, Huile végétale, Couvertures...</span>
                        </label>
                        <input type="text" 
                               name="item_name" 
                               class="form-control" 
                               required 
                               placeholder="Entrez le nom du produit"
                               autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            Description
                            <span class="help">Ex: Riz de qualité supérieure - sac de 50kg</span>
                        </label>
                        <textarea name="description" 
                                  class="form-control" 
                                  placeholder="Décrivez le produit (unité, qualité, conditionnement...)"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                Entrepôt <span class="required">*</span>
                            </label>
                            <select name="warehouse_id" class="form-control" required>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse->id ?>">
                                        <?= htmlspecialchars($warehouse->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Type de Stock <span class="required">*</span>
                            </label>
                            <select name="stock_type_id" class="form-control" required>
                                <?php foreach ($stockTypes as $type): ?>
                                    <option value="<?= $type->id ?>">
                                        <?= htmlspecialchars($type->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>
                                Quantité Initiale
                                <span class="help">Vous pouvez laisser à 0</span>
                            </label>
                            <input type="number" 
                                   name="quantity" 
                                   class="form-control" 
                                   value="0" 
                                   step="0.01" 
                                   min="0">
                        </div>
                        
                        <div class="form-group">
                            <label>
                                Seuil d'Alerte (Min)
                                <span class="help">Alerte si stock < ce seuil</span>
                            </label>
                            <input type="number" 
                                   name="min_quantity" 
                                   class="form-control" 
                                   value="10" 
                                   step="0.01" 
                                   min="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            Prix Unitaire
                            <span class="help">Optionnel - en FCFA</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   name="unit_price" 
                                   class="form-control" 
                                   value="0" 
                                   step="0.01" 
                                   min="0"
                                   placeholder="0">
                            <span class="unit">FCFA</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        ✅ Ajouter le Produit
                    </button>
                    
                    <a href="gestion_produits.php" class="btn btn-secondary">
                        📋 Voir tous les produits
                    </a>
                </form>
                
                <div class="exemples">
                    <h3>💡 Exemples de produits à ajouter :</h3>
                    <ul>
                        <li><strong>Denrées :</strong> Riz blanc, Maïs, Huile végétale, Haricots...</li>
                        <li><strong>Matériel :</strong> Tentes, Couvertures, Jerrycans, Bâches...</li>
                        <li><strong>Carburant :</strong> Essence, Gasoil, Pétrole...</li>
                        <li><strong>Médicaments :</strong> Paracétamol, Pansements, Sérum...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>







































