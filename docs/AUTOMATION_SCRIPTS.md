# 📱 Scripts d'Automatisation - Déploiement CSAR SIM Collect

## 🚀 **Script d'Installation Automatisée**

### **📦 install-environment.sh (macOS/Linux)**
```bash
#!/bin/bash

echo "🚀 Installation de l'environnement React Native pour CSAR SIM Collect"

# Vérifier Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé. Installation..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
else
    echo "✅ Node.js est déjà installé: $(node --version)"
fi

# Installer React Native CLI
echo "📦 Installation de React Native CLI..."
npm install -g @react-native-community/cli

# Vérifier Android Studio
if [ -d "$HOME/Library/Android/sdk" ] || [ -d "$HOME/Android/Sdk" ]; then
    echo "✅ Android SDK est détecté"
else
    echo "⚠️  Veuillez installer Android Studio manuellement"
fi

# Créer le projet
echo "📱 Création du projet CSARSimCollect..."
npx react-native init CSARSimCollect --template react-native-template-typescript

cd CSARSimCollect

# Installer les dépendances
echo "📦 Installation des dépendances..."
npm install @react-navigation/native @react-navigation/stack @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context
npm install axios @react-native-async-storage/async-storage
npm install @react-native-community/netinfo react-native-device-info
npm install react-native-geolocation-service react-native-permissions
npm install react-native-image-picker react-native-camera react-native-fs
npm install react-native-maps react-native-vector-icons
npm install react-native-paper react-native-splash-screen
npm install react-native-reanimated react-native-gesture-handler

# Configuration iOS
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "🍎 Configuration iOS..."
    cd ios && pod install && cd ..
fi

# Créer la structure
echo "📂 Création de la structure..."
mkdir -p src/{screens,services,components,utils,assets,types,theme}
mkdir -p __tests__/{screens,services,components}

echo "✅ Installation terminée!"
echo "🚀 Lancez 'npm start' puis 'npm run android' pour démarrer"
```

### **📦 install-environment.bat (Windows)**
```batch
@echo off
echo 🚀 Installation de l'environnement React Native pour CSAR SIM Collect

REM Vérifier Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js n'est pas installé. Veuillez le télécharger depuis https://nodejs.org
    pause
    exit /b 1
)

echo ✅ Node.js est installé

REM Installer React Native CLI
echo 📦 Installation de React Native CLI...
npm install -g @react-native-community/cli

REM Créer le projet
echo 📱 Création du projet CSARSimCollect...
npx react-native init CSARSimCollect --template react-native-template-typescript

cd CSARSimCollect

REM Installer les dépendances
echo 📦 Installation des dépendances...
npm install @react-navigation/native @react-navigation/stack @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context
npm install axios @react-native-async-storage/async-storage
npm install @react-native-community/netinfo react-native-device-info
npm install react-native-geolocation-service react-native-permissions
npm install react-native-image-picker react-native-camera react-native-fs
npm install react-native-maps react-native-vector-icons
npm install react-native-paper react-native-splash-screen
npm install react-native-reanimated react-native-gesture-handler

REM Créer la structure
echo 📂 Création de la structure...
mkdir src\screens
mkdir src\services
mkdir src\components
mkdir src\utils
mkdir src\assets
mkdir src\types
mkdir src\theme
mkdir __tests__\screens
mkdir __tests__\services
mkdir __tests__\components

echo ✅ Installation terminée!
echo 🚀 Lancez 'npm start' puis 'npm run android' pour démarrer
pause
```

---

## 🧪 **Scripts de Tests Automatisés**

### **📋 run-tests.sh**
```bash
#!/bin/bash

echo "🧪 Exécution des tests pour CSAR SIM Collect"

# Nettoyer le cache
echo "🧹 Nettoyage du cache..."
npm start -- --reset-cache > /dev/null 2>&1 &
METRO_PID=$!
sleep 5
kill $METRO_PID 2>/dev/null

# Tests unitaires
echo "📝 Tests unitaires..."
npm test -- --coverage --watchAll=false

# Tests E2E (si Detox est configuré)
if [ -f ".detoxrc.json" ]; then
    echo "🔄 Tests E2E..."
    detox build --configuration android.emu.release
    detox test --configuration android.emu.release
else
    echo "⚠️  Detox n'est pas configuré. Installation..."
    npm install --save-dev detox
    detox init
fi

# Linting
echo "🔍 Analyse du code..."
npm run lint

echo "✅ Tests terminés!"
```

