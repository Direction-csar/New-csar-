# 📱 Application Mobile CSAR SIM Collect - Guide de Déploiement

## 🎯 **Vue d'ensemble**

Application mobile React Native complète pour la collecte de données SIM du CSAR, prête pour le déploiement sur Play Store.

## 🏗️ **Architecture de l'Application**

```
📱 src/
├── screens/              # Écrans principaux
│   ├── LoginScreen.js    # Connexion collecteur
│   ├── HomeScreen.js     # Tableau de bord
│   ├── CollectionForm.js # Formulaire de collecte
│   ├── PendingCollections.js
│   ├── SyncHistory.js
│   └── ProfileScreen.js
├── services/             # Services backend
│   ├── api.js            # Communication API
│   ├── storage.js        # Stockage local
│   ├── location.js       # GPS et géolocalisation
│   └── offline.js        # Mode hors-ligne
├── components/           # Composants réutilisables
│   ├── FormInput.js
│   ├── PhotoCapture.js
│   ├── MarketSelector.js
│   └── ProductSelector.js
├── utils/               # Utilitaires
│   ├── validation.js
│   ├── constants.js
│   └── helpers.js
└── assets/              # Images et ressources
```

## 🚀 **Installation et Développement**

### **Prérequis**
```bash
# Node.js 16+
node --version

# React Native CLI
npm install -g @react-native-community/cli

# Android Studio (pour Android)
# Xcode (pour iOS)
```

### **Installation**
```bash
# Cloner le projet
git clone https://github.com/csar-senegal/sim-collect-mobile.git
cd sim-collect-mobile

# Installer les dépendances
npm install

# Pour iOS
cd ios && pod install && cd ..

# Démarrer Metro
npm start

# Lancer sur Android
npm run android

# Lancer sur iOS
npm run ios
```

## 🔧 **Configuration**

### **Variables d'environnement**
```javascript
// src/config/api.js
const API_CONFIG = {
  BASE_URL: __DEV__ 
    ? 'http://localhost:8000/api/mobile/v1'  // Développement
    : 'https://api.csar.sn/mobile/v1',      // Production
  TIMEOUT: 15000,
  RETRY_ATTEMPTS: 3
};
```

### **Configuration Android**
```xml
<!-- android/app/src/main/AndroidManifest.xml -->
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
```

### **Configuration iOS**
```xml
<!-- ios/CSARSimCollect/Info.plist -->
<key>NSLocationWhenInUseUsageDescription</key>
<string>CSAR SIM a besoin de votre localisation pour géolocaliser les collectes</string>
<key>NSCameraUsageDescription</key>
<string>CSAR SIM a besoin d'accéder à la caméra pour photographier les marchés</string>
```

## 📦 **Build de Production**

### **Android**
```bash
# Générer la clé de signature
keytool -genkey -v -keystore csar-release.keystore -alias csar -keyalg RSA -keysize 2048 -validity 10000

# Configurer gradle.properties
MYAPP_RELEASE_STORE_FILE=csar-release.keystore
MYAPP_RELEASE_KEY_ALIAS=csar
MYAPP_RELEASE_STORE_PASSWORD=your_store_password
MYAPP_RELEASE_KEY_PASSWORD=your_key_password

# Build APK
npm run build:android

# Build AAB (recommandé pour Play Store)
cd android && ./gradlew bundleRelease
```

### **iOS**
```bash
# Build avec Xcode
npm run build:ios

# Ou via ligne de commande
xcodebuild -workspace CSARSimCollect.xcworkspace \
  -scheme CSARSimCollect \
  -configuration Release \
  -destination generic/platform=iOS \
  -archivePath CSARSimCollect.xcarchive archive
```

## 🎨 **Personnalisation**

