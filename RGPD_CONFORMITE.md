# 🛡️ CONFORMITÉ RGPD - PLATEFORME CSAR

**Date** : 24 Octobre 2025  
**Version** : 1.0  
**Responsable DPO** : À désigner

---

## 📋 SOMMAIRE

1. [Introduction](#1-introduction)
2. [Registre des Traitements](#2-registre-des-traitements)
3. [Droits des Personnes](#3-droits-des-personnes)
4. [Mesures de Sécurité](#4-mesures-de-sécurité)
5. [Politique de Confidentialité](#5-politique-de-confidentialité)
6. [Procédures](#6-procédures)
7. [Formation](#7-formation)

---

## 1. INTRODUCTION

### 1.1 Contexte

La plateforme CSAR traite des données personnelles et doit se conformer au Règlement Général sur la Protection des Données (RGPD) ainsi qu'à la loi sénégalaise sur les données personnelles.

### 1.2 Responsable du Traitement

**Organisme** : Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)  
**Adresse** : [Adresse complète]  
**Email** : contact@csar.sn  
**Téléphone** : +221 XX XXX XX XX

### 1.3 Délégué à la Protection des Données (DPO)

**Nom** : [À désigner]  
**Email** : dpo@csar.sn  
**Rôle** : Supervision de la conformité RGPD

---

## 2. REGISTRE DES TRAITEMENTS

### 2.1 Traitement 1 : Gestion des Utilisateurs Admin

**Finalité** : Authentification et autorisation des utilisateurs de la plateforme admin

**Base légale** : Exécution d'une mission d'intérêt public

**Données collectées** :
- Nom et prénom
- Email professionnel
- Numéro de téléphone
- Rôle et permissions
- Photo de profil (optionnelle)
- Logs de connexion (IP, date/heure)

**Durée de conservation** :
- Données actives : Pendant la durée du contrat
- Données inactives : 3 mois après fin de contrat
- Logs : 12 mois

**Destinataires** :
- Administrateurs système
- DPO
- Hébergeur (avec contrat de sous-traitance)

**Mesures de sécurité** :
- Chiffrement des mots de passe (bcrypt)
- HTTPS obligatoire
- Contrôle d'accès par rôle
- Logs d'audit

---

### 2.2 Traitement 2 : Gestion du Personnel

**Finalité** : Gestion RH, paie, administration du personnel

**Base légale** : 
- Exécution du contrat de travail
- Obligation légale (paie, déclarations sociales)

**Données collectées** :
- État civil complet
- Coordonnées
- Numéro de sécurité sociale
- Informations bancaires
- Diplômes et certifications
- Photo d'identité
- Données de paie

**Catégories particulières** :
- Données de santé (arrêts maladie) - avec consentement

**Durée de conservation** :
- Dossier actif : Durée du contrat
- Bulletins de paie : 5 ans
- Dossier archivé : 5 ans après départ

**Destinataires** :
- Service RH
- Service comptabilité
- Organismes sociaux
- Banque (pour virements)

**Mesures de sécurité** :
- Accès restreint (DRH uniquement)
- Chiffrement des données sensibles
- Sauvegarde chiffrée
- Contrôle d'accès strict

---

### 2.3 Traitement 3 : Demandes Citoyennes

**Finalité** : Traitement des demandes d'assistance, suivi, réponse

**Base légale** : Exécution d'une mission d'intérêt public

**Données collectées** :
- Nom et prénom
- Email
- Téléphone
- Région
- Nature de la demande
- Pièces jointes (optionnelles)

**Durée de conservation** :
- Demandes actives : Jusqu'à traitement
- Demandes traitées : 2 ans
- Statistiques anonymisées : Illimitée

**Destinataires** :
- Agents traitants
- Responsables admin
- DG (rapports anonymisés)

**Mesures de sécurité** :
- Accès selon rôle
- Pseudonymisation pour stats
- Chiffrement en transit et au repos

---

### 2.4 Traitement 4 : Newsletter

**Finalité** : Communication institutionnelle, information

**Base légale** : Consentement explicite

**Données collectées** :
- Email
- Nom et prénom (optionnels)
- Date d'inscription
- Historique d'ouverture (si tracking)

**Durée de conservation** :
- Abonnés actifs : Jusqu'à désabonnement
- Désabonnés : 6 mois (liste de suppression)

**Destinataires** :
- Service communication
- Prestataire newsletter (Mailchimp/SendGrid/Brevo)

**Mesures de sécurité** :
- Double opt-in (confirmation par email)
- Lien de désabonnement dans chaque email
- Contrat de sous-traitance avec prestataire

---

### 2.5 Traitement 5 : Messages de Contact

**Finalité** : Réponse aux demandes de contact

**Base légale** : Intérêt légitime

**Données collectées** :
- Nom complet
- Email
- Téléphone (optionnel)
- Sujet et message

**Durée de conservation** :
- Messages actifs : 3 mois
- Messages traités : 1 an
- Suppression automatique après

**Destinataires** :
- Service communication
- Administrateurs

---

## 3. DROITS DES PERSONNES

### 3.1 Droit d'Accès

**Procédure** :
1. Demande par email à dpo@csar.sn
2. Vérification d'identité (pièce d'identité)
3. Réponse sous 30 jours
4. Export des données au format électronique

**Implémentation technique** :
```php
// Route dédiée
Route::get('/rgpd/export-my-data', [RGPDController::class, 'exportUserData']);

// Méthode
public function exportUserData(Request $request)
{
    $user = auth()->user();
    
    $data = [
        'personal_info' => $user->only(['name', 'email', 'phone']),
        'account_info' => [...],
        'activity_logs' => $user->auditLogs()->get(),
        'messages' => $user->messages()->get(),
        // etc.
    ];
    
    return response()->json($data)->download("mes-donnees-csar-{$user->id}.json");
}
```

### 3.2 Droit de Rectification

**Procédure** :
1. Connexion au compte
2. Menu Profil > Modifier
3. Correction des données
4. Validation

**Ou** :
1. Email à dpo@csar.sn
2. Indication des données à corriger
3. Correction sous 30 jours

### 3.3 Droit à l'Effacement ("Droit à l'oubli")

**Conditions** :
- Retrait du consentement (newsletter)
- Données non nécessaires
- Opposition au traitement
- Fin de contrat (personnel)

**Exceptions** :
- Obligation légale de conservation (paie: 5 ans)
- Intérêt public (archives)

**Procédure** :
1. Demande à dpo@csar.sn
2. Vérification des conditions
3. Effacement sous 30 jours
4. Confirmation par email

**Implémentation technique** :
```php
Route::post('/rgpd/delete-my-account', [RGPDController::class, 'deleteAccount']);

public function deleteAccount(Request $request)
{
    $user = auth()->user();
    
    // Vérifier qu'il n'y a pas d'obligation légale
    if ($this->canDelete($user)) {
        // Anonymiser ou supprimer
        $user->anonymize(); // ou $user->delete();
        
        // Log de l'action
        Log::info("Compte supprimé (RGPD)", ['user_id' => $user->id]);
        
        return redirect('/')->with('success', 'Compte supprimé');
    }
    
    return back()->with('error', 'Suppression impossible (obligation légale)');
}
```

### 3.4 Droit à la Portabilité

**Format** : JSON, CSV, Excel

**Procédure** :
1. Demande d'export
2. Génération du fichier
3. Téléchargement sécurisé
4. Lien valide 7 jours

### 3.5 Droit d'Opposition

**Opposition au traitement** :
- Formulaire de contact
- Email à dpo@csar.sn
- Réponse sous 30 jours

**Opposition marketing** :
- Lien de désabonnement newsletter
- Immediate et automatique

### 3.6 Droit de Limitation

**Contextes** :
- Contestation de l'exactitude
- Traitement illicite
- Données non nécessaires mais conservées pour défense en justice

**Procédure** :
1. Demande motivée
2. Gel des données
3. Traitement limité

---

## 4. MESURES DE SÉCURITÉ

### 4.1 Mesures Techniques

✅ **Chiffrement** :
- HTTPS/TLS 1.3 obligatoire
- Mots de passe hashés (bcrypt)
- Données sensibles chiffrées en base

✅ **Contrôle d'Accès** :
- Authentification forte
- Rôles et permissions granulaires
- Principe du moindre privilège
- Logs d'accès

✅ **Sauvegardes** :
- Quotidiennes automatiques
- Chiffrées
- Stockage distant
- Tests de restauration mensuels

✅ **Monitoring** :
- Détection d'intrusion
- Alertes automatiques
- Logs d'audit complets

### 4.2 Mesures Organisationnelles

✅ **Formation** :
- Sensibilisation annuelle du personnel
- Formation RGPD obligatoire pour RH et admin

✅ **Procédures** :
- Charte informatique signée
- Procédures de sécurité documentées
- Plan de gestion des incidents

✅ **Contrats** :
- Clauses RGPD avec sous-traitants
- Accords de confidentialité

---

## 5. POLITIQUE DE CONFIDENTIALITÉ

### 5.1 Contenu Obligatoire

La politique de confidentialité doit contenir :

1. ✅ Identité du responsable de traitement
2. ✅ Coordonnées du DPO
3. ✅ Finalités des traitements
4. ✅ Base légale de chaque traitement
5. ✅ Destinataires des données
6. ✅ Durées de conservation
7. ✅ Droits des personnes et modalités d'exercice
8. ✅ Droit de réclamation auprès de la CNIL/CDP
9. ✅ Transferts hors UE (si applicable)
10. ✅ Mesures de sécurité

### 5.2 Affichage

- ✅ Lien en footer de toutes les pages
- ✅ Acceptation explicite avant collecte
- ✅ Mise à jour datée
- ✅ Langue accessible (français)

### 5.3 Template

Voir fichier : `resources/views/legal/privacy-policy.blade.php`

---

## 6. PROCÉDURES

### 6.1 Procédure de Gestion des Demandes RGPD

**Étape 1 - Réception** :
- Email dédié : dpo@csar.sn
- Formulaire web (à créer)
- Courrier postal

**Étape 2 - Vérification d'identité** :
- Demande de pièce d'identité
- Vérification email/téléphone
- Sécurisation de l'échange

**Étape 3 - Traitement** :
- Analyse de la demande
- Vérification des conditions
- Exécution (export, correction, suppression)

**Étape 4 - Réponse** :
- Délai maximum : 30 jours (prorogeable 2 mois si complexe)
- Confirmation par email
- Gratuité (sauf demandes abusives)

**Étape 5 - Archivage** :
- Conservation de la demande : 3 ans
- Preuve de conformité

### 6.2 Procédure de Violation de Données

**Définition** : Incident de sécurité entraînant destruction, perte, altération, divulgation non autorisée.

**Délai de notification** :
- À la CDP (Commission de Protection des Données - Sénégal) : 72h
- Aux personnes concernées : Sans délai si risque élevé

**Procédure** :

**1. Détection** :
- Monitoring automatique
- Alerte manuelle
- Notification par tiers

**2. Évaluation** :
- Nature de la violation
- Données concernées
- Nombre de personnes
- Niveau de risque

**3. Containment** :
- Isolation du système
- Arrêt de la fuite
- Mesures correctives

**4. Notification** :
- Si risque : notification CDP sous 72h
- Si risque élevé : notification personnes concernées
- Informations à fournir :
  - Nature de la violation
  - Données concernées
  - Conséquences probables
  - Mesures prises/envisagées

**5. Documentation** :
- Registre des violations
- Actions menées
- Leçons apprises

**6. Prévention** :
- Analyse des causes
- Mise à jour des procédures
- Formation si nécessaire

---

## 7. FORMATION

### 7.1 Formation Initiale (Obligatoire)

**Public** : Tous les utilisateurs accédant à des données personnelles

**Durée** : 2 heures

**Contenu** :
- Principes du RGPD
- Droits des personnes
- Sécurité des données
- Bonnes pratiques
- Procédures CSAR

### 7.2 Formation Continue

**Fréquence** : Annuelle

**Format** :
- E-learning
- Webinaire
- Documentation

### 7.3 Sensibilisation

**Mensuelle** :
- Newsletter sécurité
- Tips RGPD
- Cas pratiques

---

## 8. DOCUMENTS ANNEXES

### 8.1 Formulaires

- ✅ Formulaire de demande d'exercice des droits
- ✅ Formulaire de consentement (newsletter)
- ✅ Formulaire de réclamation

### 8.2 Modèles

- ✅ Modèle de réponse aux demandes RGPD
- ✅ Modèle de notification de violation
- ✅ Modèle de contrat de sous-traitance

### 8.3 Registres

- ✅ Registre des traitements
- ✅ Registre des violations
- ✅ Registre des demandes RGPD

---

## 9. CONTACTS UTILES

### 9.1 Interne

- **DPO CSAR** : dpo@csar.sn
- **Responsable Sécurité** : security@csar.sn
- **Support Technique** : support@csar.sn

### 9.2 Externe

- **CDP (Commission de Protection des Données - Sénégal)**
  - Site : https://cdp.sn
  - Email : contact@cdp.sn
  - Téléphone : +221 XX XXX XX XX

- **CNIL (France - pour référence)**
  - Site : https://www.cnil.fr
  - Téléphone : +33 1 53 73 22 22

---

## 10. MISE À JOUR

**Historique des versions** :

| Version | Date | Modifications |
|---------|------|---------------|
| 1.0 | 24/10/2025 | Version initiale |

**Prochaine révision** : 24/10/2026 (annuelle minimum)

---

**Document validé par** : [Nom DPO]  
**Date de validation** : 24/10/2025  
**Statut** : Document de conformité officiel

---

© 2025 CSAR - Document confidentiel - Conformité RGPD






































