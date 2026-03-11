# 📖 GUIDE UTILISATEUR - PLATEFORME ADMIN CSAR

**Version** : 1.0  
**Date** : 24 Octobre 2025  
**Audience** : Administrateurs, DG, DRH, Responsables d'entrepôt

---

## 📑 TABLE DES MATIÈRES

1. [Introduction](#1-introduction)
2. [Premiers Pas](#2-premiers-pas)
3. [Dashboard](#3-dashboard)
4. [Gestion des Utilisateurs](#4-gestion-des-utilisateurs)
5. [Gestion des Demandes](#5-gestion-des-demandes)
6. [Gestion des Entrepôts](#6-gestion-des-entrepôts)
7. [Gestion des Stocks](#7-gestion-des-stocks)
8. [Gestion du Personnel](#8-gestion-du-personnel)
9. [Communication](#9-communication)
10. [Rapports et Statistiques](#10-rapports-et-statistiques)
11. [FAQ et Dépannage](#11-faq-et-dépannage)

---

## 1. INTRODUCTION

### 1.1 Qu'est-ce que la plateforme CSAR Admin ?

La plateforme CSAR Admin est une interface web sécurisée permettant de gérer l'ensemble des opérations du Commissariat à la Sécurité Alimentaire et à la Résilience.

**Fonctionnalités principales** :
- Gestion des stocks et entrepôts
- Traitement des demandes citoyennes
- Gestion des ressources humaines
- Communication interne et externe
- Rapports et statistiques en temps réel

### 1.2 Rôles et Permissions

| Rôle | Description | Accès |
|------|-------------|-------|
| **Admin** | Accès complet à toutes les fonctionnalités | 100% |
| **DG** | Direction Générale - Rapports et supervision | Lecture + Rapports |
| **DRH** | Gestion du personnel uniquement | Personnel + Stats RH |
| **Responsable** | Gestion des stocks et entrepôts | Stocks + Entrepôts |
| **Agent** | Consultation et coordination terrain | Lecture limitée |

---

## 2. PREMIERS PAS

### 2.1 Se Connecter

1. **Accéder à la plateforme**
   ```
   URL : https://csar.sn/admin/login
   ```

2. **Saisir vos identifiants**
   - Email : Votre adresse email professionnelle
   - Mot de passe : Minimum 8 caractères

3. **Options**
   - ☑️ "Se souvenir de moi" : Rester connecté 2 semaines
   - Ne PAS utiliser sur ordinateur partagé

4. **Cliquer sur "Se connecter"**

⚠️ **Sécurité** :
- Maximum 5 tentatives en 15 minutes
- Compte bloqué après échecs répétés
- Contactez l'administrateur si bloqué

### 2.2 Interface Principale

```
┌──────────────────────────────────────────────┐
│  SIDEBAR  │         CONTENU PRINCIPAL       │
│           │                                  │
│  Logo     │   🔔 Notifications    👤 Profil │
│  ───      │   ─────────────────────────────  │
│  📊 Dashboard                                │
│  📝 Demandes                                 │
│  👥 Utilisateurs                             │
│  🏭 Entrepôts                                │
│  📦 Stocks                                   │
│  ...                                         │
└──────────────────────────────────────────────┘
```

**Éléments clés** :
1. **Sidebar gauche** : Navigation principale
2. **Barre supérieure** : Notifications, profil, déconnexion
3. **Zone centrale** : Contenu de la page active
4. **Breadcrumb** : Fil d'Ariane (position actuelle)

### 2.3 Navigation

**Méthode 1 - Menu Sidebar** :
- Cliquez sur une section dans le menu gauche
- Le menu actif est surligné en violet

**Méthode 2 - Raccourcis Dashboard** :
- Cartes cliquables sur le tableau de bord
- Accès rapide aux fonctions principales

**Méthode 3 - Breadcrumb** :
- Cliquez sur un élément du fil d'Ariane
- Remontez dans l'arborescence

---

## 3. DASHBOARD

### 3.1 Vue d'Ensemble

Le Dashboard affiche les statistiques clés en temps réel.

**Cartes de statistiques** :
```
┌──────────┐  ┌──────────┐  ┌──────────┐
│ 👥 150   │  │ 📝 45    │  │ 🏭 12    │
│ Users    │  │ Demandes │  │ Entrepôts│
└──────────┘  └──────────┘  └──────────┘
```

### 3.2 Graphiques

**1. Évolution des Stocks** (Ligne)
- Période : 6 derniers mois
- Par catégorie : Alimentaire, Matériel, Carburant, Médicaments
- Survolez pour détails

**2. Demandes par Statut** (Donut)
- En attente (Orange)
- En cours (Bleu)
- Approuvées (Vert)
- Rejetées (Rouge)

**3. Activité Utilisateurs** (Barres)
- Connexions par jour
- 7 derniers jours

### 3.3 Activités Récentes

Liste des 10 dernières actions :
- ✅ Création de stock
- 📝 Demande traitée
- 👤 Nouvel utilisateur
- 🏭 Entrepôt modifié

**Rafraîchir** : Cliquez sur le bouton "🔄 Actualiser"

### 3.4 Alertes Système

```
⚠️ 5 produits en dessous du seuil minimum
🔴 1 demande urgente non traitée
✅ Sauvegarde réussie ce matin
```

**Actions** :
- Cliquez sur une alerte pour détails
- Marquezcomme lue après traitement

---

## 4. GESTION DES UTILISATEURS

### 4.1 Liste des Utilisateurs

**Accès** : Menu > Utilisateurs

**Affichage** :
| Nom | Email | Rôle | Statut | Actions |
|-----|-------|------|--------|---------|
| Jean Dupont | jean@csar.sn | Admin | ✅ Actif | 👁️ ✏️ 🗑️ |

**Fonctions** :
- 🔍 **Recherche** : Par nom ou email
- 🎛️ **Filtres** : Par rôle, statut
- 📄 **Pagination** : 25/50/100 par page
- 📥 **Export** : CSV, Excel, PDF

### 4.2 Créer un Utilisateur

1. **Cliquer sur "➕ Nouvel Utilisateur"**

2. **Remplir le formulaire** :
   - Nom complet *(obligatoire)*
   - Email *(obligatoire, unique)*
   - Téléphone
   - Rôle : Admin / DG / DRH / Responsable / Agent
   - Mot de passe temporaire

3. **Options** :
   - ☑️ Compte actif
   - ☑️ Envoyer email de bienvenue

4. **Cliquer sur "Enregistrer"**

⚠️ **Important** :
- L'email doit être unique
- Le mot de passe sera envoyé par email
- L'utilisateur doit changer son mot de passe à la première connexion

### 4.3 Modifier un Utilisateur

1. **Cliquer sur l'icône ✏️ (Modifier)**
2. **Modifier les champs nécessaires**
3. **Enregistrer**

**Champs modifiables** :
- Informations personnelles
- Rôle
- Statut (actif/inactif)
- Photo de profil

❌ **Non modifiable** : Email (identifiant unique)

### 4.4 Désactiver un Compte

**Option 1 - Temporaire** :
1. Modifier l'utilisateur
2. Décocher "Compte actif"
3. Enregistrer

**Option 2 - Permanent** :
1. Cliquer sur 🗑️ (Supprimer)
2. Confirmer la suppression
3. ⚠️ **Irréversible** - Toutes les données associées sont supprimées

### 4.5 Réinitialiser un Mot de Passe

1. **Cliquer sur l'icône 🔑 (Réinitialiser)**
2. **Un nouveau mot de passe est généré**
3. **Envoyé par email à l'utilisateur**
4. **L'utilisateur doit le changer au prochain login**

---

## 5. GESTION DES DEMANDES

### 5.1 Liste des Demandes

**Accès** : Menu > Demandes

**Statuts** :
- 🟠 **En attente** : Non traitée
- 🔵 **En cours** : En traitement
- 🟢 **Approuvée** : Acceptée
- 🔴 **Rejetée** : Refusée

### 5.2 Consulter une Demande

1. **Cliquer sur la ligne de la demande**

**Informations affichées** :
- Nom et prénom du demandeur
- Type de demande
- Région d'origine
- Message détaillé
- Pièces jointes (si présentes)
- Code de suivi
- Date de soumission
- Historique des actions

### 5.3 Traiter une Demande

1. **Ouvrir la demande**
2. **Lire attentivement**
3. **Choisir une action** :
   - ✅ **Approuver** : Accepter la demande
   - ❌ **Rejeter** : Refuser avec motif
   - 📝 **En cours** : Marquer comme en traitement

4. **Ajouter un commentaire** (optionnel)
5. **Cliquer sur "Enregistrer"**

⚠️ **Notification automatique** :
- Le demandeur reçoit un email
- SMS si numéro fourni
- Notification dans son espace (si compte)

### 5.4 Télécharger le PDF

1. **Ouvrir la demande**
2. **Cliquer sur "📄 Télécharger PDF"**
3. **Le fichier PDF est généré** contenant :
   - Toutes les informations
   - Code de suivi
   - Logo CSAR
   - Statut actuel

### 5.5 Exporter les Demandes

1. **Cliquer sur "📥 Exporter"**
2. **Choisir le format** : CSV / Excel / PDF
3. **Sélectionner les filtres** (optionnel) :
   - Date de début
   - Date de fin
   - Statut
   - Région
4. **Cliquer sur "Télécharger"**

---

## 6. GESTION DES ENTREPÔTS

### 6.1 Vue Carte Interactive

**Accès** : Menu > Entrepôts

**Affichage carte** :
- 🗺️ Carte interactive du Sénégal
- 📍 Marqueurs GPS pour chaque entrepôt
- Cliquez sur un marqueur pour détails

### 6.2 Liste des Entrepôts

**Tableau** :
| Nom | Code | Région | Capacité | Occupation | Statut |
|-----|------|--------|----------|------------|--------|
| Entrepôt Dakar | DAK-001 | Dakar | 1000 T | 750 T (75%) | ✅ |

**Indicateurs** :
- 🟢 < 70% : Normal
- 🟠 70-90% : Attention
- 🔴 > 90% : Plein

### 6.3 Créer un Entrepôt

1. **Cliquer sur "➕ Nouvel Entrepôt"**

2. **Informations de base** :
   - Nom *(obligatoire)*
   - Code unique *(ex: DAK-001)*
   - Région
   - Adresse complète

3. **Géolocalisation** :
   - Latitude *(ex: 14.7167)*
   - Longitude *(ex: -17.4677)*
   - Ou cliquer sur la carte

4. **Capacité** :
   - Capacité maximale (tonnes)
   - Types de stock acceptés

5. **Responsable** :
   - Nom du responsable
   - Téléphone
   - Email

6. **Documents** (optionnel) :
   - Photos de l'entrepôt
   - Plans
   - Documents administratifs

7. **Enregistrer**

### 6.4 Modifier un Entrepôt

1. **Cliquer sur ✏️ (Modifier)**
2. **Modifier les champs**
3. **Enregistrer**

⚠️ **Attention** :
- Modifier la capacité affecte les calculs
- Vérifier les stocks existants avant

### 6.5 Désactiver un Entrepôt

1. **Modifier l'entrepôt**
2. **Statut : Inactif**
3. **Enregistrer**

❗ **Effet** :
- N'apparaît plus sur la carte publique
- Mouvements de stock bloqués
- Historique conservé

---

## 7. GESTION DES STOCKS

### 7.1 Vue d'Ensemble

**Accès** : Menu > Gestion des Stocks

**Statistiques** :
```
Total Mouvements : 1,245
├─ Entrées : 645 (52%)
├─ Sorties : 485 (39%)
└─ Transferts : 115 (9%)
```

### 7.2 Types de Mouvements

**1. Entrée de Stock** ➕
- Réapprovisionnement
- Don reçu
- Production

**2. Sortie de Stock** ➖
- Distribution
- Mission humanitaire
- Perte/avarie

**3. Transfert** 🔄
- Entre entrepôts
- Redistribution

**4. Ajustement** ⚙️
- Inventaire
- Correction d'erreur

### 7.3 Enregistrer une Entrée

1. **Cliquer sur "➕ Nouvelle Entrée"**

2. **Formulaire** :
   - Type : **Entrée**
   - Entrepôt de destination
   - Produit (sélectionner dans la liste)
   - Quantité *(nombre positif)*
   - Unité (sacs, kg, litres, etc.)
   - Prix unitaire (FCFA)
   - Fournisseur
   - Document justificatif (upload)
   - Motif/Commentaire

3. **Validation** :
   - Quantité > 0
   - Capacité entrepôt suffisante
   - Produit actif

4. **Enregistrer**

✅ **Actions automatiques** :
- Stock mis à jour
- Mouvement enregistré
- Notification au responsable
- Log d'audit créé

### 7.4 Enregistrer une Sortie

1. **Cliquer sur "➖ Nouvelle Sortie"**

2. **Formulaire** :
   - Type : **Sortie**
   - Entrepôt source
   - Produit
   - Quantité
   - Destination/Bénéficiaire
   - Motif (distribution, mission, etc.)
   - Document justificatif

3. **Validation** :
   - Stock disponible suffisant
   - Autorisation si quantité > seuil
   - Document obligatoire pour > 100 unités

4. **Enregistrer**

⚠️ **Alerte** :
- Si stock < seuil minimum → notification
- Si rupture de stock → alerte urgente

### 7.5 Transfert Entre Entrepôts

1. **Cliquer sur "🔄 Transfert"**

2. **Formulaire** :
   - Entrepôt source
   - Entrepôt destination
   - Produit
   - Quantité
   - Motif (redistribution, équilibrage)
   - Document de transfert

3. **Validation** :
   - Stock source suffisant
   - Capacité destination suffisante
   - Entrepôts actifs

4. **Enregistrer**

✅ **Résultat** :
- 2 mouvements créés (sortie + entrée)
- Stock source diminué
- Stock destination augmenté
- Les 2 responsables notifiés

### 7.6 Recherche et Filtres

**Barre de recherche** :
- Par nom de produit
- Par référence
- Par entrepôt

**Filtres avancés** :
- Type de mouvement
- Entrepôt
- Catégorie de stock
- Période (date début - date fin)
- Utilisateur créateur

**Tri** :
- Plus récent
- Plus ancien
- Par quantité
- Par valeur

### 7.7 Alertes de Stock

**Seuils configurables** :
- 🟢 **Normal** : > seuil minimum
- 🟠 **Attention** : < seuil minimum
- 🔴 **Critique** : Rupture de stock

**Notifications** :
- Email au responsable
- Alerte dans le dashboard
- SMS si urgence (optionnel)

---

## 8. GESTION DU PERSONNEL

### 8.1 Fiches de Personnel

**Accès** : Menu > Personnel

**Liste** :
| Photo | Nom | Matricule | Poste | Service | Actions |
|-------|-----|-----------|-------|---------|---------|
| 👤 | Awa Diop | EMP-001 | DRH | RH | 👁️ ✏️ 📄 |

### 8.2 Créer une Fiche

1. **Cliquer sur "➕ Nouveau Personnel"**

2. **État Civil** :
   - Nom complet
   - Date de naissance
   - Lieu de naissance
   - Sexe
   - Situation familiale
   - Nombre d'enfants

3. **Contact** :
   - Téléphone
   - Email professionnel
   - Adresse

4. **Professionnel** :
   - Matricule *(auto-généré)*
   - Poste/Fonction
   - Service/Département
   - Date d'embauche
   - Type de contrat (CDI, CDD, Stage)
   - Salaire (confidentiel)

5. **Documents** :
   - Photo d'identité *(JPG, PNG, max 2MB)*
   - CV (PDF)
   - Diplômes
   - Contrat signé

6. **Enregistrer**

### 8.3 Générer la Fiche PDF

1. **Ouvrir la fiche**
2. **Cliquer sur "📄 Générer PDF"**

**Contenu du PDF** :
- Photo officielle
- Informations complètes
- Code QR (matricule)
- Signature digitale
- Logo CSAR

**Utilisation** :
- Badge d'employé
- Archives RH
- Dossier personnel

### 8.4 Bulletins de Paie

1. **Ouvrir la fiche personnel**
2. **Onglet "Bulletins de Paie"**
3. **Cliquer sur "➕ Nouveau Bulletin"**

**Informations** :
- Mois
- Salaire de base
- Primes
- Déductions
- Net à payer

4. **Générer le PDF**
5. **Envoyer par email** (optionnel)

### 8.5 Gestion des Congés

1. **Fiche personnel > Onglet "Congés"**
2. **Nouveau congé** :
   - Type (annuel, maladie, maternité, etc.)
   - Date début
   - Date fin
   - Nombre de jours
   - Motif

3. **Statut** :
   - 🟠 En attente
   - 🟢 Approuvé
   - 🔴 Rejeté

4. **Validation DRH**

---

## 9. COMMUNICATION

### 9.1 Messages Internes

**Accès** : Menu > Messages

**Boîte de réception** :
- Messages non lus (gras)
- Émetteur
- Objet
- Date

**Actions** :
- Lire
- Répondre
- Marquer comme lu
- Supprimer

### 9.2 Envoi de Message

1. **Cliquer sur "✉️ Nouveau Message"**
2. **Destinataires** :
   - Tous les utilisateurs
   - Par rôle (tous les admins, etc.)
   - Utilisateur spécifique
3. **Objet**
4. **Message**
5. **Pièce jointe** (optionnel)
6. **Envoyer**

### 9.3 Annonces Générales

1. **Menu > Communication > Annonces**
2. **Créer une annonce**
3. **Titre + Message**
4. **Destinataires** (tous ou ciblés)
5. **Publier**

✅ **Notification** :
- Push in-app
- Email
- SMS (si configuré)

### 9.4 Newsletter

**Abonnés** :
- Liste complète
- Export (CSV, Excel)
- Désabonnements

**Envoyer une newsletter** :
1. Menu > Newsletter
2. Créer une newsletter
3. Objet + Contenu HTML
4. Aperçu
5. Envoyer test
6. Programmer ou envoyer

**Statistiques** :
- Taux d'ouverture
- Taux de clics
- Désabonnements

---

## 10. RAPPORTS ET STATISTIQUES

### 10.1 Rapports Prédéfinis

**Menu > Statistiques**

**Rapports disponibles** :
1. **Rapport Stocks**
   - Inventaire complet
   - Mouvements par période
   - Alertes et ruptures
   - Valeur totale

2. **Rapport Demandes**
   - Demandes par statut
   - Demandes par région
   - Temps de traitement moyen
   - Taux de satisfaction

3. **Rapport RH**
   - Effectif total
   - Par service
   - Masse salariale
   - Taux de présence

4. **Rapport Activité**
   - Connexions
   - Actions effectuées
   - Utilisateurs actifs
   - Performance système

### 10.2 Générer un Rapport

1. **Choisir le type de rapport**
2. **Sélectionner la période** :
   - Aujourd'hui
   - 7 derniers jours
   - 30 derniers jours
   - Mois en cours
   - Année en cours
   - Personnalisé (date début - date fin)

3. **Filtres** (optionnel) :
   - Par entrepôt
   - Par utilisateur
   - Par catégorie

4. **Format** :
   - 📊 **PDF** : Présentation complète
   - 📈 **Excel** : Données brutes pour analyse
   - 📄 **CSV** : Import dans d'autres outils

5. **Cliquer sur "Générer"**

**Durée** : 5-30 secondes selon la taille

### 10.3 Graphiques Personnalisés

1. **Menu > Statistiques > Graphiques**
2. **Choisir le type** :
   - Ligne (évolution)
   - Barres (comparaison)
   - Donut (répartition)
   - Aires (cumul)

3. **Données** :
   - Métrique à afficher
   - Période
   - Groupement

4. **Aperçu**
5. **Exporter** (image PNG ou PDF)

---

## 11. FAQ ET DÉPANNAGE

### 11.1 Questions Fréquentes

**Q1 : J'ai oublié mon mot de passe**
**R :** Cliquez sur "Mot de passe oublié ?" sur la page de login. Un lien de réinitialisation sera envoyé par email.

**Q2 : Mon compte est bloqué**
**R :** Attendez 15 minutes ou contactez un administrateur pour déblocage immédiat.

**Q3 : Je ne vois pas certains menus**
**R :** Vérifiez votre rôle. Seuls les admins ont accès complet. Contactez la DRH pour changement de rôle.

**Q4 : Une erreur "403 Forbidden" apparaît**
**R :** Vous n'avez pas les permissions pour cette action. Vérifiez votre rôle ou contactez un admin.

**Q5 : Les données ne se chargent pas**
**R :** 
1. Rafraîchissez la page (F5)
2. Videz le cache (Ctrl+Shift+Del)
3. Vérifiez votre connexion internet
4. Contactez le support si problème persiste

**Q6 : Comment exporter toutes les demandes ?**
**R :** Menu > Demandes > Bouton "Exporter" > Choisir CSV ou Excel > Télécharger.

**Q7 : Puis-je annuler un mouvement de stock ?**
**R :** Non, pour l'audit. Créez un mouvement inverse (ajustement) avec justification.

**Q8 : Comment ajouter un nouveau type de produit ?**
**R :** Contactez un administrateur. Seuls les admins peuvent créer de nouveaux types.

**Q9 : Les notifications ne s'affichent pas**
**R :** 
1. Cliquez sur la cloche 🔔
2. Vérifiez vos paramètres de notification
3. Autorisez les notifications dans votre navigateur

**Q10 : Comment changer ma photo de profil ?**
**R :** Menu > Profil > "Changer la photo" > Upload (max 2MB, JPG/PNG).

### 11.2 Codes d'Erreur

| Code | Signification | Solution |
|------|---------------|----------|
| 401 | Non authentifié | Reconnectez-vous |
| 403 | Accès refusé | Vérifiez vos permissions |
| 404 | Page introuvable | Vérifiez l'URL |
| 419 | Session expirée | Rafraîchissez (F5) |
| 500 | Erreur serveur | Contactez le support |

### 11.3 Support

**Niveaux de support** :

**Niveau 1 - Auto-assistance** :
- Guide utilisateur (ce document)
- FAQ en ligne
- Vidéos tutoriels

**Niveau 2 - Support IT** :
- Email : support@csar.sn
- Téléphone : +221 XX XXX XX XX
- Horaires : Lun-Ven 8h-17h

**Niveau 3 - Urgent** :
- Hotline : +221 XX XXX XX XX
- Disponible 24/7 pour urgences critiques

**Informations à fournir** :
1. Votre nom et rôle
2. Description du problème
3. Étapes pour reproduire
4. Captures d'écran si possible
5. Message d'erreur exact

---

## ANNEXES

### A. Raccourcis Clavier

| Raccourci | Action |
|-----------|--------|
| Ctrl + S | Enregistrer |
| Ctrl + F | Rechercher |
| Esc | Fermer modal |
| F5 | Rafraîchir |
| Ctrl + P | Imprimer |

### B. Formats de Fichiers Acceptés

| Type | Extensions | Taille Max |
|------|------------|------------|
| Images | JPG, PNG, GIF | 5 MB |
| Documents | PDF, DOC, DOCX | 10 MB |
| Feuilles | XLS, XLSX, CSV | 10 MB |

### C. Navigateurs Supportés

- ✅ Google Chrome 90+
- ✅ Mozilla Firefox 88+
- ✅ Microsoft Edge 90+
- ✅ Safari 14+
- ❌ Internet Explorer (non supporté)

---

**Version** : 1.0  
**Dernière mise à jour** : 24/10/2025  
**Contact Support** : support@csar.sn

---

© 2025 CSAR - Guide Utilisateur Confidentiel











































