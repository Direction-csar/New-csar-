# CSAR Distribution — diagnostic du 4 novembre 2026

## Conclusion et niveau de preuve

Des défauts ont été reproduits dans le code Flutter Distribution puis corrigés.
Les 31 tests locaux passent. La cause exacte de l'incident photographié reste
à confirmer avec les logs du téléphone et la réponse de son API : aucun appareil
n'est présent dans `adb devices -l`, et aucune session réelle d'agent n'a été
utilisée. Les captures seules ne permettent pas d'attribuer l'incident à un champ
JSON ou à un statut HTTP précis.

Le dépôt contenait déjà un premier correctif (`a10c9df`) dont les 14 tests
passaient avant cette intervention. Le précédent APK local annonçait
`sn.csar.csar_distribution`, version `1.0.0`, code `1`. L'identité de l'APK
effectivement installé sur le téléphone n'a pas pu être vérifiée.

## Défauts reproduits avant correction

| Déclencheur simulé | Cause dans le code | Résultat avant correction |
| --- | --- | --- |
| Planning avec `distribution_date: ""` | `substring(0, 10)` sans contrôle dans `_PlanningCard.build` | `RangeError` pendant la construction du widget |
| Quota `"NaN"` ou `"Infinity"` | `jsonNum()` accepte une valeur non finie, puis `jsonInt()` appelle `round()` | `UnsupportedError: Infinity or NaN toInt` |
| `data: [null]` dans les plannings | Validation de la liste seulement, puis accès `p['beneficiaires']` dans le tableau | `NoSuchMethodError` pendant la construction |
| HTTP 401 avec corps HTML | `_decode()` perd le statut dans son traitement d'échec JSON | Session conservée à tort |
| HTTP 403 | Assimilation d'un refus de permission à un jeton expiré | Déconnexion à tort |
| Historique ne répondant jamais | Aucun délai maximal sur `getTicketsHistory()` | Indicateur permanent, sans erreur ni reprise |

Les exceptions de construction surviennent **après** le retour de la requête,
lors du rendu Flutter. Le `try/catch` entourant le chargement HTTP ne peut donc
pas les intercepter. Flutter peut présenter un écran gris en release lorsqu'un
widget échoue ; le filet de sécurité déjà présent dans le dépôt n'éliminait pas
les causes dans les données et les widgets.

`artifacts/regressions-before.txt` contient les reproductions : 13 tests passent,
7 échouent avant correction. Il s'agit de tests locaux avec données fictives,
pas de logs Android de l'incident.

## Corrections

- `lib/screens/home_screen.dart` : validation des éléments et relations des
  plannings avant rendu, parsing sûr des dates et libellés, libération de l'état
  de chargement dans `finally`, exclusion des chargements concurrents, contrôle
  du widget et de la session après attente, polling suspendu en arrière-plan,
  actualisation à la reprise et interception des erreurs de connectivité/sync.
- `lib/utils/json_num.dart` : rejet des valeurs numériques non finies.
- `lib/services/api_service.dart` : délai de 25 secondes pour connexion,
  plannings et historique, fermeture du client HTTP à la fin ou au dépassement,
  conservation du statut même sans JSON, messages explicites pour 403/404/419/5xx.
  Les 401/419 invalident la session ; un 403 indique un refus de permission.
- `lib/services/auth_service.dart` : restauration du stockage bornée et protégée,
  fin de chargement garantie, session locale nettoyée indépendamment du réseau,
  révocation distante bornée à 5 secondes, notifications protégées après destruction.
- `lib/screens/tickets_history_screen.dart` : erreurs visibles et bouton
  « Réessayer », validation des données, rejet des réponses obsolètes,
  fermeture de la route protégée lors d'une expiration.
- `lib/services/diagnostics.dart` et `lib/main.dart` : traces de diagnostic
  limitées au nom de l'opération, statut HTTP, durée, type d'exception et frames
  de l'application. Aucun corps HTTP, en-tête, URL complète, message d'exception,
  identifiant, mot de passe ou jeton n'est écrit par ce diagnostic.
- `test/login_flow_test.dart` et `test/widget_test.dart` : reproductions et
  scénarios de reprise ; initialisation correcte des plugins simulés.
- `pubspec.yaml` : version `1.0.1+2`, pour distinguer le nouvel APK.

Le profil provient du champ `user` de la réponse de connexion et de son cache
local. Ce parcours ne comporte pas de requête de profil distincte. Les appels
protégés transmettent `Authorization: Bearer …` et `Accept: application/json`,
vérifiés dans les tests. L'autorisation effective reste vérifiée par l'API :
les tests simulent ses décisions, ils ne prouvent pas les permissions d'un compte
de production ni l'état réel d'un jeton Sanctum.

## Vérifications locales

Commandes exécutées depuis `distribution-app` :

```text
flutter test test/login_flow_test.dart test/widget_test.dart --no-pub --reporter expanded
flutter analyze --no-pub --no-fatal-infos
flutter build apk --release --no-pub
```

