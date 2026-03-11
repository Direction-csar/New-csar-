# 📦 Ajout de Produits pour les Mouvements de Stock

## 🎯 Situation

Vous avez un formulaire de mouvement de stock avec un dropdown **"Produit/Stock"** vide. Vous devez ajouter **vos propres produits** manuellement.

---

## ⚡ DÉMARRAGE RAPIDE (30 secondes)

### 🌐 Ouvrez dans votre navigateur :

```
http://localhost/csar/START_HERE.html
```

👉 **Cliquez sur "Formulaire Simple"** et ajoutez votre premier produit !

---

## 📝 3 Façons d'Ajouter VOS Produits

### 1️⃣ Formulaire Web Simple ⭐ **RECOMMANDÉ**

**Le plus facile - Pas besoin de connaître SQL !**

```
http://localhost/csar/ajouter_produit_manuel.php
```

**Avantages :**
- ✅ Interface visuelle simple
- ✅ Formulaire guidé
- ✅ Validation automatique
- ✅ Confirmation immédiate

**Étapes :**
1. Remplissez le nom du produit
2. Ajoutez une description (optionnelle)
3. Choisissez l'entrepôt et le type
4. Indiquez le prix (optionnel)
5. Cliquez sur "Ajouter"
6. ✅ Terminé !

---

### 2️⃣ Interface de Gestion Complète

**Pour gérer tous vos produits**

```
http://localhost/csar/gestion_produits.php
```

**Avantages :**
- ✅ Voir tous les produits
- ✅ Ajouter de nouveaux produits
- ✅ Supprimer des produits
- ✅ Statistiques en temps réel

---

### 3️⃣ SQL Manuel (phpMyAdmin)

**Pour ceux qui connaissent SQL**

1. Ouvrez phpMyAdmin : `http://localhost/phpmyadmin`
2. Sélectionnez votre base de données
3. Cliquez sur l'onglet **"SQL"**
4. Copiez et modifiez cet exemple :

```sql
-- Exemple : Ajouter du Riz blanc
INSERT INTO stocks (
    warehouse_id, stock_type_id, item_name, description, 
    quantity, min_quantity, max_quantity, unit_price, 
    is_active, created_at, updated_at
)
VALUES (
    1,                  -- Entrepôt
    1,                  -- Type: 1=Denrées, 2=Matériel, 3=Carburant, 4=Médicaments
    'Riz blanc',        -- ← CHANGEZ : Nom de votre produit
    'Sac de 50kg',      -- ← CHANGEZ : Description
    0,                  -- Quantité initiale
    10,                 -- Seuil d'alerte
    1000,               -- Capacité max
    25000,              -- ← CHANGEZ : Prix en FCFA
    1, NOW(), NOW()
);
```

5. Cliquez sur **"Exécuter"**

📖 **Plus d'exemples :** Consultez `GUIDE_AJOUT_MANUEL.md`

---

## 🗂️ Types de Produits

Choisissez le bon type (`stock_type_id`) pour vos produits :

| ID | Type | Exemples |
|----|------|----------|
| **1** | 🌾 **Denrées alimentaires** | Riz, Maïs, Huile, Haricots, Farine, Sucre, Sel |
| **2** | 🏕️ **Matériel humanitaire** | Tentes, Couvertures, Bâches, Jerrycans, Moustiquaires |
| **3** | ⛽ **Carburant** | Essence, Gasoil, Pétrole |
| **4** | 💊 **Médicaments** | Paracétamol, Pansements, Sérum, Antiseptiques |

---

## 📋 Exemple : Ajouter Vos 5 Premiers Produits

### Via le Formulaire Web

1. **Riz blanc**
   - Description : "Sac de 50kg"
   - Type : Denrées alimentaires
   - Prix : 25000 FCFA

2. **Huile végétale**
   - Description : "Bidon de 20L"
   - Type : Denrées alimentaires
   - Prix : 15000 FCFA

3. **Couvertures**
   - Description : "Couvertures chaudes"
   - Type : Matériel humanitaire
   - Prix : 5000 FCFA

4. **Essence**
   - Description : "Litre"
   - Type : Carburant
   - Prix : 650 FCFA

