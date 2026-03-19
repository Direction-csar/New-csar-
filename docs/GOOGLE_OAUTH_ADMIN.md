# Connexion Admin via Google (Gmail)

L'admin peut se connecter à `http://localhost:8000/admin/login` avec son compte Gmail.

## Configuration

### 1. Google Cloud Console

1. Aller sur https://console.cloud.google.com/
2. Créer un projet ou en sélectionner un
3. Activer l'API "Google+ API" ou "Google Identity"
4. Aller dans **APIs & Services** > **Credentials** > **Create Credentials** > **OAuth client ID**
5. Type : **Web application**
6. Nom : CSAR Admin
7. **Authorized redirect URIs** : ajouter
   - En local : `http://localhost:8000/admin/login/google/callback`
   - En prod : `https://votredomaine.com/admin/login/google/callback`
8. Copier le **Client ID** et le **Client Secret**

### 2. Variables d'environnement (.env)

```env
GOOGLE_CLIENT_ID=votre_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=votre_client_secret

# Optionnel : liste des emails autorisés (séparés par des virgules)
# Si vide, tout admin existant peut se connecter via Google
ADMIN_ALLOWED_EMAILS=admin@csar.sn,dg@csar.sn
```

### 3. Migration

```bash
php artisan migrate
```

La migration ajoute la colonne `google_id` à la table `users`.

### 4. Premier admin via Google

- Si l'email n'existe pas et `ADMIN_ALLOWED_EMAILS` est vide : un nouvel admin est créé automatiquement
- Si `ADMIN_ALLOWED_EMAILS` est défini : seuls ces emails peuvent se connecter
- Pour un admin existant : ajouter son email dans la base (avec role=admin) ou dans `ADMIN_ALLOWED_EMAILS`

## Sécurité

- Les admins existants avec le même email seront liés à leur compte Google
- Utilisez `ADMIN_ALLOWED_EMAILS` en production pour restreindre l'accès
- Le bouton "Se connecter avec Google" n'apparaît que si `GOOGLE_CLIENT_ID` est configuré
