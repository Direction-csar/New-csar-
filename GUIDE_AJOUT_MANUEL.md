# 📝 Guide d'Ajout Manuel de Produits

## 🎯 Objectif
Ce guide vous montre comment ajouter VOS PROPRES produits manuellement, un par un, selon vos besoins.

---

## ✅ Méthode 1 : Formulaire Web Simple (RECOMMANDÉ)

### 📍 Ouvrez dans votre navigateur :
```
http://localhost/csar/ajouter_produit_manuel.php
```

### ✏️ Remplissez le formulaire :
1. **Nom du Produit** : Le nom de votre produit (Ex: Riz blanc)
2. **Description** : Plus de détails (Ex: Riz de qualité - sac de 50kg)
3. **Entrepôt** : Sélectionnez l'entrepôt
4. **Type de Stock** : Choisissez la catégorie
5. **Quantité Initiale** : Mettez 0 si vous n'avez pas encore de stock
6. **Prix Unitaire** : Le prix en FCFA

### 🎉 Cliquez sur "Ajouter" et c'est terminé !

---

## ✅ Méthode 2 : SQL dans phpMyAdmin

### Étape 1 : Ouvrir phpMyAdmin
```
http://localhost/phpmyadmin
```

### Étape 2 : Sélectionner votre base de données
Cliquez sur votre base de données (probablement nommée `csar`)

### Étape 3 : Cliquer sur l'onglet "SQL"

### Étape 4 : Copier et modifier UN de ces exemples

---

## 📋 EXEMPLES SQL À COPIER-COLLER

### 🌾 Exemple 1 : Ajouter du RIZ

```sql
-- Insérer du Riz blanc
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
    1,                                  -- ID de l'entrepôt (changez si besoin)
    1,                                  -- ID du type "Denrées alimentaires"
    'Riz blanc',                        -- ← CHANGEZ LE NOM ICI
    'Riz de qualité - sac de 50kg',    -- ← CHANGEZ LA DESCRIPTION ICI
    0,                                  -- Quantité initiale (0 = pas de stock)
    10,                                 -- Alerte si moins de 10
    1000,                               -- Capacité maximale
    25000,                              -- ← CHANGEZ LE PRIX ICI (en FCFA)
    1,                                  -- Actif
    NOW(),                              -- Date création
    NOW()                               -- Date modification
);
```

---

### 🫘 Exemple 2 : Ajouter des HARICOTS

```sql
INSERT INTO stocks (
    warehouse_id, stock_type_id, item_name, description, 
    quantity, min_quantity, max_quantity, unit_price, is_active, 
    created_at, updated_at
)
VALUES (
    1,                          -- Entrepôt
    1,                          -- Type: Denrées alimentaires
    'Haricots',                 -- ← VOTRE PRODUIT
    'Haricots secs - sac de 50kg',  -- ← DESCRIPTION
    0,                          -- Quantité = 0
    10,                         -- Seuil alerte = 10
    1000,                       -- Max = 1000
    30000,                      -- ← PRIX en FCFA
    1, NOW(), NOW()
);
```

---

### 🛢️ Exemple 3 : Ajouter de l'HUILE

```sql
INSERT INTO stocks (
    warehouse_id, stock_type_id, item_name, description, 
    quantity, min_quantity, max_quantity, unit_price, is_active, 
    created_at, updated_at
)
VALUES (
    1, 1,
    'Huile végétale',           -- ← VOTRE PRODUIT
    'Huile de cuisine - bidon de 20L',  -- ← DESCRIPTION
    0, 5, 500,
    15000,                      -- ← PRIX
    1, NOW(), NOW()
);
```

---

### 🏕️ Exemple 4 : Ajouter des COUVERTURES (Matériel)

```sql
INSERT INTO stocks (
    warehouse_id, stock_type_id, item_name, description, 
    quantity, min_quantity, max_quantity, unit_price, is_active, 
    created_at, updated_at
)
VALUES (
    1,
    2,                          -- Type: Matériel humanitaire (ID = 2)
    'Couvertures',              -- ← VOTRE PRODUIT
    'Couvertures chaudes en laine',  -- ← DESCRIPTION
    0, 20, 1000,
    5000,                       -- ← PRIX
    1, NOW(), NOW()
);
```

---

### ⛽ Exemple 5 : Ajouter de l'ESSENCE (Carburant)

```sql
INSERT INTO stocks (
    warehouse_id, stock_type_id, item_name, description, 
    quantity, min_quantity, max_quantity, unit_price, is_active, 
    created_at, updated_at
)
VALUES (
    1,
    3,                          -- Type: Carburant (ID = 3)
    'Essence',                  -- ← VOTRE PRODUIT
    'Essence ordinaire - litre',  -- ← DESCRIPTION
    0, 100, 10000,
    650,                        -- ← PRIX par litre
    1, NOW(), NOW()
);
```

