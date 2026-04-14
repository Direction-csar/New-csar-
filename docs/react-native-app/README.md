# CSAR SIM Collect — Application Mobile React Native

Application de collecte de données SIM (Système d'Information sur les Marchés) pour le terrain.

## Structure

```
docs/react-native-app/
├── App.js                    # Navigation principale (Stack + BottomTabs)
├── index.js                  # Point d'entrée AppRegistry
├── package.json
├── screens/
│   ├── LoginScreen.js        # Authentification collecteur
│   ├── HomeScreen.js         # Dashboard stats temps réel
│   ├── CollectScreen.js      # Formulaire de collecte de prix
│   ├── MapScreen.js          # Carte GPS + statut agent
│   └── SyncScreen.js         # Synchronisation + historique
└── services/
    ├── api.js                # Appels API Laravel (axios)
    ├── storage.js            # AsyncStorage (cache + offline)
    └── location.js           # GPS + géocodage inverse
```

## Installation

```bash
npm install
# Android
npm run android
# iOS
npm run ios
```

## Configuration API

Dans `services/api.js`, modifier `BASE_URL` :

```js
const BASE_URL = 'https://www.csar.sn/api/mobile/v1';
```

Pour les tests locaux XAMPP :
```js
const BASE_URL = 'http://192.168.1.X/csar/public/api/mobile/v1';
```

## Endpoints utilisés

| Screen | Endpoint |
|--------|----------|
| Login | `POST /login` |
| HomeScreen | `GET /stats` |
| CollectScreen | `GET /markets`, `GET /products`, `POST /collections`, `POST /location` |
| MapScreen | `GET /markets`, `POST /location` |
| SyncScreen | `POST /sync`, `GET /sync/history` |

## Build APK (Android)

```bash
cd android
./gradlew assembleRelease
# APK généré : android/app/build/outputs/apk/release/app-release.apk
```

## Fonctionnalités

- ✅ Authentification par token Sanctum
- ✅ Collecte de prix (3 niveaux : producteur, détail, ½ gros)
- ✅ Géolocalisation automatique à chaque collecte
- ✅ Sauvegarde hors-ligne (AsyncStorage) + sync automatique
- ✅ Suivi temps réel sur carte (WebView Leaflet)
- ✅ Statuts agents : actif / en collecte / en pause
- ✅ Historique des synchronisations
