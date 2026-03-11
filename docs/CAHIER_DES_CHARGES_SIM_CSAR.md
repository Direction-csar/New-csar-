# CAHIER DES CHARGES
# SYSTÈME D'INFORMATION SUR LES MARCHÉS (SIM)
# COMMISSARIAT À LA SÉCURITÉ ALIMENTAIRE ET À LA RÉSILIENCE (CSAR)

**République du Sénégal**
Un Peuple – Un But – Une Foi

**Ministère de la Famille, de l'Action Sociale et des Solidarités**
Commissariat à la Sécurité Alimentaire et à la Résilience

---

**Version** : 1.0
**Date** : Février 2026

---

## TABLE DES MATIÈRES

1. Contexte
2. Objectif général
3. Objectifs spécifiques
4. Public visé
5. Gouvernance de la plateforme
6. Rôles et permissions
7. Structure générale de la plateforme
8. Module public SIM
9. Module collecte terrain
10. Suivi en temps réel des collecteurs
11. Module validation superviseur
12. Module administration centrale
13. Gestion des marchés
14. Gestion des produits suivis
15. Données collectées
16. Calculs et indicateurs automatiques
17. Graphiques et visualisation
18. Bulletins et publications
19. Carte des marchés
20. Gestion de l'accès aux données
21. Sécurité et traçabilité
22. Exigences techniques
23. Structure de base de données
24. Interfaces à développer
25. Ordre de développement
26. Annexes

---

## 1. CONTEXTE

Le Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR) est un établissement public à caractère administratif, doté de l'autonomie administrative et financière, créé par le Décret N°2024-11 du 05 janvier 2024.

Le CSAR dispose du Système d'Information sur les Marchés (SIM), un outil de suivi et de collecte d'informations sur les marchés agropastoraux mis en place depuis juillet 1987 par le Programme de Sécurité Alimentaire (PSA) de la coopération allemande (GTZ).

Le SIM assure la collecte hebdomadaire des prix et des disponibilités des produits alimentaires sur les marchés du Sénégal. Les données collectées alimentent des bulletins d'information, des analyses de tendance et des alertes précoces en matière de sécurité alimentaire.

Le CSAR souhaite digitaliser et intégrer le SIM dans sa plateforme web existante afin de moderniser la collecte, centraliser les données, automatiser les analyses et contrôler la diffusion des informations.

La plateforme doit s'inspirer du modèle du SIMA Niger (https://simaniger.net/) tout en l'adaptant aux besoins spécifiques du CSAR, notamment en matière de sécurité des données, de suivi en temps réel des collecteurs et de contrôle administratif centralisé.

---

## 2. OBJECTIF GÉNÉRAL

Mettre en place une plateforme SIM intégrée au site CSAR permettant de :

- présenter publiquement le SIM et ses missions
- gérer les marchés et les produits suivis
- permettre aux collecteurs de saisir les données terrain de manière digitale
- suivre les collecteurs en temps réel pendant les opérations de collecte
- centraliser et valider les données collectées
- calculer automatiquement les indicateurs de prix et de disponibilité
- produire des graphiques, des courbes et des tableaux de bord
- publier des bulletins et des rapports validés
- gérer les demandes externes d'accès aux données
- donner à l'administrateur un contrôle total sur la plateforme

---

## 3. OBJECTIFS SPÉCIFIQUES

La solution devra permettre de :

1. Digitaliser la collecte des données des marchés en remplaçant les fiches papier par des formulaires numériques.
2. Améliorer la fiabilité, la rapidité et la traçabilité de la collecte.
3. Suivre l'activité des collecteurs en temps réel (présence, progression, transmission).
4. Centraliser toutes les données dans une base de données unique et sécurisée.
5. Produire automatiquement des statistiques de prix et de disponibilités.
6. Générer des graphiques d'évolution mensuelle, annuelle et régionale.
7. Publier des bulletins SIM validés et contrôlés par l'administration.
8. Gérer les demandes externes d'accès aux données de manière formelle.
9. Donner à l'administrateur le contrôle total sur les accès, les publications et les validations.
10. Assurer la non-diffusion des données brutes ou non validées au public.

---

## 4. PUBLIC VISÉ

La plateforme doit servir à :

