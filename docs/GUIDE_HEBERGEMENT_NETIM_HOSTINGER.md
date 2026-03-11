# Guide : Héberger la plateforme CSAR (domaine Netim + hébergement Hostinger)

Vous avez acheté **le domaine sur Netim** et **l’hébergement sur Hostinger**. Ce guide explique comment lier le domaine au serveur et mettre en ligne votre site.

---

## Étape 1 : Récupérer les infos côté Hostinger

Connectez-vous à **Hostinger** (hpanel.hostinger.com) et notez :

| Info | Où la trouver |
|------|----------------|
| **Type d’hébergement** | VPS ou Hébergement web (shared) ? |
| **IP du serveur** | Pour VPS : dans "VPS" → Détails (ex. 72.61.16.34) |
| **Nameservers** | Pour hébergement web : "Domaines" → "Gérer" → "Nameservers" (ex. ns1.dns-parking.com, ns2.dns-parking.com) |

- Si vous avez un **VPS Hostinger** (serveur Ubuntu avec IP 72.61.16.34) → suivez la **Partie A** (domaine + VPS).
- Si vous avez un **hébergement web (shared)** Hostinger → suivez la **Partie B** (domaine + hébergement web).

---

## Partie A : Domaine Netim + VPS Hostinger (recommandé pour Laravel)

### A1. Pointer le domaine Netim vers l’IP du VPS

1. Connectez-vous à **Netim** : https://www.netim.com (ou espace client).
2. Allez dans **Mes domaines** → sélectionnez votre domaine (ex. **csar.sn**).
3. Ouvrez la gestion **DNS** ou **Zone DNS**.
4. Modifiez ou ajoutez ces enregistrements :

| Type | Nom (sous-domaine) | Valeur (cible) | TTL |
|------|---------------------|----------------|-----|
| **A** | `@` | `72.61.16.34` | 300 ou 3600 |
| **A** | `www` | `72.61.16.34` | 300 ou 3600 |

(Remplacez `72.61.16.34` par l’IP réelle de votre VPS si différente.)

5. Enregistrez et attendez la propagation DNS (5 min à 48 h).

### A2. Déployer Laravel sur le VPS Hostinger

Votre serveur est déjà en Ubuntu (comme indiqué dans votre message). Vous pouvez :

**Option 1 – Script automatique (si le code est sur GitHub)**

Sur le serveur (en SSH) :

```bash
# Installer git si besoin, puis récupérer le script
cd /root
# Collez le contenu de scripts/deploy/deploy_csar_vps.sh ou uploadez-le
chmod +x deploy_csar_vps.sh
./deploy_csar_vps.sh
```

Le script configure PHP, MySQL, Nginx, .env, migrations et peut configurer le SSL.

**Option 2 – Envoyer le projet depuis votre PC (sans GitHub)**

Depuis **PowerShell** sur votre PC Windows :

```powershell
scp -r C:\xampp\htdocs\csar root@72.61.16.34:/var/www/
```

Puis sur le serveur, suivez les étapes du fichier **HEBERGEMENT_UBUNTU_VPS.md** (section "Option 2 : Déploiement manuel"), en utilisant votre **nom de domaine** (ex. **www.csar.sn**) dans Nginx au lieu de l’IP.

**Important pour le domaine dans Nginx**

Dans la config Nginx (`/etc/nginx/sites-available/csar`), mettez :

```nginx
server_name www.votredomaine.com votredomaine.com;
```

Remplacez par votre vrai domaine acheté sur Netim.

### A3. SSL (HTTPS) avec Let’s Encrypt

Une fois le domaine pointé vers le VPS et le site accessible en HTTP :

```bash
sudo certbot --nginx -d votredomaine.com -d www.votredomaine.com
```

Renseignez l’email demandé. Après ça, le site sera en **https://**.

---

## Partie B : Domaine Netim + Hébergement web (shared) Hostinger

Avec un hébergement web classique Hostinger, vous n’avez pas un serveur Ubuntu complet : vous utilisez le **panel Hostinger (hPanel)** et souvent **pas de SSH** (ou SSH limité).

### B1. Utiliser les nameservers Hostinger (solution la plus simple)