### **📋 run-tests.bat**
```batch
@echo off
echo 🧪 Exécution des tests pour CSAR SIM Collect

REM Tests unitaires
echo 📝 Tests unitaires...
npm test -- --coverage --watchAll=false

REM Linting
echo 🔍 Analyse du code...
npm run lint

echo ✅ Tests terminés!
pause
```

---

## 📦 **Scripts de Build et Déploiement**

### **🔐 generate-keystore.sh**
```bash
#!/bin/bash

echo "🔐 Génération du keystore pour CSAR SIM Collect"

KEYSTORE_NAME="csar-release"
ALIAS="csar"
VALIDITY_DAYS=10000

# Demander les informations
read -p "Nom complet (CN): " CN
read -p "Organisation (O): " O
read -p "Unité (OU): " OU
read -p "Ville (L): " L
read -p "Région (ST): " ST
read -p "Pays (C): " C

# Générer le keystore
keytool -genkey -v -keystore ${KEYSTORE_NAME}.keystore \
  -alias ${ALIAS} \
  -keyalg RSA \
  -keysize 2048 \
  -validity ${VALIDITY_DAYS} \
  -dname "CN=${CN}, OU=${OU}, O=${O}, L=${L}, ST=${ST}, C=${C}"

echo "✅ Keystore généré: ${KEYSTORE_NAME}.keystore"
echo "🔐 Déplacez ce fichier dans android/app/"
```

### **📦 build-release.sh**
```bash
#!/bin/bash

echo "📦 Build de production pour CSAR SIM Collect"

# Vérifier le keystore
if [ ! -f "android/app/csar-release.keystore" ]; then
    echo "❌ Keystore non trouvé. Générez-le d'abord avec ./generate-keystore.sh"
    exit 1
fi

# Nettoyer le projet
echo "🧹 Nettoyage du projet..."
cd android && ./gradlew clean && cd ..

# Build APK
echo "📱 Build APK Release..."
cd android && ./gradlew assembleRelease && cd ..

# Build AAB
echo "📦 Build AAB (Play Store)..."
cd android && ./gradlew bundleRelease && cd ..

echo "✅ Build terminé!"
echo "📱 APK: android/app/build/outputs/apk/release/app-release.apk"
echo "📦 AAB: android/app/build/outputs/bundle/release/app-release.aab"
```

### **📦 build-release.bat**
```batch
@echo off
echo 📦 Build de production pour CSAR SIM Collect

REM Vérifier le keystore
if not exist "android\app\csar-release.keystore" (
    echo ❌ Keystore non trouvé. Générez-le d'abord.
    pause
    exit /b 1
)

REM Nettoyer le projet
echo 🧹 Nettoyage du projet...
cd android && gradlew clean && cd ..

REM Build APK
echo 📱 Build APK Release...
cd android && gradlew assembleRelease && cd ..

REM Build AAB
echo 📦 Build AAB (Play Store)...
cd android && gradlew bundleRelease && cd ..

echo ✅ Build terminé!
echo 📱 APK: android\app\build\outputs\apk\release\app-release.apk
echo 📦 AAB: android\app\build\outputs\bundle\release\app-release.aab
pause
```

---

## 📱 **Scripts de Déploiement**

### **📤 deploy-play-store.sh**
```bash
#!/bin/bash

echo "📤 Déploiement sur Play Store"

# Vérifier le build AAB
if [ ! -f "android/app/build/outputs/bundle/release/app-release.aab" ]; then
    echo "❌ Build AAB non trouvé. Lancez d'abord ./build-release.sh"
    exit 1
fi

# Upload avec Google Play CLI (si disponible)
if command -v fastlane &> /dev/null; then
    echo "🚀 Upload avec Fastlane..."
    cd android && fastlane deploy && cd ..
else
    echo "⚠️  Fastlane n'est pas installé. Upload manuel requis:"
    echo "1. Allez sur https://play.google.com/console"
    echo "2. Sélectionnez l'application CSAR SIM Collect"
    echo "3. Allez dans 'Créer une nouvelle version'"
    echo "4. Uploadez le fichier: android/app/build/outputs/bundle/release/app-release.aab"
fi

echo "✅ Prêt pour le déploiement!"
```

---

## 🧪 **Scripts de Tests Terrain**