- **Grand public** : consulter les informations publiées sur le SIM.
- **Producteurs et commerçants** : consulter les prix validés et les tendances.
- **Décideurs publics** : s'appuyer sur les analyses pour la prise de décision.
- **Partenaires techniques et financiers** : accéder aux données sur autorisation.
- **Collecteurs (enquêteurs)** : saisir les données terrain.
- **Superviseurs régionaux** : contrôler et valider les collectes.
- **Administrateur CSAR** : gérer la totalité de la plateforme.

---

## 5. GOUVERNANCE DE LA PLATEFORME

La plateforme SIM/CSAR est administrée de manière centralisée par l'administrateur du système.

L'administrateur dispose d'un contrôle total sur :

- la gestion des utilisateurs (collecteurs, superviseurs)
- la gestion des marchés et des produits
- la validation finale des données collectées
- le lancement des calculs statistiques
- la publication des prix, graphiques et bulletins
- la gestion des demandes d'accès aux données
- l'autorisation ou le refus d'accès aux données détaillées
- la gestion de tous les contenus publics du SIM

Les collecteurs et superviseurs interviennent dans le processus opérationnel, mais la gouvernance, la validation finale et la diffusion des données relèvent exclusivement de l'administration centrale.

---

## 6. RÔLES ET PERMISSIONS

### 6.1 Administrateur (Admin CSAR)

L'administrateur est le gestionnaire principal de toute la plateforme. Il peut :

- créer, modifier, activer et désactiver les comptes utilisateurs
- créer et gérer les marchés
- créer et gérer les produits suivis
- affecter les collecteurs aux marchés
- affecter les superviseurs aux régions
- suivre tous les collecteurs en temps réel sur l'ensemble du territoire
- consulter, corriger, valider ou rejeter toutes les collectes
- lancer les calculs statistiques (moyennes, variations, tendances)
- publier les prix validés sur la partie publique
- publier les graphiques sur la partie publique
- créer et publier les bulletins
- traiter les demandes d'accès aux données
- accorder, limiter ou refuser l'accès aux données
- gérer la carte des marchés
- gérer tous les paramètres de la plateforme

### 6.2 Superviseur régional

Le superviseur appuie l'administration pour le contrôle régional. Il peut :

- suivre en temps réel les collecteurs de sa zone
- consulter les collectes reçues de sa zone
- vérifier la cohérence des données
- corriger ou demander correction au collecteur
- valider les collectes avant transmission à l'admin
- détecter les retards de collecte
- signaler les anomalies

Le superviseur ne peut pas :

- publier des données ou des bulletins
- accorder des accès externes aux données
- gérer les marchés ou les produits
- modifier les paramètres généraux de la plateforme

### 6.3 Collecteur (enquêteur)

Le collecteur saisit les données terrain. Il peut :

- se connecter à son espace
- consulter les marchés qui lui sont affectés
- lancer une session de collecte
- saisir les données de prix, de quantités, de disponibilités et de provenance
- ajouter des observations
- transmettre la fiche de collecte
- mettre à jour son statut (en déplacement, arrivé, en cours, terminé)
- consulter l'historique de ses collectes
- voir si sa collecte a été validée ou rejetée

Le collecteur ne peut pas :

- voir les données des autres collecteurs
- publier des données
- gérer les marchés ou les produits
- accéder aux données nationales agrégées

### 6.4 Demandeur de données (externe)

Le demandeur externe peut :

- remplir un formulaire de demande d'accès aux données
- préciser l'objet, la période, le type de données et l'organisme
- attendre la décision de l'administrateur

Le demandeur ne peut pas :

- accéder directement aux données sans autorisation de l'admin
- télécharger des données brutes

### 6.5 Public (visiteur)

Le visiteur peut :

- consulter les pages publiques du SIM
- voir les prix validés et publiés
- consulter les graphiques publiés
- télécharger les bulletins publiés
- voir la carte des marchés
- soumettre une demande d'accès aux données

Le visiteur ne peut pas :

- voir les données brutes
- voir les fiches de collecte
- voir les données non validées
- accéder à l'espace interne

---

## 7. STRUCTURE GÉNÉRALE DE LA PLATEFORME

### 7.1 Menu principal public CSAR

```
Accueil
À propos
Actualités
SIM
Institution
Partenaires
Contact
```

### 7.2 Sous-espace public du SIM

Quand l'utilisateur clique sur « SIM » dans le menu principal, il accède à :