---

### 💊 Exemple 6 : Ajouter du PARACÉTAMOL (Médicament)

```sql
INSERT INTO stocks (
    warehouse_id, stock_type_id, item_name, description, 
    quantity, min_quantity, max_quantity, unit_price, is_active, 
    created_at, updated_at
)
VALUES (
    1,
    4,                          -- Type: Médicaments (ID = 4)
    'Paracétamol 500mg',        -- ← VOTRE PRODUIT
    'Comprimés - boîte de 100',  -- ← DESCRIPTION
    0, 50, 2000,
    2000,                       -- ← PRIX par boîte
    1, NOW(), NOW()
);
```

---

## 🔧 MODIFIER LES EXEMPLES

### Pour ajouter VOTRE propre produit :

1. **Copiez un exemple ci-dessus**
2. **Changez ces valeurs** :
   - `item_name` → Le nom de votre produit
   - `description` → La description de votre produit
   - `unit_price` → Le prix en FCFA
   - `stock_type_id` → Le type de produit :
     - `1` = Denrées alimentaires
     - `2` = Matériel humanitaire
     - `3` = Carburant
     - `4` = Médicaments

3. **Collez dans phpMyAdmin** (onglet SQL)
4. **Cliquez sur "Exécuter"**

---

## 📊 LES IDs DES TYPES DE STOCK

| ID | Type | Pour quoi ? |
|----|------|-------------|
| 1 | Denrées alimentaires | Riz, Maïs, Huile, Haricots, Farine... |
| 2 | Matériel humanitaire | Tentes, Couvertures, Jerrycans, Bâches... |
| 3 | Carburant | Essence, Gasoil, Pétrole... |
| 4 | Médicaments | Paracétamol, Pansements, Sérum... |

---

## ❓ Questions Fréquentes

### Q: Comment savoir l'ID de mon entrepôt ?
**R:** Exécutez cette requête dans phpMyAdmin :
```sql
SELECT id, name FROM warehouses WHERE is_active = 1;
```

### Q: Puis-je mettre la quantité initiale à 0 ?
**R:** Oui ! Vous ajouterez du stock plus tard avec les mouvements d'entrée.

### Q: Que signifie `min_quantity` ?
**R:** C'est le seuil d'alerte. Si le stock descend en dessous, vous recevrez une alerte.

### Q: Dois-je remplir le prix ?
**R:** Non, c'est optionnel. Vous pouvez mettre 0 si vous ne connaissez pas le prix.

---

## ✅ VÉRIFIER VOS PRODUITS

### Voir tous vos produits ajoutés :
```sql
SELECT 
    item_name AS 'Produit',
    description AS 'Description',
    quantity AS 'Quantité',
    unit_price AS 'Prix FCFA'
FROM stocks
WHERE is_active = 1
ORDER BY item_name;
```

### Compter vos produits :
```sql
SELECT COUNT(*) AS 'Nombre de produits' 
FROM stocks 
WHERE is_active = 1;
```

---

## 🎯 APRÈS AVOIR AJOUTÉ VOS PRODUITS

1. **Connectez-vous à l'application** en tant qu'Admin
2. **Allez sur** : Admin > Gestion des Stocks > Nouveau Mouvement
3. **Vous verrez maintenant vos produits** dans le dropdown "Produit/Stock"
4. **Créez une entrée de stock** pour ajouter des quantités

---

## 📞 BESOIN D'AIDE ?

### Interface Web :
```
http://localhost/csar/ajouter_produit_manuel.php
```
← **UTILISEZ CECI** si vous n'êtes pas à l'aise avec SQL !

### Voir tous les produits :
```
http://localhost/csar/gestion_produits.php
```

---

## 💡 ASTUCE

**Pour ajouter rapidement plusieurs produits similaires :**

1. Ajoutez le premier produit avec le formulaire web
2. Copiez l'exemple SQL
3. Modifiez juste le nom et le prix
4. Collez dans phpMyAdmin plusieurs fois en changeant les valeurs

**Exemple :** Ajouter Riz, Maïs, Mil (tous des céréales) :

```sql
-- Riz
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES (1, 1, 'Riz blanc', 'Sac de 50kg', 0, 10, 1000, 25000, 1, NOW(), NOW());

-- Maïs
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES (1, 1, 'Maïs', 'Sac de 50kg', 0, 10, 1000, 18000, 1, NOW(), NOW());

-- Mil
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES (1, 1, 'Mil', 'Sac de 50kg', 0, 10, 1000, 20000, 1, NOW(), NOW());
```

Copiez les 3 lignes d'un coup et exécutez → 3 produits ajoutés ! ✅

---

**Vous êtes prêt ! Ajoutez vos produits et commencez à gérer votre stock ! 🎉**







































