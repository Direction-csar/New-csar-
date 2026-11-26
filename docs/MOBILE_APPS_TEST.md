# Applications mobiles — connexion aux environnements et comptes de test

Guide pour connecter les APK (collecte SIM, Comptable des matières) au
serveur local de développement, aux tests et à la production, et pour
créer des comptes de test sécurisés. **Aucun identifiant n'est inscrit
dans ce document.**

## 1. Présentation

| Application | Projet | API | Compte requis |
|---|---|---|---|
| Collecte SIM (prix sur marchés) | `mobile-app/` (Flutter) | `/mobile/v1` | Collecteur SIM (`sim_collectors`, statut `active`) |
| Comptable des matières | `mobile/csar_comptable/` (Flutter) | `/api/comptable/v1` | Utilisateur `role = comptable`, `is_active = 1` |
| Distribution (bons-matière) | `mobile-app/` (même projet) | `/mobile/v1/distribution` | Jeton Sanctum |

## 2. Configurer l'URL du serveur par environnement

L'URL n'est plus codée en dur vers `https://www.csar.sn`. Elle est
centrée sur `--dart-define=API_HOST=...` (voir
`mobile-app/lib/config/app_config.dart` et
`mobile/csar_comptable/lib/config/app_config.dart`).

| Cible | Valeur `API_HOST` | Remarque |
|---|---|---|
| **Production** (défaut) | `https://www.csar.sn` | Aucun flag requis : valeur par défaut. |
| **Téléphone physique (dev)** | `http://<IP-LAN-PC>:8000` | IP LAN du PC sur le même réseau Wi-Fi, ex. `http://192.168.2.109:8000`. Ne jamais utiliser `localhost`. |
| **Émulateur Android (dev)** | `http://10.0.2.2:8000` | `10.0.2.2` = localhost du PC hôte vu par l'émulateur. |
| **Preprod** | `https://preprod.csar.sn` | HTTPS obligatoire. |

```bash
# Téléphone physique (même réseau que le PC)
flutter run --dart-define=API_HOST=http://192.168.2.109:8000

# Émulateur
flutter run --dart-define=API_HOST=http://10.0.2.2:8000

# Production (par défaut, pas de flag)
flutter build apk --release
```

### HTTP clair en debug

Les APK parlent en `http://` vers le serveur local. Android bloque le
trafic clair : les manifestes `android/app/src/debug/AndroidManifest.xml`
des deux apps déclarent `android:usesCleartextTraffic="true"`.
**Uniquement la variante debug** — les APK de production n'acceptent que
le HTTPS. Ne pas le déplacer dans `src/main`.

### Serveur local

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

L'écoute `0.0.0.0` est indispensable pour un téléphone sur le LAN.
Autoriser le pare-feu Windows le cas échéant.

## 3. Comptes de test locaux

```bash
php artisan mobile:test-accounts
```

- Interdit en production (`APP_ENV=production` → échec).
- Crée/réinitialise un collecteur SIM et un compte comptable de test,
  **mots de passe aléatoires régénérés à chaque exécution**.
- Révoque les anciens jetons de ces comptes.
- Garantit un marché assigné et un marché non assigné pour tester le
  cloisonnement des zones.
- Écrit les identifiants dans `%USERPROFILE%\.csar\mobile-test-accounts.json`
  (hors du dépôt, permissions restreintes) — jamais dans le code ni les logs.

Options : `--sim-email=`, `--comptable-email=`, `--output=`.

Ne jamais intégrer de mot de passe de test dans le code source ou un APK
distribué.

## 4. Rôles et permissions

### Collecteur SIM (`/mobile/v1`)

- `POST /login` → jeton Sanctum (capacité `mobile-collect`).
- Les endpoints exigent un jeton de `SimCollector` **actif**
  (middleware `sim.collector`). Un jeton de personnel (`users`) est refusé.
- `GET /markets` : uniquement les marchés dont l'ID figure dans
  `assigned_zones`. Aucun marché assigné → 403.
- `POST /collections` : 403 si le `market_id` n'est pas assigné.
- `GET /collections`, `/sync`, `/sync/history` : données du collecteur
  uniquement.
- `POST /logout` : révoque les jetons.
- Un collecteur suspendu perd immédiatement l'accès (même avec un jeton
  valide).

### Comptable des matières (`/api/comptable/v1`)

- `POST /login` → jeton Sanctum (capacité `comptable`), refuse un rôle
  autre que `comptable` ou un compte inactif (403).
- Chaque requête protégée revérifie `role = comptable` et `is_active`
  (`denyNonComptable`). Un jeton de collecteur SIM est refusé.
- Validation : `draft → validated` (gel). Rejet : motif ≥ 10 caractères
  obligatoire. Un mouvement validé ne peut être rejeté ni revalidé.

### Administration des comptes

- Collecteur : admin → `/admin/sim/collectors` (créer, modifier
  mot de passe/zones, supprimer).
- Comptable : admin → Utilisateurs, rôle « Comptable des matières » ;
  reset via `POST /admin/users/{user}/reset-password`.

## 5. Vérification rapide

```powershell
# Test HTTP réel (lit les secrets depuis ~/.csar/, ne les affiche pas)
powershell -File scripts\test-mobile-api.ps1 -BaseUrl http://127.0.0.1:8000

# Tests automatisés
php artisan test --filter="SimMobileApiTest|ComptableApiRealSchemaTest|ComptableApiTest|ComptableEquipmentApiTest"
```

## 6. Sécurité

- Les anciens comptes de test publics (`collecteur@csar.sn`,
  `mamadou.diallo@sim.sn`, etc.) sont des comptes de démonstration aux
  mots de passe connus : **ne jamais les utiliser en production**.
- Ne jamais committer le fichier d'identifiants ni d'APK de debug
  acceptant le HTTP clair en production.
