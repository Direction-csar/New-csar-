# 📦 Guide d'Ajout de Produits pour les Mouvements de Stock

## 🎯 Problème

Le dropdown "Produit/Stock" dans le formulaire de mouvement de stock est vide car il n'y a pas de produits dans la base de données.

## ✅ Solutions (3 méthodes au choix)

### Méthode 1 : Interface Web (LA PLUS SIMPLE) ⭐

1. **Ouvrez votre navigateur** et allez sur :
   ```
   http://localhost/csar/gestion_produits.php
   ```

2. **Vous verrez** :
   - 📊 Statistiques des produits actuels
   - ➕ Formulaire pour ajouter un nouveau produit
   - 📋 Liste de tous les produits existants

3. **Remplissez le formulaire** :
   - Nom du Produit : Ex: `Riz blanc`
   - Description : Ex: `Riz de qualité supérieure - sac de 50kg`
   - Entrepôt : Sélectionnez un entrepôt
   - Type de Stock : Sélectionnez (Denrées alimentaires, Matériel, etc.)
   - Quantité Initiale : Ex: `0` (sera mise à jour avec les mouvements)
   - Prix Unitaire : Ex: `25000` FCFA

4. **Cliquez sur "Ajouter le Produit"** ✅

5. **C'est fait !** Le produit apparaîtra maintenant dans votre liste déroulante.

---

### Méthode 2 : Script SQL dans phpMyAdmin 🔧

**Si vous préférez ajouter plusieurs produits d'un coup :**

1. **Ouvrez phpMyAdmin** dans votre navigateur :
   ```
   http://localhost/phpmyadmin
   ```

2. **Sélectionnez votre base de données** (généralement `csar` ou le nom de votre projet)

3. **Cliquez sur l'onglet "SQL"** en haut

4. **Ouvrez le fichier** `ajouter_produits.sql` avec un éditeur de texte (Notepad++)

5. **Copiez tout le contenu** du fichier

6. **Collez-le dans la zone de texte SQL** de phpMyAdmin

7. **Cliquez sur "Exécuter"** (bouton en bas à droite)

8. **Résultat** : Plus de 60 produits seront ajoutés automatiquement dans toutes les catégories :
   - 🌾 Denrées alimentaires (20 produits)
   - 🏕️ Matériel humanitaire (23 produits)
   - ⛽ Carburant (4 produits)
   - 💊 Médicaments (18 produits)

---

### Méthode 3 : Script PHP en ligne de commande 💻

**Pour les utilisateurs avancés :**

1. **Ouvrez un terminal/invite de commande**

2. **Naviguez vers le dossier du projet** :
   ```bash
   cd C:\xampp\htdocs\csar
   ```

3. **Exécutez le script** :
   ```bash
   php ajouter_produits_stock.php
   ```

4. **Suivez les instructions** à l'écran :
   - Tapez `o` pour ajouter tous les produits
   - Ou tapez `n` et choisissez une catégorie spécifique

5. **Attendez la confirmation** ✅

---

## 📋 Structure de la Table Stocks

Les produits sont stockés dans la table `stocks` avec les colonnes suivantes :

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | ID unique |
| `warehouse_id` | INT | ID de l'entrepôt (obligatoire) |
| `stock_type_id` | INT | ID du type de stock (obligatoire) |
| `item_name` | VARCHAR | Nom du produit (obligatoire) |
| `description` | TEXT | Description détaillée |
| `quantity` | DECIMAL | Quantité actuelle en stock |
| `min_quantity` | DECIMAL | Seuil d'alerte minimum |
| `max_quantity` | DECIMAL | Capacité maximale |
| `unit_price` | DECIMAL | Prix unitaire en FCFA |
| `is_active` | BOOLEAN | Produit actif ou non |

---

## 🔍 Vérification

### Vérifier que les produits sont bien ajoutés :

1. **Via l'interface web** :
   ```
   http://localhost/csar/gestion_produits.php
   ```
   Vous devriez voir la liste complète des produits.

2. **Via MySQL** :
   ```sql
   SELECT item_name, quantity, unit_price 
   FROM stocks 
   WHERE is_active = 1
   ORDER BY item_name;
   ```

