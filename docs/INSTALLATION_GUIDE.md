# 📱 Guide d'Installation - Environnement React Native

## 🚀 **Étape 1 : Vérification des Prérequis**

### **💻 Vérifier Node.js**
```bash
# Ouvrir un terminal et vérifier
node --version
npm --version

# Versions requises :
# Node.js : 16.x ou supérieur
# npm : 8.x ou supérieur

# Si Node.js n'est pas installé :
# Windows : Télécharger depuis https://nodejs.org
# macOS : brew install node
# Linux (Ubuntu/Debian) :
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### **🔧 Installation React Native CLI**
```bash
# Installer globalement
npm install -g @react-native-community/cli

# Vérifier l'installation
npx react-native --version
```

---

## 🤖 **Étape 2 : Configuration Android**

### **📦 Installation Android Studio**
```bash
# 1. Télécharger Android Studio
# https://developer.android.com/studio

# 2. Installer les composants requis :
# - Android SDK
# - Android SDK Platform-Tools
# - Performance (Intel ® HAXM installer)
# - Android Virtual Device

# 3. Configurer les variables d'environnement (Windows)
# Dans les variables d'environnement système :
# ANDROID_HOME = C:\Users\VOTRE_NOM\AppData\Local\Android\Sdk
# Ajouter à PATH : %ANDROID_HOME%\platform-tools
# Ajouter à PATH : %ANDROID_HOME%\tools
# Ajouter à PATH : %ANDROID_HOME%\tools\bin
```

### **📱 Configuration SDK Android**
```bash
# Ouvrir Android Studio
# Tools > SDK Manager
# Installer :
# - Android 13 (API level 33)
# - Android 12 (API level 32)
# - Android SDK Build-Tools 33.0.0
# - Android SDK Platform-Tools
# - Android SDK Tools
```

### **🖥️ Créer un Émulateur**
```bash
# Dans Android Studio :
# Tools > AVD Manager
# Create Virtual Device
# Choisir : Pixel 4 ou Pixel 6
# System Image : Android 13 (API 33)
# Nom : CSAR_Emulator
# Démarrer l'émulateur
```

---

## 🍎 **Étape 3 : Configuration iOS (macOS uniquement)**

### **📦 Installation Xcode**
```bash
# Installer Xcode depuis Mac App Store
# Version 14.0 ou supérieure

# Installer les Command Line Tools
xcode-select --install

# Accepter la licence Xcode
sudo xcodebuild -license
```

### **🔧 Configuration CocoaPods**
```bash
# Installer CocoaPods
sudo gem install cocoapods

# Mettre à jour les pods
pod setup
```

---

## 📱 **Étape 4 : Création du Projet**

### **🚀 Initialisation du Projet**
```bash
# Créer le projet React Native
npx react-native init CSARSimCollect --template react-native-template-typescript

# Entrer dans le projet
cd CSARSimCollect

# Vérifier la structure
ls -la
```

### **📦 Installation des Dépendances**
```bash
# Navigation
npm install @react-navigation/native @react-navigation/stack @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context

# API et Stockage
npm install axios @react-native-async-storage/async-storage
npm install @react-native-community/netinfo react-native-device-info

# GPS et Permissions
npm install react-native-geolocation-service react-native-permissions

# Camera et Images
npm install react-native-image-picker react-native-camera react-native-fs

# Cartes et Localisation
npm install react-native-maps react-native-vector-icons

# UI et Design
npm install react-native-paper react-native-splash-screen
npm install react-native-reanimated react-native-gesture-handler

# Firebase (optionnel pour monitoring)
npm install @react-native-firebase/app @react-native-firebase/analytics
npm install @react-native-firebase/crashlytics

# Développement et Tests
npm install --save-dev @types/react @types/react-native
npm install --save-dev jest @testing-library/react-native
npm install --save-dev detox
```

---

## ⚙️ **Étape 5 : Configuration du Projet**

### **📂 Structure des Dossiers**
```bash
# Créer la structure
mkdir -p src/{screens,services,components,utils,assets,types,theme}
mkdir -p src/screens/{auth,home,collection,sync,profile}
mkdir -p src/services/{api,storage,location,offline}
mkdir -p src/components/{forms,ui,common}
mkdir -p __tests__/{screens,services,components}