| Vérification | Résultat |
| --- | --- |
| Tests Flutter | **31/31 réussis**, voir `artifacts/tests.txt` |
| Analyse Dart | Aucune erreur ni avertissement ; 8 informations de style/dépréciation préexistantes, voir `artifacts/analyze.txt` |
| Reproductions avant correction | Voir `artifacts/regressions-before.txt` |
| Build release | Réussi, APK universel signé ; journal : `artifacts/build.txt` |
| Signature APK | Vérifiée par `apksigner`, identique au précédent APK local ; voir `artifacts/signature.txt` |
| Appareil Android | Aucun appareil détecté, voir `artifacts/android-devices.txt` |

Les tests couvrent connexion réussie, quotas décimaux, compte sans données,
profil absent/corrompu, réponse HTML, statuts HTTP 401/403/404/419/500, panne
réseau, délai dépassé, bouton de reprise, réponse tardive ignorée, délai simulé
de dix secondes, déconnexion/reconnexion même si la révocation distante bloque,
historique avec données, erreur d'historique et reprise, expiration dans
l'historique, arrière-plan/reprise et fermeture pendant une requête.

Les durées réseau de ces scénarios utilisent l'horloge virtuelle des tests.
Les `elapsed_ms` des traces représentent le temps de calcul du test, pas la
latence réelle d'un réseau mobile.

## APK livré

- Fichier : `artifacts/CSAR-Distribution-1.0.1+2.apk`
- Application : `sn.csar.csar_distribution`
- Version : `1.0.1`, code `2`
- Taille : 72 940 637 octets
- Architectures : `arm64-v8a`, `armeabi-v7a`, `x86_64`
- Android minimum : API 24 (identique au précédent APK local)
- Signature : certificat CSAR existant, schéma APK v2 vérifié
- SHA-256 : `5989c64648567d0ecfffe7b4822e1846da84b2014ea60ed549bc0708aadff597`

L'APK utilise l'adresse API habituelle `https://www.csar.sn`, sans modification
de configuration du serveur. Il est compilé et signé, mais sa validation
fonctionnelle sur le téléphone reste à effectuer.
Le binaire APK est disponible localement ; les fichiers `.apk` sont exclus du
suivi Git. Les sources, le rapport et les journaux de vérification sont versionnés.

Installation de mise à jour, une fois le téléphone autorisé :

```powershell
adb install -r "C:\xampp\csar.sn\distribution-app\artifacts\CSAR-Distribution-1.0.1+2.apk"
```

## Validation Android restant à réaliser

**Aucun des scénarios ci-dessous n'est encore validé sur appareil physique.**
Il faut un téléphone autorisé par ADB et un compte de test Distribution.
Pour simuler les pannes et l'expiration sans intervenir en production, utiliser
un environnement de test et, si nécessaire, un APK configuré via `CSAR_API_URL`.

| Scénario sur appareil | Attendu |
| --- | --- |
| Connexion d'un agent autorisé | Tableau et plannings complets |
| Serveur indisponible | Erreur explicite, puis reprise possible |
| Internet lent | Succès avant 25 s ou message de délai dépassé |
| Jeton expiré | Retour à la connexion, aucune route protégée restante |
| Compte sans données | « Aucun planning assigne », sans indicateur permanent |
| Déconnexion/reconnexion | Session renouvelée et tableau affiché |
| Historique | Tickets, navigation aller-retour et bouton de reprise |
| Saisie, validation, ticket QR | Opérations conservées |
| Impression Sunmi | Ticket réellement imprimé |
| Saisie hors ligne puis synchronisation | File conservée et transmission vérifiée |

Brancher le téléphone, activer le débogage USB et accepter l'autorisation RSA.
Ne pas désinstaller l'application pour effectuer la mise à jour : cela pourrait
effacer la file SQLite des saisies hors ligne. Installer avec `adb install -r`.
La signature du nouvel APK doit correspondre à celle de l'application installée ;
la comparaison au précédent APK local ne suffit pas à identifier celle du téléphone.

Pour consulter uniquement les nouvelles traces applicatives :

```powershell
adb logcat -v threadtime -s flutter:I | Select-String '\[Distribution\]|package:csar_distribution/'
```

Pour les erreurs Android natives, capturer séparément les logs du processus
Distribution, puis retirer toute donnée personnelle ou secrète avant partage.

## Périmètre

Toutes les modifications sont dans `distribution-app`. Aucun déploiement,
changement backend ou production n'a été effectué.
Scanner est une application distincte et n'a pas été modifiée.

Les implémentations de saisie, validation, génération QR, impression Sunmi,
stockage SQLite et transmission de la file hors ligne n'ont pas été réécrites.
Leurs fonctions matérielles et la synchronisation réelle restent à valider sur
appareil. Les délais ajoutés concernent le parcours de connexion/tableau/historique,
pas une refonte de toutes les opérations métier.
