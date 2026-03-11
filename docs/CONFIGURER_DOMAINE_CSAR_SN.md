# Faire fonctionner https://csar.sn

Si **https://csar.sn** affiche « Délai d’attente dépassé » (ERR_CONNECTION_TIMED_OUT) alors que **http://147.93.85.131** fonctionne, le domaine ne pointe pas encore vers votre serveur. Voici quoi faire.

---

## 1. Configurer le DNS (chez votre registrar de domaine)

Là où vous avez acheté **csar.sn** (ex. Orange, Sonatel, autre), créez ou modifiez les enregistrements :

| Type | Nom (sous-domaine) | Valeur (cible)     | TTL  |
|------|---------------------|--------------------|------|
| A    | @ (ou vide)         | 147.93.85.131     | 3600 |
| A    | www                 | 147.93.85.131     | 3600 |

- **@** = csar.sn  
- **www** = www.csar.sn  

La propagation DNS peut prendre de 5 minutes à 48 h.

Vérifier (après quelques minutes) :

- https://dnschecker.org → chercher **csar.sn** et **www.csar.sn** en type **A** : les deux doivent afficher **147.93.85.131**.

---

## 2. Configurer Nginx sur le serveur

Une fois le DNS propagé, le serveur recevra les requêtes pour **csar.sn** et **www.csar.sn**. Il faut dire à Nginx de répondre pour ces noms.

Sur le serveur (SSH) :

```bash
sudo nano /etc/nginx/sites-available/csar
```

Dans le bloc `server`, modifiez la ligne `server_name` pour inclure le domaine :

```nginx
server_name 147.93.85.131 csar.sn www.csar.sn _;
```

Sauvegardez (Ctrl+O, Entrée, Ctrl+X), puis :

```bash
nginx -t && sudo systemctl reload nginx
```

Après ça, **http://csar.sn** et **http://www.csar.sn** devraient afficher le même site que http://147.93.85.131 (page de maintenance ou site selon le mode).

---

## 3. HTTPS (optionnel, recommandé)

Sans certificat SSL, le navigateur affichera « Non sécurisé » et **https://csar.sn** peut échouer. Pour activer HTTPS avec Let’s Encrypt (gratuit) :

1. Vérifier que le DNS pointe bien vers **147.93.85.131** (étape 1).
2. Sur le serveur :

```bash
sudo certbot --nginx -d csar.sn -d www.csar.sn
```

Suivre les questions (e-mail, accord des conditions). Certbot configurera le certificat et Nginx pour HTTPS.

Ensuite **https://csar.sn** et **https://www.csar.sn** fonctionneront.

---

## Résumé

1. **DNS** : A @ → 147.93.85.131 et A www → 147.93.85.131  
2. **Nginx** : `server_name` avec csar.sn et www.csar.sn  
3. **HTTPS** : `certbot --nginx -d csar.sn -d www.csar.sn`  
4. **Favicon** : déjà ajouté sur la page de maintenance (logo CSAR). Après mise à jour, refaire `php artisan up` puis `php artisan down --render="errors.503"` pour régénérer la page.
