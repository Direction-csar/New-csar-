# CSAR Magasinier - APK Mobile de Gestion de Stock

## Description
Application mobile Flutter pour les magasiniers CSAR. Gestion des entrées, sorties et suivi de stock en temps réel, avec support offline.

## Fonctionnalités
- **Authentification sécurisée** : Token JWT stocké dans le keystore Android
- **Fiche de stock digitale** : Reproduction exacte de la fiche papier (Date, Libellé, Position, Entrée Sacs/KG, Sortie Sacs/KG, Solde, Observations)
- **Mode offline** : SQLite local pour enregistrer les mouvements sans connexion
- **Synchronisation** : Envoi batch des mouvements en attente dès la connexion rétablie
- **Traçabilité** : Référence unique et QR code par mouvement
- **Sécurité** : Validation côté client (pas de sortie > solde), journal d'audit

## Prérequis
- Flutter SDK >= 3.0.0
- Android Studio ou VS Code avec extensions Flutter
- JDK 17+
- Android SDK (API 21+)

## Installation

```bash
cd mobile/warehouse_keeper
flutter pub get
```

## Compilation APK

```bash
# Mode release (pour production)
flutter build apk --release

# L'APK sera généré dans :
# build/app/outputs/flutter-apk/app-release.apk
```

## Configuration API
Editer `lib/services/api_service.dart` si besoin :
```dart
static const String baseUrl = 'https://www.csar.sn/api/warehouse/v1';
```

## Identifiants de test
- **Email** : `magasinier@csar.sn`
- **Mot de passe** : `password`

## Endpoints API
| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/warehouse/v1/login` | POST | Authentification |
| `/api/warehouse/v1/warehouses` | GET | Liste des magasins |
| `/api/warehouse/v1/warehouses/{id}/sheet` | GET | Fiche de stock |
| `/api/warehouse/v1/movements` | POST | Créer un mouvement |
| `/api/warehouse/v1/sync` | POST | Synchronisation batch |
| `/api/warehouse/v1/stats` | GET | Statistiques |

## Écrans
1. **Login** : Connexion sécurisée
2. **Dashboard** : Vue d'ensemble, stats rapides, accès magasins
3. **Fiche de Stock** : Tableau style fiche papier avec solde en temps réel
4. **Nouveau Mouvement** : Formulaire Entrée/Sortie/Ajustement avec auto-calc KG
5. **Synchronisation** : Liste des mouvements en attente, bouton sync

## Sécurité
- Token JWT dans FlutterSecureStorage (keystore Android)
- Validation solde avant sortie
- Références uniques par mouvement
- Journal d'audit côté serveur
- SQLite local chiffré