```
SIM
├── Présentation SIM
├── Historique
├── Missions
├── Marchés suivis
├── Prix validés
├── Graphiques
├── Bulletins
├── Carte des marchés
└── Demander accès aux données
```

### 7.3 Espace interne sécurisé

Accessible uniquement après authentification :

```
Espace interne SIM
├── Tableau de bord
├── Collecteurs
│   ├── Nouvelle collecte
│   ├── Mes collectes
│   └── Mon statut
├── Superviseurs
│   ├── Collectes de ma zone
│   ├── Suivi temps réel
│   └── Validation
└── Admin
    ├── Tableau de bord général
    ├── Suivi temps réel (national)
    ├── Marchés
    ├── Produits
    ├── Utilisateurs et rôles
    ├── Collectes (toutes)
    ├── Validation finale
    ├── Statistiques et calculs
    ├── Graphiques
    ├── Bulletins
    ├── Demandes d'accès aux données
    └── Paramètres
```

---

## 8. MODULE PUBLIC SIM

### 8.1 Présentation SIM

Contenu à afficher :

- contexte de création du SIM
- intégration au CSAR
- objectifs stratégiques
- dispositif opérationnel (équipe de coordination, superviseurs, enquêteurs)
- résumé méthodologique

### 8.2 Historique

Contenu à afficher :

- création du SIM en juillet 1987 par le PSA/GTZ
- objectif initial : accompagner la libéralisation des prix
- préparation : sélection des marchés, formation du dispositif
- démarrage de la collecte en janvier 1988
- évolution du dispositif jusqu'à aujourd'hui
- création du CSAR en 2024

### 8.3 Missions

Contenu à afficher :

- transparence du marché agricole national
- régulation (transferts zones excédentaires vers déficitaires)
- réactivité (information en temps réel sur les cours)
- aide à la décision (éclairage des autorités et partenaires)
- collecte des informations (offres, stocks, prix, flux)
- gestion de la base de données
- diffusion des bulletins
- coordination et études ponctuelles

### 8.4 Marchés suivis

Contenu à afficher :

- liste des marchés suivis par région et département
- type de marché (rural collecte, rural consommation, urbain, frontalier)
- jour du marché
- nombre de marchés par région
- filtrage par région

### 8.5 Prix validés

Contenu à afficher :