# Copier les fichiers existants
cp -r /path/to/docs/react-native-app/* src/
```

### **🔧 Configuration TypeScript**
```bash
# Créer tsconfig.json si nécessaire
npx tsc --init

# Configurer les types
mkdir -p src/types
touch src/types/index.ts
touch src/types/navigation.ts
touch src/types/api.ts
touch src/types/collection.ts
```

### **🎨 Configuration du Thème**
```bash
# Créer les fichiers de thème
touch src/theme/colors.ts
touch src/theme/typography.ts
touch src/theme/index.ts
```

---

## 🍎 **Étape 6 : Configuration iOS**

### **📦 Installation Pods**
```bash
cd ios
pod install
cd ..
```

### **🔧 Configuration Info.plist**
```xml
<!-- Ajouter dans ios/CSARSimCollect/Info.plist -->
<key>NSLocationWhenInUseUsageDescription</key>
<string>CSAR SIM a besoin de votre localisation pour géolocaliser les collectes</string>
<key>NSLocationAlwaysAndWhenInUseUsageDescription</key>
<string>CSAR SIM a besoin d'accéder à votre localisation en arrière-plan</string>
<key>NSCameraUsageDescription</key>
<string>CSAR SIM a besoin d'accéder à la caméra pour photographier les marchés</string>
<key>NSPhotoLibraryUsageDescription</key>
<string>CSAR SIM a besoin d'accéder à vos photos pour les collectes</string>
```

---

## 🤖 **Étape 7 : Configuration Android**

### **🔧 Configuration Permissions**
```xml
<!-- Ajouter dans android/app/src/main/AndroidManifest.xml -->
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.READ_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

### **🔧 Configuration Gradle**
```gradle
// android/app/build.gradle
android {
    compileSdkVersion rootProject.ext.compileSdkVersion
    compileOptions {
        sourceCompatibility JavaVersion.VERSION_11
        targetCompatibility JavaVersion.VERSION_11
    }
    // ... autres configurations
}
```

---

## 🚀 **Étape 8 : Premier Lancement**

### **📱 Lancer Metro Bundler**
```bash
# Dans le terminal du projet
npm start
# ou
npx react-native start
```

### **📱 Lancer sur Android**
```bash
# Dans un autre terminal
npm run android
# ou
npx react-native run-android
```

### **📱 Lancer sur iOS**
```bash
# Dans un autre terminal (macOS uniquement)
npm run ios
# ou
npx react-native run-ios
```

---

## 🧪 **Étape 9 : Tests Initiaux**

### **🔧 Vérifier l'Installation**
```bash
# Vérifier que tout fonctionne
npx react-native doctor

# Résultats attendus :
# ✅ Node.js
# ✅ React Native CLI
# ✅ Android SDK
# ✅ Android Studio
# ✅ ADB (Android Debug Bridge)
# ✅ Émulateur Android
```

### **📱 Test de l'Application**
```bash
# L'application devrait démarrer et afficher :
# - Écran de bienvenue React Native
# - Navigation entre écrans
# - Accès aux fonctionnalités de base
```

---

## 🔧 **Étape 10 : Configuration des Scripts**

### **📦 Scripts package.json**
```json
{
  "scripts": {
    "android": "react-native run-android",
    "ios": "react-native run-ios",
    "start": "react-native start",
    "test": "jest",
    "lint": "eslint . --ext .js,.jsx,.ts,.tsx",
    "build:android": "cd android && ./gradlew assembleRelease",
    "build:ios": "cd ios && xcodebuild -workspace CSARSimCollect.xcworkspace -scheme CSARSimCollect -configuration Release",
    "clean": "react-native clean",
    "clean:android": "cd android && ./gradlew clean",
    "clean:ios": "cd ios && rm -rf Pods && pod install"
  }
}
```

---

## 🚨 **Dépannage Commun**

### **🔧 Problèmes Fréquents**
```bash
# 1. Metro ne démarre pas
# Solution : npm start -- --reset-cache

# 2. Erreur de build Android
# Solution : cd android && ./gradlew clean && cd .. && npm run android

# 3. Erreur iOS
# Solution : cd ios && rm -rf Pods && pod install && cd .. && npm run ios

# 4. Erreur de permissions
# Solution : Vérifier les permissions dans AndroidManifest.xml et Info.plist

# 5. Émulateur ne démarre pas
# Solution : Redémarrer Android Studio et recréer l'AVD
```

### **📞 Support**
```bash
# Documentation officielle React Native
# https://reactnative.dev/docs/getting-started

# Communauté React Native
# https://github.com/react-native-community

# Stack Overflow
# https://stackoverflow.com/questions/tagged/react-native
```

---

## ✅ **Checklist d'Installation**

- [ ] Node.js 16+ installé
- [ ] React Native CLI installé
- [ ] Android Studio configuré
- [ ] SDK Android installé
- [ ] Émulateur Android fonctionnel
- [ ] (macOS) Xcode installé
- [ ] (macOS) CocoaPods installé
- [ ] Projet React Native créé
- [ ] Dépendances installées
- [ ] Structure des dossiers créée
- [ ] Configuration TypeScript
- [ ] Permissions configurées
- [ ] Application lance correctement
- [ ] Tests initiaux réussis

---

**🎉 Installation terminée ! Vous êtes prêt à commencer le développement de l'application CSAR SIM Collect !**