1. Dans **Hostinger** : **Domaines** → votre domaine ou "Ajouter un domaine" → notez les **nameservers** (ex. `ns1.dns-parking.com`, `ns2.dns-parking.com`).
2. Dans **Netim** : **Mes domaines** → votre domaine → **Délégation / Nameservers**.
3. Passez en **"Nameservers personnalisés"** et entrez exactement les deux (ou trois) nameservers fournis par Hostinger.
4. Enregistrez. La propagation peut prendre jusqu’à 24–48 h.

Résultat : le domaine pointe entièrement vers Hostinger (site + emails si configurés chez Hostinger).

### B2. Garder les DNS chez Netim et pointer vers Hostinger

Si vous préférez garder la gestion DNS chez Netim :

1. Dans **Hostinger**, trouvez l’**IP du serveur** de votre hébergement (souvent dans "Informations d’hébergement" ou "Détails du compte").
2. Dans **Netim** → **Zone DNS** du domaine, ajoutez :

| Type | Nom | Valeur | TTL |
|------|-----|--------|-----|
| **A** | `@` | **IP fournie par Hostinger** | 300 |
| **A** | `www` | **IP fournie par Hostinger** | 300 |

Si Hostinger vous donne un **CNAME** pour `www` au lieu d’une IP, utilisez un enregistrement **CNAME** pour `www` vers la cible indiquée par Hostinger.

### B3. Mettre la plateforme CSAR sur l’hébergement web Hostinger

Laravel sur hébergement shared demande quelques précautions :

1. **Upload des fichiers**
   - Dans hPanel : **Fichiers** → **Gestionnaire de fichiers** (ou FTP avec les identifiants Hostinger).
   - Uploadez tout le contenu de votre projet dans le dossier prévu pour le site (souvent `public_html` ou un sous-dossier).

2. **Document root Laravel**
   - Le point d’entrée Laravel doit être le dossier **`public`**.
   - Sur Hostinger, vous pouvez souvent définir le "répertoire racine" du domaine sur `public_html/votresite/public` (en mettant le reste du projet au-dessus, par ex. `votresite/` avec `app/`, `public/`, etc. à l’intérieur).

3. **Base de données**
   - Dans hPanel : **Bases de données MySQL** → créer une base + un utilisateur, noter nom de la BDD, utilisateur, mot de passe et **nom d’hôte** (souvent `localhost`).
   - Créer un fichier **`.env`** à la racine du projet (pas dans `public`) avec :
     - `APP_URL=https://votredomaine.com`
     - `DB_DATABASE=...`, `DB_USERNAME=...`, `DB_PASSWORD=...`, `DB_HOST=...` (valeurs Hostinger).
   - Lancer les migrations si vous avez accès SSH ou un outil type "Run script" (sinon, importer un export SQL depuis phpMyAdmin).

4. **PHP**
   - Dans hPanel, choisir **PHP 8.1 ou 8.2** pour le domaine.
   - Vérifier que les extensions nécessaires à Laravel sont activées (mbstring, xml, curl, zip, gd, etc.) dans les paramètres PHP Hostinger.

5. **Sécurité**
   - Ne pas exposer le fichier `.env` : il doit être en dehors de la racine web (dossier `public`).

Si vous me dites si vous êtes en **VPS** ou en **hébergement web** Hostinger, je peux vous détailler uniquement la partie qui vous concerne (A ou B) avec les noms exacts des menus si vous les avez sous les yeux.

---

## Récapitulatif

| Étape | Netim | Hostinger |
|-------|--------|-----------|
| **VPS** | A → `72.61.16.34` (et www) | Déployer Laravel (script ou manuel), puis SSL |
| **Hébergement web** | Soit nameservers Hostinger, soit A/CNAME vers IP Hostinger | Upload fichier, BDD, racine = `public`, PHP 8.x |

---

## Vérifications après mise en place

1. **DNS** : https://dnschecker.org → vérifier que `votredomaine.com` et `www` pointent vers la bonne IP.
2. **Site** : ouvrir `http://votredomaine.com` puis `https://votredomaine.com` après SSL.
3. **Laravel** : pas d’erreur 500 ; si oui, consulter `storage/logs/laravel.log` (VPS) ou les logs dans hPanel (shared).

Si vous indiquez : **nom de domaine exact** + **VPS ou hébergement web**, on peut faire la configuration pas à pas avec les vrais noms (Netim / Hostinger).
