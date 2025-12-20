-- ============================================
-- Script SQL pour ajouter des produits
-- Exécutez ce fichier dans phpMyAdmin ou MySQL
-- ============================================

-- 1. Vérifier/Créer un entrepôt par défaut
INSERT INTO warehouses (name, code, address, manager, phone, capacity, is_active, created_at, updated_at)
SELECT 'Entrepôt Principal', 'EP-001', 'Adresse de l\'entrepôt', 'Gestionnaire', '00000000', 10000, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM warehouses WHERE code = 'EP-001');

-- 2. Vérifier/Créer les types de stock
INSERT INTO stock_types (name, code, description, created_at, updated_at)
SELECT 'Denrées alimentaires', 'ALIM', 'Produits alimentaires', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stock_types WHERE code = 'ALIM');

INSERT INTO stock_types (name, code, description, created_at, updated_at)
SELECT 'Matériel humanitaire', 'MAT', 'Équipements humanitaires', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stock_types WHERE code = 'MAT');

INSERT INTO stock_types (name, code, description, created_at, updated_at)
SELECT 'Carburant', 'CARB', 'Carburants et lubrifiants', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stock_types WHERE code = 'CARB');

INSERT INTO stock_types (name, code, description, created_at, updated_at)
SELECT 'Médicaments', 'MED', 'Produits médicaux', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stock_types WHERE code = 'MED');

-- 3. Ajouter les produits
-- Récupérer les IDs nécessaires
SET @warehouse_id = (SELECT id FROM warehouses WHERE code = 'EP-001' LIMIT 1);
SET @type_alim = (SELECT id FROM stock_types WHERE code = 'ALIM' LIMIT 1);
SET @type_mat = (SELECT id FROM stock_types WHERE code = 'MAT' LIMIT 1);
SET @type_carb = (SELECT id FROM stock_types WHERE code = 'CARB' LIMIT 1);
SET @type_med = (SELECT id FROM stock_types WHERE code = 'MED' LIMIT 1);

