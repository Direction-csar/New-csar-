# Signature release des APK CSAR

Chaque application (`distribution-app`, `scanner-app`, `mobile/csar_magasinier`,
`mobile/csar_comptable`) lit `android/key.properties` à la racine du dossier
`android/` de l'app. Ce fichier contient les secrets du keystore et est
**gitignoré** (`**/android/key.properties`, `*.jks`, `*.keystore`).

## Fichier android/key.properties (à créer localement)

```properties
storePassword=<mot de passe du keystore>
keyPassword=<mot de passe de la clé>
keyAlias=csar-release
storeFile=../../csar-release.jks
```

`storeFile` est résolu depuis `android/app/` : `../../csar-release.jks` place le
keystore dans `<app>/android/csar-release.jks` (également gitignoré).

## Génération du keystore (une seule fois, conservé précieusement)

```powershell
keytool -genkey -v -keystore <app>\android\csar-release.jks `
  -keyalg RSA -keysize 2048 -validity 10000 -alias csar-release
```

Le même keystore peut être partagé entre les 4 apps en pointant `storeFile`
vers un emplacement commun. **Perdre ce keystore empêche toute mise à jour
des applications déjà installées.**

## Comportement de build

- `key.properties` présent → signature release.
- Absent → repli automatique sur la clé debug (builds de développement).

## Vérification de signature

```powershell
& "$env:ANDROID_HOME\build-tools\<version>\apksigner.bat" verify --print-certs app-release.apk
```

Un APK release affiche le DN du keystore CSAR ; un build debug affiche
`CN=Android Debug`.
