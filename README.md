# 🏛️ Plateforme CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience (Sénégal)

Application web Laravel du CSAR : site institutionnel public, interface Admin (gestion complète) et interface DG (vue stratégique en lecture seule).

---

## 📋 En bref

- **Site public** : Accueil, À propos, Institution, Actualités, Galerie, Rapports, Contact, Formulaire de demande, Suivi de demande, SIM (Système d’Information sur les Marchés), Partenaires, Discours, Newsletter.
- **Admin** : Tableau de bord, demandes, entrepôts, stocks, produits, personnel, utilisateurs, actualités, galerie, rapports SIM, gestion SIM (régions, marchés, collectes, demandes d’accès), chiffres clés, contenu, communications, messages, newsletter, audit, notifications, statistiques, nettoyage BDD.
- **DG** : Même vue de données qu’Admin (lecture seule) : tableau de bord, demandes, entrepôts, stocks, personnel, utilisateurs, rapports, carte interactive.

**Interfaces actuellement désactivées** : DRH, Responsable entrepôt, Agent (redirection vers une page « Interface désactivée »).

---

## 🔗 Accès

| Interface | URL |
|-----------|-----|
| **Admin** | `/admin` ou `/admin/login` |
| **DG** | `/dg` ou `/dg/login` |
| **Site public** | `/fr` ou `/en` (puis menu : actualités, galerie, demande, suivi, SIM, etc.) |

En production, remplacer par votre domaine (ex. `https://csar.sn`).

---

## 🚀 Technologies

- **Backend** : Laravel 12.x (PHP 8.2+)
- **Base de données** : MySQL
- **Frontend** : Blade, Bootstrap 5, CSS/JS
- **Sessions / Cache** : recommandation `database` (voir `.env`)

---

## 📦 Installation rapide

```bash
git clone <url-du-repo> csar
cd csar
composer install
cp .env.example .env
php artisan key:generate
```

Configurer `.env` (base de données, `APP_URL`, `SESSION_DRIVER=database`, `CACHE_STORE=database`), puis :

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

---

## 📚 Documentation complète

**Toute la description du projet (fonctionnalités, routes, BDD, configuration, déploiement) est dans :**

👉 **[docs/SUPPORT_PROJET_CSAR_COMPLET.md](docs/SUPPORT_PROJET_CSAR_COMPLET.md)**

Fichiers utiles à la racine :

- **ACTIONS_IMMEDIATES.txt** — 5 actions pour le déploiement (config, backups, tests).
- **A_GARDER_DNS_ET_LIENS.txt** — DNS et liens Admin/DG.

---

## 📄 Licence

Projet sous licence MIT. Voir [LICENSE](LICENSE) le cas échéant.

---

*Plateforme CSAR — Sécurité alimentaire et résilience, Sénégal.*
