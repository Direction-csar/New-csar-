# Rapport — Affectation inspecteurs / magasiniers (14 régions)

Statut : développement terminé et testé en local. **Aucune donnée de production touchée.**

## 1. Principe : aucune donnée inventée

Les noms, e-mails et téléphones des inspecteurs réels ne sont **pas** dans le dépôt ni la base locale (qui ne contient que des comptes `demo.*@csar.test`). Le système est donc prêt à les recevoir, sans rien fabriquer :

1. `php artisan stock:audit` puis `php artisan stock:coverage` sur le serveur → état réel des 14 régions.
2. La direction fournit un fichier CSV (modèle : `docs/modele-inspecteurs.csv`) : `nom;email;telephone;regions` (régions séparées par `|`).
3. `php artisan inspectors:provision fichier.csv` → **simulation** (rien n'est écrit) ; ajouter `--apply` après validation.

## 2. Outils livrés

| Outil | Rôle |
|---|---|
| `php artisan stock:coverage [--json]` | Lecture seule : pour chacune des 14 régions, magasins, magasiniers responsables, inspecteurs affectés, état (COUVERT / INCOMPLET / AUCUN MAGASIN) |
| `php artisan inspectors:provision <csv> [--apply]` | Crée les comptes inspecteurs (mot de passe aléatoire écrit dans `storage/app/private/`, droits 0600) et affecte tous les magasins actifs des régions indiquées. Idempotent, journalisé, refuse d'écraser un compte d'un autre rôle, ne supprime rien |
| Page « Affectations inspecteurs » (DG/DTL/Admin) | Nouveau panneau **Couverture des 14 régions** + gestion manuelle des affectations |
| API `/stock-ops/warehouses` (inspecteur) | Renvoie désormais, pour chaque magasin du périmètre, la liste de ses **magasiniers** (nom, téléphone) |

Les 14 régions : Dakar, Thiès, Diourbel, Fatick, Kaolack, Kaffrine, Louga, Saint-Louis, Matam, Tambacounda, Kédougou, Kolda, Sédhiou, Ziguinchor.

## 2bis. Administration des comptes (Admin / DG / DTL)

Page **« Personnel stock »** (`/admin|dg|dtl/personnel-stock`) : créer, modifier, désactiver/réactiver inspecteurs et magasiniers, rattacher un magasinier à son magasin, avec **historique** (audit_logs `staff.*`, dont les changements de magasin). Les magasins des inspecteurs se gèrent dans « Affectations inspecteurs » (historique + révocation). Aucun compte n'est supprimé ; mot de passe initial généré et affiché une seule fois.

**Faille corrigée** : un compte désactivé conservait l'accès API (token Sanctum encore valide, login non bloqué). Désormais : login refusé (403), middleware `EnsureActiveApiUser` sur toutes les routes API stock, tokens révoqués à la désactivation.

**APK Inspecteur** : la liste des magasins affiche maintenant les magasiniers du magasin (APK recompilé, `flutter analyze` propre).

## 3. État en local (données de démo uniquement)

| Région | Magasin | Magasinier | Inspecteur | État |
|---|---|---|---|---|
| Dakar | DEMO Magasin Dakar Centre ; Cuve FUEL-SMOKE | DEMO Magasinier Dakar ; — | DEMO Inspecteur ; — | Incomplet |
| Thiès | DEMO Magasin Thiès | DEMO Magasinier Thiès | DEMO Inspecteur | Couvert |
| 12 autres | aucun | — | — | Aucun magasin |

L'état **réel** (production) ne sera connu qu'après `stock:audit` / `stock:coverage` sur le serveur.

## 4. Règles garanties (tests)

- Un magasinier n'accède qu'à son magasin (Web + API).
- Un inspecteur ne voit/vérifie que son périmètre, y compris en forçant les URL/IDs ; sans affectation il ne voit rien.
- DG / DTL / Admin : vision globale ; DTL/DG peuvent modifier les affectations ; historique conservé, révocation sans suppression.
- Circuit DG → DTL → Inspecteur → Magasinier → Inspecteur (vérification) inchangé ; le stock officiel ne bouge qu'après vérification.
- Web et APK utilisent les mêmes services backend (mêmes données).

## 5. Tests

- `InspectorScopeTest` : 21 tests (dont compte désactivé, gestion du personnel, permissions par rôle) (isolation, révocation, couverture 14 régions, magasiniers visibles de l'inspecteur, provisionnement simulation/apply/idempotence).
- Suite complète : suite complète **490 passed, 1 skipped** (2 343 assertions), aucune régression.

## 6. Points ouverts

- **Magasins réels des 14 régions** : à confirmer par l'audit de production ; les régions sans magasin apparaîtront « AUCUN MAGASIN » (aucun magasin n'est créé automatiquement).
- **Un magasinier par magasin** : comptes à créer par l'administration avec les vraies identités (`users.warehouse_id`).
- **Nettoyage fictifs** : candidats identifiables après audit (ex. noms `DEMO*`, e-mails `*@csar.test`, « Cuve FUEL-SMOKE »). **Rien ne sera supprimé sans votre validation, un audit et une sauvegarde préalables.**
- **Tests APK sur téléphone** : toujours à réaliser (émulateur local non disponible/non démarré).
