# 📱 Application Mobile de Collecte SIM - Documentation Technique

## 🎯 **Vue d'ensemble**

L'application mobile de collecte SIM est conçue pour permettre aux collecteurs de saisir les données des marchés directement depuis leur téléphone ou tablette, avec synchronisation automatique vers la plateforme CSAR.

## 🏗️ **Architecture Technique**

### **Backend Laravel**
```
📁 Database
├── sim_collectors (collecteurs SIM)
├── sim_mobile_collections (données collectées mobile)
└── sim_sync_logs (historique de synchronisation)

📁 Models
├── SimCollector (gestion des collecteurs)
├── SimMobileCollection (données de collecte)
└── SimSyncLog (logs de synchronisation)

📁 API Controller
└── SimCollectionController (endpoints mobile)

📁 Routes
└── /api/mobile/v1/* (API REST)
```

### **Endpoints API**

#### **Authentification**
```http
POST /api/mobile/v1/login
{
  "email": "mamadou.diallo@sim.sn",
  "password": "password123",
  "device_token": "optional"
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "token": "sanctum_token_here",
    "collector": {
      "id": 1,
      "name": "Mamadou Diallo",
      "assigned_zones": ["Dakar", "Thiès"]
    }
  }
}
```

#### **Données de référence**
```http
GET /api/mobile/v1/markets     // Liste des marchés
GET /api/mobile/v1/products    // Liste des produits
```

#### **Collecte de données**
```http
POST /api/mobile/v1/collections
{
  "market_id": 1,
  "product_id": 1,
  "price": 350.50,
  "retail_price": 400.00,
  "wholesale_price": 320.00,
  "collection_date": "2026-03-13",
  "latitude": 14.6928,
  "longitude": -17.4467,
  "photos": ["url1", "url2"],
  "metadata": {}
}
```

#### **Synchronisation**
```http
POST /api/mobile/v1/sync        // Synchroniser les données
GET /api/mobile/v1/collections/pending  // Données en attente
GET /api/mobile/v1/sync/history        // Historique des syncs
```

## 📱 **Application Mobile React Native**

### **Structure de l'App**
```
src/
├── screens/
│   ├── LoginScreen.js
│   ├── HomeScreen.js
│   ├── CollectScreen.js
│   ├── SyncScreen.js
│   └── ProfileScreen.js
├── services/
│   ├── api.js          # Service API
│   ├── storage.js      # Stockage local
│   └── gps.js          # Géolocalisation
├── components/
│   ├── FormInput.js
│   ├── MarketSelector.js
│   └── PhotoCapture.js
└── utils/
    ├── offline.js      # Mode hors-ligne
    └── validation.js   # Validation des données
```

### **Fonctionnalités Clés**

#### **🔄 Mode Hors-ligne**
- Stockage local des collectes
- Synchronisation automatique quand connexion disponible
- File d'attente intelligente

#### **📍 GPS Géolocalisation**
- Capture automatique des coordonnées
- Validation de la zone autorisée
- Carte interactive des marchés

#### **📸 Capture Photos**
- Photos des étals et produits
- Compression automatique
- Stockage optimisé

#### **🔐 Sécurité**
- Token JWT sécurisé
- Chiffrement des données locales
- Validation des permissions

## 🚀 **Déploiement Play Store**

### **Configuration Build**
```json
{
  "name": "CSAR SIM Collect",
  "package": "sn.csar.simcollect",
  "version": "1.0.0",
  "platform": "android",
  "minSdk": 21,
  "targetSdk": 33
}
```

### **Étapes de Déploiement**
1. **Build APK de production**
2. **Signature avec keystore CSAR**
3. **Création compte Play Store**
4. **Soumission pour validation**
5. **Publication progressive**

## 📊 **Flux de Données**

```
📱 App Mobile
    ↓ (API REST)
🌐 Laravel API
    ↓
💾 MySQL Database
    ↓
🖥️ Plateforme CSAR Web
```

## 🔧 **Installation et Configuration**

### **Backend Laravel**
```bash
# Migration des tables
php artisan migrate --path=database/migrations/2026_03_13_120000_create_sim_mobile_collection_tables.php

# Création des collecteurs de test
php artisan tinker
>>> DB::table('sim_collectors')->insert([...]);

# Test API
curl -X POST http://localhost:8000/api/mobile/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"mamadou.diallo@sim.sn","password":"password123"}'
```

### **Collecteurs de Test**
- **Email**: mamadou.diallo@sim.sn | **Zones**: Dakar, Thiès
- **Email**: awa.sene@sim.sn | **Zones**: Kaolack, Fatick  
- **Email**: ibrahim.ba@sim.sn | **Zones**: Saint-Louis, Louga
- **Mot de passe**: password123

## 🎯 **Prochaines Étapes**

1. **Développement App React Native** (1-2 semaines)
2. **Tests et Validation** (3-5 jours)
3. **Déploiement Play Store** (3-5 jours)
4. **Formation Collecteurs** (2-3 jours)

## 📈 **Avantages**

✅ **Collecte en temps réel**  
✅ **Mode hors-ligne performant**  
✅ **GPS intégré**  
✅ **Photos des marchés**  
✅ **Synchronisation automatique**  
✅ **Interface simplifiée pour collecteurs**  
✅ **Traçabilité complète des données**  
✅ **Compatible téléphone/tablette**  

---

**L'application mobile sera disponible sur Play Store sous le nom "CSAR SIM Collect" et sera uniquement accessible aux collecteurs autorisés par le CSAR.**
