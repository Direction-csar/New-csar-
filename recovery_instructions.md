# 🔧 Instructions de Récupération MySQL InnoDB

## Situation Actuelle

MySQL a des erreurs InnoDB critiques qui empêchent le démarrage complet. Le mode de récupération a été configuré à **niveau 6** (maximum).

## ⚠️ IMPORTANT - Mode Récupération Niveau 6

En mode récupération niveau 6 :
- ✅ MySQL peut démarrer malgré les erreurs
- ✅ Vous pouvez **LIRE** les données
- ❌ Vous **NE POUVEZ PAS** :
  - Créer/modifier/supprimer des tables
  - Insérer/mettre à jour des données
  - Exécuter des migrations Laravel
  - Faire des opérations d'écriture

## 📋 Étapes de Récupération

### 1. Redémarrer MySQL

Dans le XAMPP Control Panel :
1. Arrêtez MySQL
2. Attendez 10 secondes
3. Redémarrez MySQL
4. Vérifiez les logs pour confirmer le démarrage

### 2. Tester la Connexion

Une fois MySQL redémarré, exécutez :
```bash
php fix_mysql_connection.php
```

### 3. Exporter les Données (si la connexion fonctionne)

Si MySQL accepte les connexions en mode lecture :
```bash
C:\xampp\mysql\bin\mysqldump.exe -u root csar > backup_csar_$(date +%Y%m%d).sql
```

### 4. Solution Permanente - Réinitialiser InnoDB

**⚠️ ATTENTION : Cette opération supprimera toutes les données InnoDB !**

1. **Arrêter MySQL** dans XAMPP

2. **Sauvegarder les fichiers importants** :
   ```bash
   # Copier les fichiers de données si nécessaire
   copy C:\xampp\mysql\data\csar C:\xampp\mysql\data\csar_backup
   ```

3. **Supprimer les fichiers InnoDB corrompus** :
   ```bash
   del C:\xampp\mysql\data\ibdata1
   del C:\xampp\mysql\data\ib_logfile0
   del C:\xampp\mysql\data\ib_logfile1
   ```

4. **Retirer le mode de récupération** du fichier `my.ini` :
   - Ouvrir `C:\xampp\mysql\bin\my.ini`
   - Supprimer ou commenter la ligne `innodb_force_recovery=6`

5. **Redémarrer MySQL** - Il créera de nouveaux fichiers InnoDB

6. **Réimporter vos données** :
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root csar < backup_csar_YYYYMMDD.sql
   ```

## 🔄 Alternative : Réinitialiser Complètement

Si vous n'avez pas de données critiques à sauvegarder :

1. Arrêter MySQL
2. Supprimer tous les fichiers dans `C:\xampp\mysql\data\` (sauf les dossiers de bases de données que vous voulez garder)
3. Retirer `innodb_force_recovery=6` du `my.ini`
4. Redémarrer MySQL
5. Réexécuter les migrations Laravel : `php artisan migrate`

## 📞 Support

Si le problème persiste après ces étapes, les fichiers InnoDB sont probablement trop corrompus et une réinitialisation complète sera nécessaire.