5. **Paracétamol**
   - Description : "Boîte de 100 comprimés"
   - Type : Médicaments
   - Prix : 2000 FCFA

---

## ✅ Vérifier Vos Produits

### Option 1 : Interface Web
```
http://localhost/csar/gestion_produits.php
```

### Option 2 : Requête SQL
```sql
SELECT item_name, quantity, unit_price 
FROM stocks 
WHERE is_active = 1
ORDER BY item_name;
```

---

## 🎉 Après l'Ajout des Produits

### Où les retrouver ?

1. **Connectez-vous** à l'application en tant qu'Admin

2. **Naviguez vers** : 
   ```
   Admin > Gestion des Stocks > Nouveau Mouvement
   ```

3. **Le dropdown "Produit/Stock"** affiche maintenant tous vos produits ! 🎊

4. **Créez votre premier mouvement** :
   - **Type "Entrée"** → pour ajouter du stock
   - **Type "Sortie"** → pour retirer du stock

---

## 🔧 Configuration Automatique

Les scripts créent automatiquement si nécessaire :

✅ **Entrepôt par défaut** ("Entrepôt Principal")  
✅ **Types de stock** (Denrées, Matériel, Carburant, Médicaments)

---

## ❓ FAQ

### Q : Dois-je remplir tous les champs ?
**R :** Non. Seulement le **nom**, **entrepôt** et **type** sont obligatoires.

### Q : Quelle quantité initiale mettre ?
**R :** Mettez **0** si vous n'avez pas encore de stock. Vous ajouterez les quantités avec les mouvements d'entrée.

### Q : Le prix est-il obligatoire ?
**R :** Non, c'est optionnel. Vous pouvez mettre **0** et le modifier plus tard.

### Q : Comment modifier ou supprimer un produit ?
**R :** Utilisez l'interface de gestion : `gestion_produits.php`

### Q : Pourquoi le dropdown reste vide ?
**R :** Vérifiez que :
- XAMPP est démarré (Apache + MySQL)
- Vous avez bien ajouté des produits avec `is_active = 1`
- Vous avez rafraîchi la page (Ctrl + F5)

---

## 📁 Fichiers Créés

Tous dans votre dossier `C:\xampp\htdocs\csar\` :

| Fichier | Description |
|---------|-------------|
| **START_HERE.html** | 🏠 Page d'accueil - Commencez ici ! |
| **ajouter_produit_manuel.php** | ✏️ Formulaire simple pour ajouter 1 produit |
| **gestion_produits.php** | 🏪 Interface complète de gestion |
| **GUIDE_AJOUT_MANUEL.md** | 📖 Guide détaillé avec exemples SQL |
| **COMMENT_AJOUTER_PRODUITS.txt** | 📄 Instructions simples en texte |
| **README_PRODUITS.md** | 📋 Ce fichier - Vue d'ensemble |
| **ajouter_produits.sql** | 🗄️ Script SQL avec 60+ produits pré-définis |
| **test_produits.php** | 🧪 Script de test de configuration |

---

## 🚀 Action Immédiate

### Pour commencer maintenant :

```
1. Ouvrez votre navigateur
2. Allez sur : http://localhost/csar/START_HERE.html
3. Cliquez sur "Formulaire Simple"
4. Ajoutez votre premier produit
5. Créez votre premier mouvement de stock !
```

---

## 💡 Conseils

- **Commencez petit** : Ajoutez 3-5 produits pour tester
- **Utilisez le formulaire web** : C'est le plus simple
- **Quantité initiale à 0** : Normal, vous la mettrez à jour avec les mouvements
- **Testez ensuite** : Créez une entrée de stock pour voir comment ça marche

---

## 🆘 Besoin d'Aide ?

1. **Guide détaillé** : `GUIDE_AJOUT_MANUEL.md`
2. **Instructions simples** : `COMMENT_AJOUTER_PRODUITS.txt`
3. **Test de configuration** : Exécutez `php test_produits.php`

---

## ✨ Résumé en 3 Étapes

```
1. http://localhost/csar/ajouter_produit_manuel.php
2. Remplissez le formulaire avec VOS produits
3. Créez des mouvements de stock !
```

**C'est aussi simple que ça ! 🎉**

---

*Système de Gestion des Stocks - CSAR*












































