# CSAR Mobile — Application de Collecte

Application Flutter pour la collecte de données sur les marchés (SIM).

## Prérequis

- [Flutter SDK](https://flutter.dev/docs/get-started/install) >= 3.0.0
- Android Studio ou VS Code avec extension Flutter
- Un émulateur Android ou un téléphone physique

## Installation

```bash
cd mobile-app
flutter pub get
flutter run
```

## Identifiants de test

| Champ | Valeur |
|-------|--------|
| Email | collecteur@csar.sn |
| Mot de passe | password |

## Structure du projet

```
lib/
├── main.dart                    # Point d'entrée
├── services/
│   ├── api_service.dart         # Appels HTTP vers l'API
│   └── auth_service.dart        # Gestion du token / authentification
└── screens/
    ├── login_screen.dart        # Écran de connexion
    ├── home_screen.dart         # Tableau de bord
    ├── markets_screen.dart      # Liste des marchés
    ├── collection_screen.dart   # Formulaire de collecte
    └── history_screen.dart      # Historique des collectes
```

## URL de l'API

`https://csar.sn/mobile/v1`

## Compilation APK (Android)

```bash
flutter build apk --release
```

Le fichier APK sera généré dans `build/app/outputs/flutter-apk/app-release.apk`