### **📱 install-test-devices.sh**
```bash
#!/bin/bash

echo "📱 Installation sur les appareils de test"

# Vérifier les appareils connectés
echo "🔍 Recherche des appareils Android..."
adb devices

# Build de test
echo "📦 Build de test..."
cd android && ./gradlew assembleDebug && cd ..

# Installation sur les appareils
echo "📱 Installation sur les appareils..."
for device in $(adb devices | grep -v "List" | cut -f1); do
    echo "📲 Installation sur l'appareil: $device"
    adb -s $device install -r android/app/build/outputs/apk/debug/app-debug.apk
done

echo "✅ Installation terminée!"
```

---

## 📊 **Scripts de Monitoring**

### **📈 monitor-performance.sh**
```bash
#!/bin/bash

echo "📈 Monitoring de performance pour CSAR SIM Collect"

# Vérifier l'API
echo "🌐 Test de l'API backend..."
curl -f http://localhost:8000/api/mobile/v1/health || echo "❌ API non disponible"

# Vérifier Metro
echo "📦 Test de Metro Bundler..."
curl -f http://localhost:8081/status || echo "❌ Metro non démarré"

# Logs de l'application
echo "📋 Logs de l'application..."
adb logcat | grep "CSARSimCollect"

echo "📊 Monitoring en cours..."
```

---

## 🔄 **Script de Nettoyage Complet**

### **🧹 clean-project.sh**
```bash
#!/bin/bash

echo "🧹 Nettoyage complet du projet CSAR SIM Collect"

# Arrêter tous les processus
echo "⏹️  Arrêt des processus..."
pkill -f "react-native" || true
pkill -f "metro" || true

# Nettoyer les caches
echo "🧹 Nettoyage des caches..."
npm start -- --reset-cache > /dev/null 2>&1 &
METRO_PID=$!
sleep 3
kill $METRO_PID 2>/dev/null

rm -rf node_modules/.cache
rm -rf .expo
rm -rf .gradle

# Nettoyer Android
echo "🤖 Nettoyage Android..."
cd android && ./gradlew clean && cd ..
rm -rf android/app/build

# Nettoyer iOS
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "🍎 Nettoyage iOS..."
    cd ios && rm -rf Pods && rm Podfile.lock && pod install && cd ..
fi

# Réinstaller les dépendances
echo "📦 Réinstallation des dépendances..."
npm install

echo "✅ Nettoyage terminé!"
echo "🚀 Relancez avec 'npm start' et 'npm run android'"
```

---

## 📋 **Script de Validation Finale**

### **✅ validate-deployment.sh**
```bash
#!/bin/bash

echo "✅ Validation finale du déploiement CSAR SIM Collect"

# Checklist de validation
echo "📋 Checklist de validation:"

# Vérifier les builds
if [ -f "android/app/build/outputs/apk/release/app-release.apk" ]; then
    echo "✅ APK Release généré"
else
    echo "❌ APK Release manquant"
fi

if [ -f "android/app/build/outputs/bundle/release/app-release.aab" ]; then
    echo "✅ AAB Release généré"
else
    echo "❌ AAB Release manquant"
fi

# Vérifier les tests
if npm test -- --watchAll=false > /dev/null 2>&1; then
    echo "✅ Tests unitaires passés"
else
    echo "❌ Tests unitaires échoués"
fi

# Vérifier le linting
if npm run lint > /dev/null 2>&1; then
    echo "✅ Linting réussi"
else
    echo "❌ Linting échoué"
fi

# Vérifier la documentation
if [ -f "docs/INSTALLATION_GUIDE.md" ]; then
    echo "✅ Documentation présente"
else
    echo "❌ Documentation manquante"
fi

echo "🎉 Validation terminée!"
```

---

## 🚀 **Comment Utiliser les Scripts**

### **📋 Étape 1 : Installation**
```bash
# Rendre les scripts exécutables (macOS/Linux)
chmod +x *.sh

# Installation de l'environnement
./install-environment.sh  # macOS/Linux
install-environment.bat    # Windows
```

### **📋 Étape 2 : Développement**
```bash
# Lancer les tests
./run-tests.sh

# Nettoyer le projet
./clean-project.sh
```

### **📋 Étape 3 : Build et Déploiement**
```bash
# Générer le keystore
./generate-keystore.sh

# Build de production
./build-release.sh

# Déploiement Play Store
./deploy-play-store.sh
```

### **📋 Étape 4 : Tests Terrain**
```bash
# Installation sur appareils de test
./install-test-devices.sh
```

---

**🎉 Tous les scripts sont prêts ! Vous pouvez maintenant automatiser l'ensemble du processus de développement et de déploiement.**
