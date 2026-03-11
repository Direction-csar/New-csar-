@extends('layouts.public')

@section('title', 'Présentation du SIM - Rapport d\'atelier')
@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => app()->getLocale()]) }}">Accueil</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sim.index', ['locale' => app()->getLocale()]) }}">SIM</a></li>
            <li class="breadcrumb-item active">Présentation & Rapport d'atelier</li>
        </ol>
    </nav>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h3 mb-3">Rapport de l'atelier de renforcement de capacités et d'actualisation du SIM</h1>
            <p class="text-muted mb-0">Hôtel Royal (Saly Portugal) : 19-20 décembre 2025 — Décembre 2025</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">SOMMAIRE</div>
        <div class="card-body">
            <ul class="mb-0">
                <li>I. Contexte</li>
                <li>II. Objectifs</li>
                <li>III. Cérémonie d'ouverture</li>
                <li>IV. Déroulement de l'atelier (Présentation du SIM/CSAR)</li>
                <li>V. Validation de la liste des marchés</li>
                <li>VI. Validation de la liste des produits suivis</li>
                <li>VII. Validation de la fiche de collecte</li>
                <li>VIII. Harmonisation méthodologique des techniques de collecte</li>
                <li>IX. Bilan de l'atelier</li>
                <li>X. Recommandations</li>
                <li>XI. Annexes (Agenda, Liste des marchés, Liste des produits, Fiche de collecte)</li>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">I. CONTEXTE</div>
        <div class="card-body">
            <p>Dans le souci de garantir la sécurité alimentaire et nutritionnelle aux populations, le Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR) dispose du SIM qui est un outil de suivi et de collecte d'informations sur les marchés agropastoraux mis en place depuis 1987 à travers des enquêtes hebdomadaires.</p>
            <p>Les informations issues de ces enquêtes servent à alerter précocement sur une éventuelle insécurité alimentaire et aider les États à une prise de décision pour une intervention efficace. Pour atteindre ces objectifs, le CSAR compte élargir son champ d'action en ciblant les marchés de tous les départements du pays et en augmentant l'échantillon de produits suivis.</p>
            <p>L'atelier de renforcement de capacités et d'actualisation du SIM organisé par le CSAR en partenariat avec le Programme de Résilience du Système Alimentaire (PRSA/FRSP) s'est tenu les 19 et 20 décembre 2025 à l'hôtel Royal de Saly et a enregistré vingt-sept (27) participants.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">II. OBJECTIFS</div>
        <div class="card-body">
            <p>L'objectif général est de renforcer les capacités du dispositif national de collecte et de supervision du SIM du CSAR afin d'améliorer la fiabilité, la couverture et la pertinence des données pour une analyse plus fine de la sécurité alimentaire et de la résilience.</p>
            <ul>
                <li>Valider la nouvelle fiche de marchés à enquêter assurant l'extension de la couverture nationale de 55 à 86 marchés.</li>
                <li>Valider l'augmentation du panier de produits à collecter passant de 32 à 50 produits.</li>
                <li>Retravailler et valider la fiche SIM comme instrument d'enquête harmonisé et pertinent.</li>
                <li>Harmoniser et valider la méthodologie et les techniques d'enquête sur les marchés (supervision et contrôle qualité).</li>
                <li>Partager les bonnes pratiques et renforcer les capacités techniques des agents de collecte et de supervision.</li>
            </ul>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">IV. PRÉSENTATION DU SIM/CSAR</div>
        <div class="card-body">
            <h6>IV.1.1 Missions du CSAR</h6>
            <p>Le CSAR, établissement public à caractère administratif (tutelle technique : Ministère de la Famille, de l'Action Sociale et des Solidarités ; tutelle financière : Ministère des Finances et du Budget), a pour missions : régulation et étude de marché ; prévention et gestion des risques liés à l'insécurité alimentaire ; identification et suivi des zones à risque ; gestion des stocks alimentaires de l'État ; élaboration et mise en œuvre des plans de réponse ; coordination de projets et programmes ; participation à l'élaboration des politiques de sécurité alimentaire et de résilience.</p>
            <h6 class="mt-3">IV.1.2 Historique du SIM</h6>
            <p>Le SIM du CSA a été mis en place en juillet 1987 par le Programme de Sécurité Alimentaire (PSA) de la coopération allemande (GTZ) pour accompagner la politique de libéralisation des prix du marché national des céréales (P.A.S.A). Sélection des marchés (ruraux et urbains), formation du dispositif (superviseurs, enquêteurs), démarrage de la collecte en janvier 1988.</p>
            <h6 class="mt-3">IV.1.3 Objectifs et missions du SIM</h6>
            <p><strong>Objectifs :</strong> (i) Rendre le marché agricole national plus transparent ; (ii) Favoriser l'autorégulation du marché (transferts zones excédentaires → zones déficitaires) ; (iii) Informer en temps réel les acteurs sur l'évolution des cours ; (iv) Éclairer les autorités et partenaires pour la prise de décision.</p>
            <p><strong>Missions :</strong> Collecte (offres, stocks, prix, flux) ; base de données (saisie, traitement, contrôle, analyse) ; élaboration et diffusion des produits (bulletins, rapports, annuaires) ; coordination, supervision et évaluation des marchés ; études ponctuelles.</p>
            <h6 class="mt-3">IV.1.4 Dispositif du SIM</h6>
            <p>Une (01) équipe de coordination à la Direction Générale ; quatorze (14) superviseurs régionaux ; des enquêteurs sur les marchés ciblés.</p>
            <h6 class="mt-3">IV.1.5 Critères de sélection des marchés</h6>
            <p>Accessibilité en toute saison ; importance du marché dans la zone (polarisation, affluence) ; présence régulière des produits suivis.</p>
            <h6 class="mt-3">IV.1.6 Typologie des marchés</h6>
            <p>Marchés ruraux de collecte ; marchés ruraux de consommation ; marchés urbains de groupement ou de consommation ; marchés frontalier et transfrontalier.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">V. VALIDATION DE LA LISTE DES MARCHÉS</div>
        <div class="card-body">
            <p>L'échantillon de marchés suivis passe de 55 à 86 marchés couvrant les 46 départements du Sénégal. Voir annexe B pour la liste complète.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">VI. VALIDATION DE LA LISTE DES PRODUITS SUIVIS</div>
        <div class="card-body">
            <p>Le SIM collecte des informations sur : céréales locales et importées ; légumineuses ; légumes locaux et importés ; bétail. La nouvelle liste intègre viande (kg), produits halieutiques, œufs, huiles et graisses, produits forestiers (pain de singe, jujube, anacarde). L'échantillon passe de 32 à 50 produits. Voir annexe C.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">VII. FICHE DE COLLECTE — VIII. TECHNIQUES DE COLLECTE</div>
        <div class="card-body">
            <p>Fiche de collecte hebdomadaire harmonisée avec colonne « provenance » (facultative). Critères pour une bonne collecte : présence de l'enquêteur de préférence entre 09h00 et 13h00 ; fiche harmonisée. Techniques : observation passive des transactions ; interviews (producteurs, commerçants, consommateurs, transporteurs) ; achat d'échantillons ; examen des livres des commerçants. Fréquence : hebdomadaire. Visibilité : tenue (gilet, T-shirt, casquette), passage par le délégué de marché. Digitalisation prévue pour l'accès aux données en temps réel.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">IX. BILAN — X. RECOMMANDATIONS</div>
        <div class="card-body">
            <p><strong>Bilan :</strong> Les participants sont sortis satisfaits ; échanges instructifs.</p>
            <p><strong>Recommandations :</strong> Doter le dispositif SIM en moyens logistiques et financiers ; renforcer le personnel déconcentré ; cartes professionnelles et badges ; acquisition de serveurs et d'outils de collecte (tablettes, balances, smartphones) ; mise en ligne des données (connexion internet, mails professionnels, nom de domaine) ; renforcement de la collaboration avec les autres sectorielles ; recrutement de personnel qualifié.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">ANNEXE B — Liste des marchés (extrait par région)</div>
        <div class="card-body">
            <p class="small text-muted">Régions : Dakar, Diourbel, Fatick, Kaffrine, Kaolack, Kédougou, Kolda, Louga, Matam, Saint-Louis, Sédhiou, Tambacounda, Thiès, Ziguinchor. Types : Urbain permanent ; Rural (regroupement, collecte, consommation) ; Frontalier / transfrontalier. Jour de marché selon localité. Liste complète : 86 marchés — voir document officiel du rapport.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">ANNEXE C — Liste des produits suivis (50 produits)</div>
        <div class="card-body">
            <p class="mb-2"><strong>Céréales & tubercules :</strong> Fonio, Maïs (local, importé), Mil (sanio, souna), Riz (local décortiqué, importé ordinaire/parfumé/entier), Sorgho (local, importé), Manioc, Patate douce, Pomme de terre (locale, importée).</p>
            <p class="mb-2"><strong>Légumineuses :</strong> Arachides (coque, décortiquée), Niébé (1ère et 2ème qualité).</p>
            <p class="mb-2"><strong>Légumes & fruits :</strong> Aubergines, Carotte, Oignon (local, importé), Tomate, Banane, Mangues, Orange, Pastèques, Pomme.</p>
            <p class="mb-2"><strong>Viande & têtes :</strong> Bœuf, Caprin, Mouton, Poulet, Bovin, Ovin, Caprin.</p>
            <p class="mb-2"><strong>Poissons & œufs :</strong> Chinchard frais, Barracuda, Sardinella, Poisson fumé/séché ; Œuf poule locale, Œuf pondeuse.</p>
            <p class="mb-0"><strong>Huiles & produits forestiers :</strong> Huile arachide (Diw ségal, raffinée), Huile végétale, tournesol, palme ; Pain de singe, Jujube (sidemme), Anacarde.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-bold">Collecte et diffusion des données (référence SIMA)</div>
        <div class="card-body">
            <p>Les activités principales du SIM sont la <strong>collecte</strong>, le <strong>traitement</strong> et la <strong>diffusion</strong> des informations relatives aux marchés agricoles. La collecte se fait par les enquêteurs de manière hebdomadaire sur les différents marchés suivis (données sur plus de 50 produits : céréales, légumineuses, fruits et légumes, tubercules, viandes, poissons, œufs, huiles, etc.).</p>
            <p><strong>Diffusion :</strong> Une (01) diffusion hebdomadaire des prix à la radio nationale ; deux (02) bulletins hebdomadaires (céréales ; fruits et légumes) ; publications mensuelles (bulletins céréales et produits de rente, bulletins conjoints) ; publication trimestrielle (contribution RESIMAO) ; annuaire des produits suivis.</p>
        </div>
    </div>

    <div class="text-center py-3">
        <a href="{{ route('sim.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary">Retour au SIM</a>
        <a href="{{ route('sim.dashboard', ['locale' => app()->getLocale()]) }}" class="btn btn-primary ms-2">Dashboard SIM</a>
    </div>
</div>
@endsection
