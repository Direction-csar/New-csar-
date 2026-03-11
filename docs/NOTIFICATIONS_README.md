# 🔔 Système de Notifications CSAR - Guide Rapide

## Installation ✅ TERMINÉE

Le système est installé et opérationnel !

## 🚀 Démarrage Rapide

### 1. Accéder au système

**En tant qu'administrateur :**
1. Connectez-vous au tableau de bord admin
2. Regardez l'icône cloche 🔔 en haut à droite
3. Le badge rouge indique le nombre de notifications non lues

### 2. Voir les notifications

**Dropdown rapide :**
- Cliquez sur l'icône cloche
- Les 10 dernières notifications s'affichent
- Actions disponibles : Voir, Marquer lu, Supprimer

**Centre de notifications complet :**
- Cliquez sur "Voir toutes les notifications"
- OU allez sur `/admin/notifications`
- Filtres : Toutes / Non lues / Lues
- Statistiques en temps réel

### 3. Générer des notifications

Les notifications sont **automatiques** pour :

#### ✅ Demandes d'aide
Lorsqu'un citoyen soumet une demande depuis la page publique :
```
Titre : "Nouvelle demande d'aide"
Message : "Une nouvelle demande d'aide alimentaire a été soumise par [nom]"
Icône : 📄 file-text
```

#### ✅ Messages de contact
Lorsqu'un visiteur envoie un message :
```
Titre : "Nouveau message de contact"
Message : "Nouveau message de contact reçu de [nom] ([email])"
Icône : ✉️ mail
```

#### ✅ Inscriptions newsletter
Lorsqu'un visiteur s'inscrit à la newsletter :
```
Titre : "Nouvelle inscription à la newsletter"
Message : "Nouvelle inscription à la newsletter : [email]"
Icône : 📤 send
```

#### ✅ Communications officielles
Lorsqu'une actualité est publiée :
```
Titre : "Nouvelle communication officielle"
Message : "Une nouvelle communication officielle a été publiée : [titre]"
Icône : 📢 megaphone
```

## 🎯 Fonctionnalités

### Interface
- ✅ Badge de compteur animé
- ✅ Dropdown moderne avec dernières notifications
- ✅ Page complète avec filtres
- ✅ Design 3D avec animations
- ✅ Responsive mobile/tablette

### Actions
- ✅ Marquer comme lu/non lu
- ✅ Tout marquer comme lu
- ✅ Supprimer une notification
- ✅ Voir l'élément concerné (redirection)

### Technique
- ✅ Rafraîchissement auto (30s)
- ✅ API REST complète
- ✅ Protection CSRF
- ✅ Optimisé et performant

## 📝 Utilisation avancée

### Créer une notification manuellement

En PHP (contrôleur) :
```php
use App\Models\Notification;

Notification::createNotification(
    'Mon titre',
    'Mon message',
    'info',  // Type: info, success, warning, error
    [],      // Données (optionnel)
    null,    // Entité liée (optionnel)
    'bell',  // Icône (optionnel)
    route('admin.dashboard')  // URL (optionnel)
);
```

### Types de notifications
- `info` - Bleu (informations générales)
- `success` - Vert (succès)
- `warning` - Orange (avertissement)
- `error` - Rouge (erreur)
- `demande` - Bleu (demandes)
- `message` - Info (messages)
- `newsletter` - Vert (newsletter)
- `communication` - Violet (communications)

### Icônes disponibles
Utilisez n'importe quelle icône [Font Awesome](https://fontawesome.com/icons) :
- `bell` - Cloche
- `file-text` - Document
- `mail` - Email
- `send` - Envoi
- `megaphone` - Mégaphone
- `check-circle` - Validation
- `exclamation-triangle` - Avertissement
- `info` - Information

## 🔧 Configuration

### Modifier le délai de rafraîchissement

Fichier : `public/js/notifications.js`
```javascript
this.refreshInterval = 60000; // 60 secondes
```

### Personnaliser les couleurs

Fichier : `public/css/notifications.css`
```css
:root {
    --notification-primary: #0d6efd;
    --notification-success: #198754;
    /* Modifiez selon vos besoins */
}
```

## 📊 API JavaScript

Le système expose un objet global `notificationSystem` :

```javascript
// Charger les notifications
notificationSystem.loadNotifications();

// Rafraîchir le badge
notificationSystem.updateBadge();

// Marquer comme lue
notificationSystem.markAsRead(notificationId);

// Tout marquer comme lu
notificationSystem.markAllAsRead();

// Supprimer
notificationSystem.deleteNotification(notificationId);
```

## 🐛 Dépannage

### Les notifications ne s'affichent pas
1. Vérifiez la console navigateur (F12)
2. Vérifiez que vous êtes connecté en tant qu'admin
3. Videz le cache : `php artisan cache:clear`

### Le badge ne se met pas à jour
1. Vérifiez que JavaScript est activé
2. Ouvrez la console pour voir les erreurs
3. Vérifiez la route API : `/admin/api/notifications/count`

### Erreur 403 ou 419
1. Vérifiez le token CSRF dans le `<head>` du layout
2. Videz le cache de config : `php artisan config:clear`

## 📚 Documentation complète

Pour plus de détails, consultez :
- `docs/NOTIFICATIONS_SYSTEM.md` - Documentation technique complète
- `SYSTEME_NOTIFICATIONS_CSAR.md` - Récapitulatif d'installation

## ✅ Checklist de vérification

- [ ] L'icône cloche est visible en haut à droite
- [ ] Le badge de compteur fonctionne
- [ ] Le dropdown s'ouvre au clic
- [ ] Les notifications s'affichent
- [ ] Le bouton "Tout marquer lu" fonctionne
- [ ] La page complète `/admin/notifications` est accessible
- [ ] Les filtres fonctionnent (Toutes/Non lues/Lues)
- [ ] Créer une demande génère une notification
- [ ] Envoyer un message génère une notification
- [ ] S'inscrire à la newsletter génère une notification
- [ ] Publier une actualité génère une notification

## 🎉 C'est tout !

Le système est prêt à l'emploi. Profitez-en ! 🚀

---

**Questions ?** Consultez la documentation complète ou les logs : `storage/logs/laravel.log`

