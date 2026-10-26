# Sauvegardes et restauration — CSAR

## 1. État des lieux

| Élément | État constaté |
|---|---|
| Script `backup-platform.sh` | Présent (workflow `/backup-restore`, serveur 192.168.2.141, `/var/www/csar`) |
| Dump base | `/tmp/csar_db_dump.sql` créé via `sudo mysqldump csar` (22/10/2026) — le compte `csar@localhost` refuse la socket ; utiliser `sudo` ou `-h 127.0.0.1` |
| Copie distante | **Pas encore faite** — le dump est en `/tmp` (volatil) sur le serveur |
| Chiffrement des sauvegardes | Non vérifié — à confirmer dans le script |
| Test de restauration | **Jamais exécuté** — priorité haute |

## 2. Stratégie cible

- **RPO ≤ 24 h** : dump SQL quotidien (cron) + rotation 7 quotidiens / 4 hebdomadaires.
- **RTO ≤ 4 h** : restauration documentée et testée trimestriellement.
- **Règle 3-2-1** : 3 copies, 2 supports (serveur + poste admin ou NAS), 1 hors site (cloud chiffré ou machine de secours).
- **Chiffrement** : les dumps contiennent des données personnelles (bénéficiaires, agents) — chiffrer avec `gpg` ou archive `openssl aes-256` avant transfert hors serveur.
- **Isolation** : les sauvegardes ne doivent pas être accessibles en écriture par l'utilisateur web (`www-data`) — éviter la suppression par ransomware.

## 3. Sauvegarde — commandes de référence

```bash
# Dump horodaté (socket root MariaDB)
sudo mysqldump csar | gzip > /var/backups/csar/csar_$(date +%F_%H%M).sql.gz

# Ou via les identifiants applicatifs (TCP)
mysqldump -h 127.0.0.1 -u csar -p csar | gzip > /var/backups/csar/csar_$(date +%F_%H%M).sql.gz

# Fichiers applicatifs (uploads, storage, .env exclu du tar public)
sudo tar czf /var/backups/csar/files_$(date +%F_%H%M).tar.gz \
    -C /var/www/csar storage/app public/uploads public/downloads

# Rapatriement sur le poste admin (depuis Windows PowerShell)
scp msow@192.168.2.141:/var/backups/csar/csar_2026-10-22_1200.sql.gz C:\Backup\csar\
```

`/var/backups/csar/` doit appartenir à `root:root` en `750`.

## 4. Restauration — environnement isolé (obligatoire avant toute restauration prod)

```bash
# Sur une machine de test ou schéma dédié — JAMAIS sur la base de prod
sudo mysql -e "CREATE DATABASE csar_restore_test;"
sudo mysql csar_restore_test < /tmp/csar_db_dump.sql

# Vérifications minimales post-restauration
sudo mysql csar_restore_test -e "
  SELECT COUNT(*) FROM public_requests;
  SELECT COUNT(*) FROM stock_movements;
  SELECT COUNT(*) FROM users WHERE is_active=1;
  SHOW TABLES LIKE 'request_missions';"
```

Comparer les compteurs avec la production avant de valider le dump comme « sain ».

## 5. Restauration en production (procédure d'urgence)

```bash
cd /var/www/csar
php artisan down --secret="lien-maintenance"          # mode maintenance
sudo mysqldump csar > /tmp/pre-restore-$(date +%F).sql  # sauvegarde de sécurité
sudo mysql -e "DROP DATABASE csar; CREATE DATABASE csar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql csar < /chemin/dump_sain.sql
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

Points de vigilance :
- `DROP DATABASE` est destructif — toujours faire le dump de sécurité préalable.
- Après restauration : `TRUNCATE sessions;` + `TRUNCATE personal_access_tokens;` (les anciens jetons peuvent être compromis).
- Vérifier `migrations` : `SELECT MAX(batch) FROM migrations;` doit correspondre à l'état attendu.
- Relancer `php artisan migrate --force` uniquement si le dump est plus ancien que le code déployé — avec accord préalable.

## 6. Surveillance

- Alerte si le dump quotidien échoue ou si la taille chute brutalement (>30 % en moins = suspicion).
- `backup-platform.sh` devrait logger dans `/var/log/csar-backup.log` avec rotation `logrotate`.
- Tester une restauration complète en environnement isolé **au moins une fois par trimestre** et consigner le résultat dans `TESTS_SECURITE.md`.