- prix moyens par produit (validés et publiés par l'admin)
- filtrage par période (mois, année)
- filtrage par catégorie de produit
- filtrage par région si autorisé
- tableau de prix

Les prix affichés sont uniquement ceux que l'admin a validés et publiés.

### 8.6 Graphiques

Contenu à afficher :

- évolution mensuelle des prix moyens (courbe multi-produits)
- comparaison annuelle (barres : année N vs N-1 vs moyenne 5 ans)
- sélection d'année
- sélection de produits

Les graphiques sont générés uniquement à partir des données validées.

### 8.7 Bulletins

Contenu à afficher :

- liste des bulletins publiés par l'admin
- type : hebdomadaire, mensuel, spécial
- titre, résumé, date de publication
- téléchargement du document PDF
- filtrage par type et période

### 8.8 Carte des marchés

Contenu à afficher :

- carte interactive du Sénégal
- marqueurs pour chaque marché suivi
- distinction par type de marché (couleur ou icône)
- clic sur un marché : fiche détaillée (nom, région, type, jour)

### 8.9 Demande d'accès aux données

Contenu à afficher :

- formulaire de demande
- champs : nom, organisme, email, objet de la demande, type de données souhaitées, période, usage prévu
- soumission de la demande
- message de confirmation
- la demande est traitée par l'admin

---

## 9. MODULE COLLECTE TERRAIN

### 9.1 Authentification collecteur

Le collecteur se connecte avec :

- identifiant (email ou matricule)
- mot de passe

### 9.2 Tableau de bord collecteur

Le collecteur voit :

- ses marchés affectés
- les collectes en cours
- les collectes terminées
- les collectes validées / rejetées
- les alertes éventuelles

### 9.3 Fiche de collecte numérique

La fiche de collecte doit reproduire la fiche papier officielle du SIM/CSAR.

Champs d'en-tête :

- marché (sélection parmi les marchés affectés)
- date de collecte
- nom du collecteur (automatique)
- jour du marché

Champs par produit (une ligne par produit) :

- désignation du produit (pré-remplie selon la liste officielle)
- quantité relevée
- prix producteur (FCFA)
- prix détail (FCFA)
- prix demi-gros (FCFA)
- provenance (locale, importée, mixte)

Champ de bas de fiche :

- observations générales

### 9.4 Liste des produits dans la fiche

La fiche doit contenir les 50 produits officiels validés :

**Céréales** : Fonio, Maïs jaune local, Maïs importé, Mil sanio, Mil souna, Riz local décortiqué, Riz importé ordinaire, Riz blanc importé parfumé, Riz entier importé, Sorgho local, Sorgho importé.

**Tubercules** : Manioc, Patate douce, Pomme de terre locale, Pomme de terre importée.

**Légumineuses** : Arachide coque, Arachide décortiquée, Niébé 1ère qualité, Niébé 2ème qualité.

**Légumes** : Aubergines, Carotte, Oignon local, Oignon importé, Tomate.

**Fruits** : Banane, Mangues, Orange, Pastèques, Pomme rouge/vert.

**Viandes** : Viande de bœuf, Viande de caprin, Viande de mouton.

**Têtes** : Poulet, Tête bovin, Tête ovin, Tête caprin.

**Produits halieutiques** : Chinchard frais (Diay), Barracuda (Seud), Sardinella frais (Yaboye), Poisson mer fumé/séché.

**Œufs** : Œuf de poule locale, Œuf de poule pondeuse.

**Huiles et graisses** : Huile d'arachide (Diw ségal), Huile d'arachide raffinée, Huile végétale, Huile de tournesol, Huile de palme.

**Produits forestiers** : Pain de singe, Sidemme (jujube), Anacarde.

### 9.5 Transmission de la collecte

Le collecteur doit pouvoir :

- enregistrer un brouillon (sauvegarde locale avant envoi)
- soumettre la fiche complétée
- recevoir une confirmation de réception
- voir si la collecte a été validée ou rejetée par le superviseur ou l'admin

### 9.6 Mode mobile

La fiche de collecte doit être utilisable sur :

- smartphone
- tablette
- ordinateur portable

L'interface doit être responsive et adaptée aux conditions de terrain.

---

## 10. SUIVI EN TEMPS RÉEL DES COLLECTEURS

### 10.1 Objectif

Permettre au CSAR de savoir en temps réel :

- quel collecteur est en mission
- à quel marché il se trouve
- s'il a démarré sa collecte
- si la collecte est en cours, terminée ou non transmise
- l'heure d'arrivée, de début de saisie et de transmission
- sa position GPS si autorisée
- les collectes en retard ou non effectuées

### 10.2 Statuts de collecte

Pour chaque collecteur, le système doit gérer les statuts suivants :

- **Non démarrée** : le collecteur n'a pas encore commencé
- **En déplacement** : le collecteur est en route vers le marché
- **Arrivé sur marché** : le collecteur est sur place
- **Collecte en cours** : la saisie est en cours
- **Collecte soumise** : la fiche a été transmise
- **Collecte validée** : la fiche a été vérifiée et acceptée
- **Collecte rejetée** : la fiche a été refusée (erreurs, incohérences)

### 10.3 Informations suivies en temps réel

Pour chaque collecteur :

- nom et identifiant
- région et zone d'affectation
- marché assigné
- date de collecte prévue
- statut actuel
- heure de dernière activité
- position GPS (si autorisée par le collecteur)
- niveau de progression de la fiche (nombre de produits saisis / total)

### 10.4 Tableau de bord temps réel

Le système doit proposer une vue temps réel accessible à l'admin et aux superviseurs :

- carte des collecteurs actifs avec marqueurs de statut
- liste des collecteurs connectés avec leur statut
- nombre de collectes en cours / soumises / en retard
- alertes automatiques :
  - collecteur absent (pas de connexion à l'heure prévue)
  - collecte non démarrée après l'heure limite
  - collecte incomplète (produits manquants)
  - données incohérentes (prix anormalement élevés ou bas)
  - transmission tardive

### 10.5 Historique de suivi

Le système doit conserver :

- historique des connexions de chaque collecteur
- historique des positions envoyées
- historique des changements de statut
- historique des transmissions de fiches
- durées de collecte

---

## 11. MODULE VALIDATION SUPERVISEUR

Le superviseur régional doit pouvoir :

- consulter les collectes reçues de sa zone
- voir le détail de chaque fiche
- identifier les écarts anormaux (prix aberrants, données manquantes)
- corriger les données si nécessaire
- demander une correction au collecteur
- rejeter une collecte incohérente
- valider la collecte pour transmission à l'admin
- suivre les retards de transmission
- voir le taux de collecte de sa zone (réalisé vs attendu)

---

## 12. MODULE ADMINISTRATION CENTRALE

L'admin dispose d'un tableau de bord global avec accès à :

### 12.1 Gestion des utilisateurs

- créer des comptes collecteurs
- créer des comptes superviseurs
- activer ou désactiver un compte
- réinitialiser un mot de passe
- affecter un collecteur à un ou plusieurs marchés
- affecter un superviseur à une région

### 12.2 Gestion des marchés

- ajouter un marché
- modifier un marché
- désactiver un marché
- définir : nom, région, département, type, jour, coordonnées GPS, statut

### 12.3 Gestion des produits

- ajouter un produit
- modifier un produit
- désactiver un produit
- définir : nom, catégorie, unité, origine, statut

### 12.4 Gestion des collectes

- consulter toutes les collectes (toutes régions)
- filtrer par date, marché, collecteur, statut
- valider ou rejeter une collecte
- corriger des données si nécessaire

### 12.5 Calculs statistiques

- lancer le calcul des prix moyens par produit
- calculer les moyennes par marché, par région, par période
- calculer les variations mensuelles et annuelles
- calculer les moyennes pluriannuelles
- détecter les tendances haussières et baissières

### 12.6 Publication

- publier les prix validés sur la partie publique
- publier les graphiques sur la partie publique
- créer et publier les bulletins
- retirer une publication

### 12.7 Demandes d'accès aux données

- consulter les demandes reçues
- approuver, limiter ou refuser une demande
- notifier le demandeur de la décision
- fournir un accès limité ou un export contrôlé si approuvé

### 12.8 Paramètres

- configuration générale du SIM
- gestion des catégories de produits
- gestion des régions et départements

---

## 13. GESTION DES MARCHÉS

L'admin gère la liste des marchés suivis.

### 13.1 Champs d'un marché

- nom du marché
- région
- département
- commune (optionnel)
- type de marché (rural collecte, rural consommation, urbain, frontalier, regroupement, transfrontalier)
- jour du marché (lundi, mardi, etc. ou permanent)
- latitude
- longitude
- statut (actif / inactif)
- superviseur responsable
- collecteur(s) affecté(s)

### 13.2 Liste initiale des marchés

La liste initiale comprend 86 marchés couvrant les 46 départements et 14 régions du Sénégal, validée lors de l'atelier de décembre 2025 (voir annexe).

---

## 14. GESTION DES PRODUITS SUIVIS

L'admin gère la liste des produits suivis.

### 14.1 Catégories de produits

- Céréales et produits céréaliers
- Tubercules, racines
- Légumineuses, noix et grains
- Légumes et feuilles vertes
- Fruits
- Viandes
- Têtes (bétail et volaille)
- Poissons, amphibiens et produits de mer
- Œufs
- Huiles et graisses
- Produits forestiers

### 14.2 Champs d'un produit

- nom
- catégorie
- unité de mesure (kg, pièce, litre, etc.)
- type d'origine (local, importé, les deux)
- statut (actif / inactif)

### 14.3 Liste initiale

La liste initiale comprend 50 produits validés lors de l'atelier de décembre 2025 (voir annexe).

---

## 15. DONNÉES COLLECTÉES

### 15.1 Types de données

Pour chaque produit sur chaque marché :

- prix au producteur (FCFA)
- prix de détail / consommateur (FCFA)
- prix de gros ou demi-gros (FCFA)
- quantité relevée
- offre / disponibilité
- stock (marchés urbains)
- provenance (locale, importée, mixte)

### 15.2 Niveaux de collecte par type de marché

| Type de donnée | Marchés ruraux collecte | Marchés ruraux consommation | Marchés urbains | Marchés frontaliers |
|---|---|---|---|---|
| Prix producteur | Oui | Non | Non | Possible |
| Prix détail | Oui | Oui | Oui | Oui |
| Prix gros / demi-gros | Non | Non | Oui | Oui |
| Offres | Oui | Non | Non | Non |
| Stocks | Non | Non | Oui | Non |

### 15.3 Fréquence de collecte

La collecte est hebdomadaire sur tous les marchés suivis.

---

## 16. CALCULS ET INDICATEURS AUTOMATIQUES

Le système doit produire automatiquement les indicateurs suivants à partir des données validées :

- prix moyen par produit (national)
- prix moyen par produit et par marché
- prix moyen par produit et par région
- prix moyen par produit et par mois
- variation mensuelle (mois N vs mois N-1)
- variation annuelle (mois N année A vs même mois année A-1)
- moyenne pluriannuelle (moyenne sur 5 ans du même mois)
- tendance haussière / baissière / stable
- alertes sur fluctuations anormales (seuil configurable par l'admin)
- taux de collecte réalisée vs attendue

---

## 17. GRAPHIQUES ET VISUALISATION

### 17.1 Graphique courbe (évolution mensuelle)

- axe X : mois (janvier à décembre)
- axe Y : prix en FCFA/kg
- une courbe par produit (Mil, Sorgho, Maïs, Riz, Niébé, etc.)
- sélection d'année
- légende avec couleurs distinctes par produit

### 17.2 Graphique barres (comparaison annuelle)

- axe X : produits
- axe Y : prix en FCFA
- barres groupées : année en cours, année précédente, moyenne 5 ans
- légende avec couleurs

### 17.3 Tableaux de bord

- produits les plus chers
- produits en hausse
- produits en baisse
- produits stables
- évolution régionale
- collectes réalisées vs attendues
- collecteurs actifs / inactifs

### 17.4 Règle importante

Les graphiques publics sont générés uniquement à partir des données validées et publiées par l'admin.

Les graphiques internes (admin et superviseur) peuvent afficher des données plus détaillées et non encore publiées.

---

## 18. BULLETINS ET PUBLICATIONS

### 18.1 Création de bulletin

L'admin crée un bulletin avec :

- titre
- type (hebdomadaire, mensuel, spécial)
- période couverte
- résumé
- document PDF joint
- image de couverture (optionnel)
- statut (brouillon, publié, archivé)

### 18.2 Publication

L'admin décide quand publier. Une fois publié, le bulletin est visible sur la partie publique du SIM.

### 18.3 Consultation publique

Le visiteur peut :

- voir la liste des bulletins publiés
- filtrer par type et période
- consulter le détail (titre, résumé, date)
- télécharger le document PDF

---

## 19. CARTE DES MARCHÉS

### 19.1 Affichage

La carte des marchés doit montrer :

- carte du Sénégal
- marqueurs pour chaque marché suivi actif
- couleurs ou icônes différentes par type de marché
- clic sur un marqueur : fiche du marché (nom, région, département, type, jour)

### 19.2 Gestion

L'admin gère les marchés affichés sur la carte. Un marché désactivé n'apparaît pas sur la carte publique.

---

## 20. GESTION DE L'ACCÈS AUX DONNÉES

### 20.1 Principe

Les données détaillées ne sont pas diffusées publiquement. Tout accès aux données détaillées passe par une demande formelle traitée par l'admin.

### 20.2 Processus de demande

1. Le demandeur externe remplit le formulaire de demande.
2. L'admin reçoit et analyse la demande.
3. L'admin décide : refus, accès partiel, accès complet limité dans le temps.
4. Le demandeur est notifié de la décision.
5. Si approuvé, l'admin fournit un export contrôlé ou un accès temporaire.

### 20.3 Champs du formulaire de demande

- nom du demandeur
- organisme
- fonction
- email
- téléphone
- objet de la demande
- type de données souhaitées
- période souhaitée
- usage prévu
- engagement de confidentialité

### 20.4 Types d'accès possibles

- aucun accès
- accès à des statistiques agrégées
- accès à certaines périodes
- accès à certaines régions
- accès à certaines catégories de produits
- export autorisé uniquement par l'admin

---

## 21. SÉCURITÉ ET TRAÇABILITÉ

### 21.1 Authentification

- connexion sécurisée par identifiant et mot de passe
- sessions protégées
- déconnexion automatique après inactivité

### 21.2 Gestion des rôles

- chaque utilisateur a un rôle unique (admin, superviseur, collecteur)
- les permissions sont définies par rôle
- l'admin peut modifier les rôles

### 21.3 Non-diffusion des données brutes

- les données brutes de collecte ne sont jamais visibles publiquement
- seules les données validées et publiées par l'admin sont accessibles au public
- les demandeurs externes n'accèdent qu'aux données autorisées

### 21.4 Traçabilité

Le système doit conserver :

- journal des connexions utilisateurs
- journal des modifications de données
- journal des validations et rejets
- journal des publications et dépublications
- journal des demandes d'accès traitées
- journal des accès accordés

---

## 22. EXIGENCES TECHNIQUES

### 22.1 Technologies recommandées

- Backend : Laravel (PHP)
- Frontend : Blade + Bootstrap ou Tailwind CSS
- Graphiques : Chart.js ou ApexCharts
- Carte interactive : Leaflet.js
- Temps réel : polling AJAX (puis évolution possible vers WebSockets)
- Base de données : MySQL
- Authentification : système de rôles Laravel
- Hébergement : VPS Ubuntu (existant)

### 22.2 Compatibilité

- navigateurs modernes (Chrome, Firefox, Safari, Edge)
- responsive mobile et tablette
- mode terrain adapté (formulaire léger, chargement rapide)

### 22.3 Performance

- temps de chargement acceptable sur connexion mobile
- interface de collecte utilisable hors ligne (sauvegarde locale puis synchronisation)

---

## 23. STRUCTURE DE BASE DE DONNÉES

### 23.1 Tables principales

```
users                     → utilisateurs (admin, superviseur, collecteur)
roles                     → rôles (admin, superviseur, collecteur)
user_roles                → association utilisateur-rôle

regions                   → régions du Sénégal
departements              → départements
marches                   → marchés suivis

categories_produits       → catégories de produits
produits                  → produits suivis

affectations_collecteurs  → affectation collecteur-marché
sessions_collecte         → sessions de travail des collecteurs

collectes                 → fiches de collecte (en-tête)
collecte_lignes           → lignes de collecte (détail par produit)
collecte_positions        → positions GPS des collecteurs
collecte_statuts          → historique des statuts de collecte

validations_collecte      → journal des validations/rejets

prix_valides              → prix moyens validés et publiés
calculs_statistiques      → résultats des calculs

bulletins                 → bulletins créés
bulletin_documents        → fichiers PDF joints

demandes_acces_donnees    → demandes d'accès aux données
autorisations_donnees     → autorisations accordées

logs_activite             → journal d'activité général
```

### 23.2 Tables détaillées

#### `marches`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| nom | string | nom du marché |
| region_id | int | région |
| departement_id | int | département |
| commune | string | commune (optionnel) |
| type_marche | enum | rural_collecte, rural_consommation, urbain, frontalier, regroupement, transfrontalier |
| jour_marche | string | jour ou permanent |
| latitude | decimal | coordonnée GPS |
| longitude | decimal | coordonnée GPS |
| actif | boolean | actif ou inactif |

#### `produits`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| categorie_id | int | catégorie |
| nom | string | nom du produit |
| unite | string | kg, litre, pièce |
| type_origine | enum | local, importe, les_deux |
| actif | boolean | actif ou inactif |

#### `collectes`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| user_id | int | collecteur |
| marche_id | int | marché |
| date_collecte | date | date de la collecte |
| statut | enum | brouillon, soumise, validee, rejetee |
| heure_debut | datetime | heure de début |
| heure_fin | datetime | heure de fin |
| heure_transmission | datetime | heure de transmission |
| observations | text | observations générales |

#### `collecte_lignes`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| collecte_id | int | collecte parente |
| produit_id | int | produit |
| prix_producteur | decimal | prix producteur FCFA |
| prix_detail | decimal | prix détail FCFA |
| prix_demi_gros | decimal | prix demi-gros FCFA |
| quantite_relevee | decimal | quantité |
| stock | decimal | stock disponible |
| offre | decimal | offre disponible |
| provenance | enum | locale, importee, mixte |

#### `collecte_positions`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| collecte_id | int | collecte liée |
| user_id | int | collecteur |
| latitude | decimal | position GPS |
| longitude | decimal | position GPS |
| created_at | datetime | horodatage |

#### `collecte_statuts`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| collecte_id | int | collecte liée |
| user_id | int | collecteur |
| statut | enum | non_demarree, en_deplacement, arrive, en_cours, soumise, validee, rejetee |
| created_at | datetime | horodatage |

#### `demandes_acces_donnees`
| Champ | Type | Description |
|---|---|---|
| id | int | identifiant |
| nom_demandeur | string | nom |
| organisme | string | organisme |
| fonction | string | fonction |
| email | string | email |
| telephone | string | téléphone |
| objet | text | objet de la demande |
| type_donnees | string | type de données demandées |
| periode | string | période souhaitée |
| usage_prevu | text | usage prévu |
| statut | enum | en_attente, approuvee, refusee |
| decision_admin | text | motif de la décision |
| traitee_par | int | admin ayant traité |
| traitee_le | datetime | date de traitement |

---

## 24. INTERFACES À DÉVELOPPER

### 24.1 Interfaces publiques

1. Page SIM (accueil SIM)
2. Présentation SIM
3. Historique
4. Missions
5. Marchés suivis
6. Prix validés
7. Graphiques
8. Bulletins
9. Carte des marchés
10. Formulaire de demande d'accès aux données

### 24.2 Interfaces collecteur

11. Connexion collecteur
12. Tableau de bord collecteur
13. Formulaire de collecte (fiche numérique)
14. Liste de mes collectes
15. Détail d'une collecte
16. Mise à jour du statut

### 24.3 Interfaces superviseur

17. Tableau de bord superviseur
18. Suivi temps réel des collecteurs de la zone
19. Liste des collectes reçues
20. Détail d'une collecte (avec validation/rejet)
21. Alertes et retards

### 24.4 Interfaces admin

22. Tableau de bord admin général
23. Suivi temps réel national
24. Gestion des marchés (CRUD)
25. Gestion des produits (CRUD)
26. Gestion des utilisateurs (CRUD)
27. Gestion des affectations
28. Liste de toutes les collectes
29. Validation finale des collectes
30. Calculs et statistiques
31. Gestion des graphiques publiés
32. Gestion des bulletins (CRUD)
33. Gestion des demandes d'accès
34. Journal d'activité
35. Paramètres

---

## 25. ORDRE DE DÉVELOPPEMENT RECOMMANDÉ

### Phase 1 : Fondations
1. Structure des rôles et permissions
2. Gestion des régions et départements
3. Gestion des marchés (admin)
4. Gestion des catégories et produits (admin)

### Phase 2 : Collecte
5. Interface collecteur (formulaire de collecte)
6. Transmission et stockage des collectes
7. Suivi temps réel des collecteurs

### Phase 3 : Validation
8. Interface superviseur (contrôle et validation)
9. Interface admin (validation finale)

### Phase 4 : Analyse
10. Calculs automatiques des statistiques
11. Graphiques et courbes (Chart.js)
12. Tableaux de bord internes

### Phase 5 : Publication
13. Publication des prix validés sur la partie publique
14. Publication des graphiques publics
15. Gestion et publication des bulletins

### Phase 6 : Contrôle d'accès
16. Formulaire de demande d'accès aux données
17. Gestion des demandes par l'admin
18. Système d'autorisation

### Phase 7 : Compléments
19. Carte interactive des marchés
20. Pages de présentation du SIM (historique, missions)
21. Journal d'activité et traçabilité
22. Optimisations et mode hors-ligne

---

## 26. ANNEXES

### Annexe A : Liste des 86 marchés suivis

(Voir liste complète validée lors de l'atelier de décembre 2025)

### Annexe B : Liste des 50 produits suivis

(Voir liste complète validée lors de l'atelier de décembre 2025)

### Annexe C : Modèle de fiche de collecte

(Voir fiche de collecte hebdomadaire officielle CSAR/DSAR/SIM)

### Annexe D : Schéma fonctionnel global

```
Collecteur terrain
    → Saisie fiche numérique
    → Transmission
    → Suivi temps réel (statut + GPS)
        ↓
Superviseur régional
    → Contrôle
    → Validation zone
        ↓
Admin CSAR
    → Validation finale
    → Calculs statistiques
    → Publication prix / graphiques / bulletins
    → Gestion demandes accès
        ↓
Public
    → Consulte données publiées
    → Télécharge bulletins
    → Soumet demande d'accès
```

---

**Fin du cahier des charges SIM/CSAR v1.0**