-- DENRÉES ALIMENTAIRES
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES
-- Céréales
(@warehouse_id, @type_alim, 'Riz blanc', 'Riz de qualité supérieure - sac de 50kg', 0, 10, 1000, 25000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Maïs', 'Maïs en grains - sac de 50kg', 0, 10, 1000, 18000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Mil', 'Mil en grains - sac de 50kg', 0, 10, 1000, 20000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Sorgho', 'Sorgho en grains - sac de 50kg', 0, 10, 1000, 19000, 1, NOW(), NOW()),

-- Légumineuses
(@warehouse_id, @type_alim, 'Haricots', 'Haricots secs - sac de 50kg', 0, 10, 1000, 30000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Niébé', 'Niébé (haricot local) - sac de 50kg', 0, 10, 1000, 28000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Arachides', 'Arachides décortiquées - sac de 25kg', 0, 10, 500, 35000, 1, NOW(), NOW()),

-- Huiles et matières grasses
(@warehouse_id, @type_alim, 'Huile végétale', 'Huile de cuisine - bidon de 20L', 0, 5, 500, 15000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Huile d\'arachide', 'Huile d\'arachide - bidon de 20L', 0, 5, 500, 18000, 1, NOW(), NOW()),

-- Farines et dérivés
(@warehouse_id, @type_alim, 'Farine de blé', 'Farine pour pain - sac de 25kg', 0, 10, 800, 12000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Farine de maïs', 'Farine de maïs - sac de 25kg', 0, 10, 800, 10000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Pâtes alimentaires', 'Pâtes de blé - carton de 10kg', 0, 20, 1000, 8000, 1, NOW(), NOW()),

-- Produits de base
(@warehouse_id, @type_alim, 'Sucre', 'Sucre cristallisé - sac de 50kg', 0, 10, 800, 35000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Sel', 'Sel de cuisine - sac de 25kg', 0, 10, 500, 8000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Tomate concentrée', 'Concentré de tomate - carton de 70g x 50', 0, 20, 1000, 12000, 1, NOW(), NOW()),

-- Produits enrichis
(@warehouse_id, @type_alim, 'Lait en poudre', 'Lait entier en poudre - carton de 10kg', 0, 5, 500, 45000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Plumpy Nut', 'Pâte nutritionnelle thérapeutique - carton', 0, 10, 300, 55000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'CSB++', 'Mélange maïs-soja enrichi - sac de 25kg', 0, 10, 500, 22000, 1, NOW(), NOW()),

-- Condiments
(@warehouse_id, @type_alim, 'Cube Maggi', 'Cubes d\'assaisonnement - carton de 100', 0, 20, 1000, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_alim, 'Poivre', 'Poivre moulu - sachet de 1kg', 0, 10, 200, 8000, 1, NOW(), NOW());

-- MATÉRIEL HUMANITAIRE
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES
-- Abris et protection
(@warehouse_id, @type_mat, 'Tentes', 'Tentes familiales (capacité 6 personnes)', 0, 5, 100, 150000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Bâches', 'Bâches en plastique résistant - 4x6m', 0, 10, 500, 8000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Couvertures', 'Couvertures chaudes en laine', 0, 20, 1000, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Nattes', 'Nattes en plastique tressé', 0, 20, 500, 3000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Moustiquaires', 'Moustiquaires imprégnées d\'insecticide', 0, 20, 1000, 4000, 1, NOW(), NOW()),

-- Eau et assainissement
(@warehouse_id, @type_mat, 'Jerrycans 20L', 'Jerrycans en plastique 20 litres', 0, 30, 1000, 3000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Jerrycans 10L', 'Jerrycans en plastique 10 litres', 0, 30, 1000, 2500, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Seaux 15L', 'Seaux en plastique avec couvercle', 0, 30, 1000, 2000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Purificateur d\'eau', 'Comprimés de purification d\'eau - boîte', 0, 20, 500, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Filtres à eau', 'Filtres à eau céramique', 0, 10, 200, 25000, 1, NOW(), NOW()),

-- Hygiène
(@warehouse_id, @type_mat, 'Kits hygiène', 'Kits d\'hygiène familiale complets', 0, 20, 500, 15000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Savon de ménage', 'Savon de ménage - carton de 72 pains', 0, 10, 500, 12000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Savon de toilette', 'Savon de toilette - carton de 72 pains', 0, 10, 500, 15000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Détergent', 'Poudre à lessive - sac de 10kg', 0, 10, 500, 8000, 1, NOW(), NOW()),

-- Cuisine
(@warehouse_id, @type_mat, 'Marmites', 'Marmites en aluminium (tailles variées)', 0, 10, 300, 12000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Ustensiles de cuisine', 'Kit ustensiles (cuillère, louche, couteau)', 0, 20, 500, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Assiettes', 'Assiettes en plastique - lot de 10', 0, 20, 500, 3000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Gobelets', 'Gobelets en plastique - lot de 10', 0, 20, 500, 2000, 1, NOW(), NOW()),

-- Éclairage
(@warehouse_id, @type_mat, 'Lampes solaires', 'Lampes solaires portables LED', 0, 10, 500, 12000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Lampes torches', 'Lampes torches à piles', 0, 20, 500, 3000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Piles', 'Piles alcalines AA/AAA - lot de 10', 0, 30, 1000, 2500, 1, NOW(), NOW()),

-- Divers
(@warehouse_id, @type_mat, 'Cordes', 'Cordes en nylon - rouleau de 50m', 0, 10, 200, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_mat, 'Sacs en toile', 'Sacs en toile résistante - lot de 10', 0, 20, 500, 8000, 1, NOW(), NOW());

-- CARBURANT
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES
(@warehouse_id, @type_carb, 'Essence', 'Essence ordinaire - litre', 0, 100, 10000, 650, 1, NOW(), NOW()),
(@warehouse_id, @type_carb, 'Gasoil', 'Gasoil - litre', 0, 100, 10000, 600, 1, NOW(), NOW()),
(@warehouse_id, @type_carb, 'Pétrole', 'Pétrole lampant - litre', 0, 50, 5000, 550, 1, NOW(), NOW()),
(@warehouse_id, @type_carb, 'Huile moteur', 'Huile moteur synthétique - bidon de 5L', 0, 10, 200, 15000, 1, NOW(), NOW());

-- MÉDICAMENTS
INSERT INTO stocks (warehouse_id, stock_type_id, item_name, description, quantity, min_quantity, max_quantity, unit_price, is_active, created_at, updated_at)
VALUES
-- Médicaments de base
(@warehouse_id, @type_med, 'Paracétamol 500mg', 'Comprimés - boîte de 100', 0, 50, 2000, 2000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Ibuprofène 400mg', 'Comprimés - boîte de 100', 0, 30, 1000, 3500, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Amoxicilline 500mg', 'Gélules antibiotiques - boîte de 24', 0, 30, 1000, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Métronidazole', 'Comprimés antiparasitaires - boîte', 0, 20, 500, 3000, 1, NOW(), NOW()),

-- Solutions et sérums
(@warehouse_id, @type_med, 'Sérum physiologique', 'Solution saline 0.9% - litre', 0, 20, 1000, 1500, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'SRO', 'Sachets de réhydratation orale - carton', 0, 50, 2000, 500, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Perfusion glucosée', 'Solution glucosée 5% - 1L', 0, 20, 500, 2500, 1, NOW(), NOW()),

-- Soins et pansements
(@warehouse_id, @type_med, 'Pansements stériles', 'Pansements divers tailles - boîte', 0, 30, 1000, 3000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Compresses stériles', 'Compresses 10x10cm - paquet de 100', 0, 30, 1000, 4000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Sparadrap', 'Sparadrap médical - rouleau', 0, 20, 500, 1500, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Bandes élastiques', 'Bandes de contention - rouleau', 0, 20, 500, 2000, 1, NOW(), NOW()),

-- Désinfectants
(@warehouse_id, @type_med, 'Alcool médical', 'Alcool à 70% - litre', 0, 20, 500, 4000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Bétadine', 'Solution antiseptique - flacon 125ml', 0, 20, 500, 3500, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Gel hydroalcoolique', 'Gel désinfectant pour les mains - 500ml', 0, 30, 1000, 2500, 1, NOW(), NOW()),

-- Matériel médical
(@warehouse_id, @type_med, 'Gants médicaux', 'Gants en latex - boîte de 100', 0, 30, 1000, 8000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Masques chirurgicaux', 'Masques à usage unique - boîte de 50', 0, 50, 2000, 3000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Thermomètres', 'Thermomètres digitaux', 0, 10, 200, 5000, 1, NOW(), NOW()),
(@warehouse_id, @type_med, 'Seringues', 'Seringues stériles - boîte de 100', 0, 20, 1000, 6000, 1, NOW(), NOW());

-- ============================================
-- Vérification
-- ============================================
SELECT 
    st.name AS 'Type de Stock',
    COUNT(s.id) AS 'Nombre de Produits'
FROM stock_types st
LEFT JOIN stocks s ON s.stock_type_id = st.id AND s.is_active = 1
GROUP BY st.id, st.name
ORDER BY st.name;

SELECT '✅ Script exécuté avec succès!' AS 'Résultat';
SELECT CONCAT('Total de produits ajoutés: ', COUNT(*)) AS 'Information'
FROM stocks
WHERE is_active = 1;







