3. **Dans l'application** :
   - Connectez-vous en tant qu'admin
   - Allez sur : **Admin > Gestion des Stocks > Nouveau Mouvement**
   - Le dropdown "Produit/Stock" devrait maintenant afficher tous les produits

---

## 📝 Exemple d'Ajout Manuel via SQL

Si vous voulez ajouter un seul produit manuellement :

```sql
-- 1. Récupérer les IDs nécessaires
SELECT id FROM warehouses WHERE is_active = 1 LIMIT 1;  -- Ex: 1
SELECT id FROM stock_types WHERE name = 'Denrées alimentaires' LIMIT 1;  -- Ex: 1

-- 2. Ajouter le produit
INSERT INTO stocks (
    warehouse_id, 
    stock_type_id, 
    item_name, 
    description, 
    quantity, 
    min_quantity, 
    max_quantity, 
    unit_price, 
    is_active, 
    created_at, 
    updated_at
)
VALUES (
    1,                                    -- ID de l'entrepôt
    1,                                    -- ID du type de stock
    'Riz blanc',                          -- Nom du produit
    'Riz de qualité - sac de 50kg',      -- Description
    0,                                    -- Quantité initiale
    10,                                   -- Seuil minimum
    1000,                                 -- Capacité max
    25000,                                -- Prix unitaire
    1,                                    -- Actif
    NOW(),                                -- Date de création
    NOW()                                 -- Date de mise à jour
);
```

---

## 🚨 Problèmes Courants

### Problème 1 : "Aucun entrepôt disponible"

**Solution** : Créez d'abord un entrepôt
```sql
INSERT INTO warehouses (name, code, address, manager, phone, capacity, is_active, created_at, updated_at)
VALUES ('Entrepôt Principal', 'EP-001', 'Adresse', 'Gestionnaire', '00000000', 10000, 1, NOW(), NOW());
```

### Problème 2 : "Aucun type de stock trouvé"

**Solution** : Créez les types de stock
```sql
INSERT INTO stock_types (name, code, description, created_at, updated_at)
VALUES 
('Denrées alimentaires', 'ALIM', 'Produits alimentaires', NOW(), NOW()),
('Matériel humanitaire', 'MAT', 'Équipements humanitaires', NOW(), NOW()),
('Carburant', 'CARB', 'Carburants', NOW(), NOW()),
('Médicaments', 'MED', 'Produits médicaux', NOW(), NOW());
```

### Problème 3 : Le dropdown reste vide après ajout

**Solutions** :
1. Vérifiez que `is_active = 1` pour vos produits
2. Videz le cache du navigateur (Ctrl + F5)
3. Vérifiez la requête dans le contrôleur :
   ```php
   // app/Http/Controllers/Admin/StockController.php
   $stocks = Stock::with('warehouse')->where('is_active', true)->get();
   ```

---

## 📞 Besoin d'Aide ?

Si vous rencontrez des difficultés :

1. Vérifiez que XAMPP est bien démarré (Apache + MySQL)
2. Vérifiez que votre base de données existe
3. Vérifiez les erreurs dans : `storage/logs/laravel.log`
4. Testez la connexion à la base de données

---

## 🎉 Après l'Ajout des Produits

Une fois les produits ajoutés, vous pouvez :

1. **Créer des mouvements de stock** :
   - Admin > Gestion des Stocks > Nouveau Mouvement
   - Sélectionnez le type (Entrée/Sortie)
   - Choisissez un produit dans la liste
   - Indiquez la quantité
   - Ajoutez une description

2. **Consulter l'historique** :
   - Admin > Gestion des Stocks > Voir l'historique

3. **Gérer les alertes** :
   - Les produits dont la quantité descend sous `min_quantity` génèrent une alerte

---

## 📚 Fichiers Créés

| Fichier | Description | Usage |
|---------|-------------|-------|
| `gestion_produits.php` | Interface web graphique | ⭐ Recommandé - Simple et visuel |
| `ajouter_produits.sql` | Script SQL complet | Pour ajout en masse via phpMyAdmin |
| `ajouter_produits_stock.php` | Script PHP interactif | Pour ligne de commande |
| `GUIDE_AJOUT_PRODUITS.md` | Ce guide | Documentation |

---

**✅ Vous êtes maintenant prêt à gérer vos stocks !**







































