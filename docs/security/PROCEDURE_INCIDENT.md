# Procédure incident de sécurité — CSAR

Conduite à tenir en cas de compromission suspectée ou confirmée. **Principe : contenir d'abord, documenter ensuite, restaurer en dernier.**

## 1. Détection — signaux à surveiller

- Connexions suspectes dans les logs nginx (`/var/log/nginx/`) ou `storage/logs/laravel.log`.
- Pic de 401/403 ou de requêtes `429` (rate limiting) sur les endpoints `/login`.
- Mouvements de stock, affectations ou validations créés par des comptes inhabituels (`created_by`, `validated_by` inconnus).
- Nouveau compte `admin`/`super_admin` non sollicité : `SELECT id,email,role,created_at FROM users WHERE role IN ('admin','super_admin');`
- Alertes email automatiques d'erreurs critiques (`bootstrap/app.php` envoie un mail admin par exception non routinelle en prod).

## 2. Containment immédiat (par ordre)

| Étape | Commande / action | Effet |
|---|---|---|
| 1 | Invalider toutes les sessions : `TRUNCATE sessions;` (via `php artisan db`) | Déconnecte tout le monde, y compris l'attaquant |
| 2 | Révoquer les tokens API : `TRUNCATE personal_access_tokens;` ou ciblé `DELETE FROM personal_access_tokens WHERE created_at > '<date_suspecte>'` | Coupe les accès mobiles/API |
| 3 | Désactiver le compte compromis : `UPDATE users SET is_active=0 WHERE id=…` | Blocage ciblé |
| 4 | Changer `DB_PASSWORD` + `ALTER USER` (voir `PLAN_CORRECTIONS.md` V2) | Coupe l'accès base |
| 5 | Révoquer le secret exposé (token GitHub, clé API, mot de passe) à la source | Ferme la porte d'entrée |
| 6 | Si intrusion serveur suspectée : couper nginx (`sudo systemctl stop nginx`) ou isoler la VM au niveau pare-feu | Gèle le site |

## 3. Préservation des preuves (avant tout nettoyage)

```bash
sudo cp -a /var/log/nginx /tmp/forensics-nginx-$(date +%F)
sudo cp -a /var/www/csar/storage/logs /tmp/forensics-laravel-$(date +%F)
mysqldump -h 127.0.0.1 -u csar -p csar > /tmp/forensics-db-$(date +%F).sql   # ou sudo mysqldump csar
history > /tmp/forensics-shell-$(date +%F).txt
last -a | head -50 > /tmp/forensics-connexions.txt
```

Ne **jamais** modifier ni supprimer ces fichiers. Noter l'heure exacte de chaque action.

## 4. Analyse

- Rechercher dans `laravel.log` : `Login`, `failed`, `429`, `SQL`, adresses IP récurrentes.
- Vérifier `personal_access_tokens` : tokens créés en dehors des heures de travail ou pour des comptes inactifs.
- Comparer `users`/`roles` à la dernière sauvegarde connue.
- Identifier la porte d'entrée : secret exposé, compte faible, endpoint non authentifié, faille applicative.

## 5. Éradication et restauration

- Corriger la cause racine (patch code, rotation de secret, règle firewall) **avant** de rouvrir.
- Restaurer les données altérées depuis le dernier dump sain (voir `SAUVEGARDES_RESTAURATION.md`).
- Forcer la réinitialisation des mots de passe des comptes exposés.
- Régénérer `APP_KEY` **uniquement** si une compromission cryptographique est prouvée — attention : invalide sessions + données chiffrées (`Crypt::`).

## 6. Post-incident

- Rédiger une chronologie horodatée : détection → containement → cause → correction.
- Ajouter le test automatisé qui aurait détecté la faille.
- Notifier la direction CSAR ; si données personnelles exposées (bénéficiaires, collecteurs) : obligation RGPD/loi sénégalaise de notification.

## 7. Contacts et responsabilités

| Rôle | Responsabilité incident |
|---|---|
| Administrateur plateforme | Containement, rotation des secrets, restauration |
| DTL / Directrice | Validation métier des impacts (dossiers, stocks) |
| Hébergeur / admin système | Pare-feu, isolation VM, journaux système |