### **Thème CSAR**
```javascript
// src/theme/colors.js
export const COLORS = {
  primary: '#27ae60',      // Vert CSAR
  secondary: '#3498db',    // Bleu
  success: '#2ecc71',
  warning: '#f39c12',
  error: '#e74c3c',
  background: '#f5f5f5',
  text: '#2c3e50',
  textLight: '#7f8c8d'
};
```

### **Icône et Splash Screen**
```bash
# Générer les icônes
npx react-native-vector-icons

# Splash screen
npx react-native-splash-screen generate
```

## 🔐 **Sécurité**

### **Protection du code**
```bash
# Obfuscation (ProGuard Android)
cd android && ./gradlew assembleRelease

# Code signing iOS
# Via Xcode: Project > Target > Signing & Capabilities
```

### **Sécurité des données**
```javascript
// Chiffrement des données locales
import CryptoJS from 'crypto-js';

const encryptData = (data, key) => {
  return CryptoJS.AES.encrypt(JSON.stringify(data), key).toString();
};
```

## 📊 **Monitoring et Analytics**

### **Configuration Firebase**
```javascript
// src/services/analytics.js
import analytics from '@react-native-firebase/analytics';

const trackCollection = (market, product, price) => {
  analytics().logEvent('collection_submitted', {
    market_name: market.name,
    product_name: product.name,
    price: price,
    timestamp: new Date().toISOString()
  });
};
```

## 🚀 **Déploiement Play Store**

### **Préparation**
1. **Compte Play Store** : Créer un compte développeur ($25)
2. **Application** : Créer l'application sur la Play Console
3. **Listing** : Préparer les captures d'écran et descriptions

### **Upload**
```bash
# Upload AAB sur Play Console
# Via l'interface web de Google Play Console
```

### **Configuration du listing**
- **Nom** : "CSAR SIM Collect"
- **Description** : "Application officielle de collecte de données SIM pour les collecteurs du CSAR"
- **Catégorie** : Outils
- **Public** : Applications internes (privées)
- **Pays** : Sénégal (et autres pays CSAR)

## 🔄 **Mises à Jour**

### **Processus de mise à jour**
```bash
# Versionner l'application
# 1. Mettre à jour package.json (version)
# 2. Build de production
# 3. Upload sur Play Store
# 4. Déploiement progressif
```

### **Mises à jour OTA (Over-The-Air)**
```javascript
// CodePush pour les mises à jour instantanées
import CodePush from 'react-native-codepush';

CodePush.sync({
  updateDialog: true,
  installMode: CodePush.InstallMode.IMMEDIATE
});
```

## 📈 **Performance**

### **Optimisations**
```javascript
// Lazy loading des écrans
const HomeScreen = React.lazy(() => import('./screens/HomeScreen'));

// Memoization des composants
const CollectionForm = React.memo(({ data }) => {
  return <Form data={data} />;
});
```

## 🐛 **Débogage**

### **Logs et erreurs**
```javascript
// Service de logging
import { Crashlytics } from '@react-native-firebase/crashlytics';

const logError = (error, context) => {
  console.error(error);
  Crashlytics().recordError(error, context);
};
```

## 📱 **Tests**

### **Tests unitaires**
```bash
npm test
```

### **Tests E2E**
```bash
# Detox pour les tests end-to-end
npm install --save-dev detox
```

## 🔗 **Intégration Continue**

### **GitHub Actions**
```yaml
# .github/workflows/build.yml
name: Build and Deploy
on:
  push:
    branches: [main]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '16'
      - name: Install dependencies
        run: npm install
      - name: Build Android
        run: cd android && ./gradlew assembleRelease
```

---

## 📋 **Checklist de Déploiement**

- [ ] Tests complets passés
- [ ] Build de production généré
- [ ] Signature configurée
- [ ] Play Console configurée
- [ ] Captures d'écran prêtes
- [ ] Description et keywords optimisés
- [ ] Politique de privacy rédigée
- [ ] Version de test validée
- [ ] Monitoring configuré

**L'application est prête pour le déploiement sur Play Store !**
