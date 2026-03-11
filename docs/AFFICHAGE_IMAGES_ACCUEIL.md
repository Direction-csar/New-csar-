# Affichage des images (Publications, Actualités, Galerie)

## Pourquoi je vois le logo CSAR au lieu de mes images ?

Les images sont servies depuis le **dossier de stockage Laravel**. Il faut que le lien `public/storage` existe.

### Solution 1 : Créer le lien storage (recommandé)

1. **En ligne de commande** (dans le dossier du projet) :
   ```bash
   php artisan storage:link
   ```
2. **Ou** ouvrir une fois dans le navigateur :  
   `http://localhost:8000/creer_lien_storage.php`  
   (puis supprimer le fichier `public/creer_lien_storage.php` pour la sécurité)

Après cela, les images déjà uploadées via l’admin (Actualités, Publications, Galerie) s’afficheront.

### Solution 2 : Images dans `public/images/`

Si vous avez mis des images dans `public/images/publication/` ou `public/images/` :

- Le code accepte les chemins qui commencent par `images/` (ex. `images/publication/mon-image.jpg`).
- Pour les utiliser, il faut que le champ en base (image de couverture, image vedette, etc.) contienne exactement ce chemin (sans `public/` ni `storage/`).

### Galerie

- Les images de la galerie viennent de la table **gallery_images** (champ `file_path`).
- Si la galerie est vide, ajoutez des images via l’interface d’administration de la galerie (ou les entrées en base avec un `file_path` valide).
- Après création du lien storage, les fichiers uploadés dans `storage/app/public/` seront accessibles.
