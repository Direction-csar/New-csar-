<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Services\Drh\HrAnalyticsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private readonly HrAnalyticsService $analytics)
    {
    }

    public function index()
    {
        $total      = Personnel::count();
        $hommes     = Personnel::where('sexe', 'Masculin')->count();
        $femmes     = Personnel::where('sexe', 'Féminin')->count();
        $demissions = Personnel::whereIn('statut', ['Demission', 'Démission', 'Démissionnaire'])->count();

        // Âge moyen
        $ageMoyen = (int) round(
            Personnel::whereNotNull('date_naissance')
                ->get()
                ->avg(fn($p) => Carbon::parse($p->date_naissance)->age) ?? 0
        );

        // Masse salariale brute estimée (somme des salaires bruts des agents)
        $masseSalariale = (float) Personnel::sum('salaire_brut') ?? 0;

        // Contrats par type (basé sur le champ type_contrat)
        $contratCDI  = Personnel::where('type_contrat', 'CDI')->count();
        $contratCDD  = Personnel::where('type_contrat', 'CDD')->count();
        $contratInt  = Personnel::where('statut', 'Stagiaire')->count();

        // Retraités à venir : 60 ans atteints dans les 24 mois (même requête que la liste détaillée)
        $retraites = $this->analytics->countRetraites();

        // Fins de contrat dans les 90 jours (date_fin_contrat renseignée uniquement)
        $finsContrats = $this->analytics->countFinsContrats();

        // Congés / récupération : jours enregistrés via Documents RH (année en cours)
        $congesRows = $this->analytics->congesRows();
        $recupRows  = $this->analytics->recuperationRows();
        $conges = ['agents' => $congesRows->count(), 'jours' => (int) $congesRows->sum('jours')];
        $recup  = ['agents' => $recupRows->count(),  'jours' => (int) $recupRows->sum('jours')];

        // Statistiques
        $stats = [
            'effectif'        => $total,
            'demissions'      => $demissions,
            'hommes'          => $hommes,
            'femmes'          => $femmes,
            'pct_hommes'      => $total > 0 ? round($hommes / $total * 100) : 0,
            'pct_femmes'      => $total > 0 ? round($femmes / $total * 100) : 0,
            'age_moyen'       => $ageMoyen,
            'masse_salariale' => $masseSalariale,
            'cdi'             => $contratCDI,
            'cdd'             => $contratCDD,
            'interim'         => $contratInt,
            'retraites'       => $retraites,
            'fins_contrats'   => $finsContrats,
        ];

        // Tranche d'âge par genre (âge calculé depuis la date de naissance)
        $tranchesAge = $this->analytics->ageByGender();

        // Ancienneté par genre (calculée depuis la date de recrutement)
        $anciennete = $this->analytics->seniorityByGender();

        // Genre incluant les fiches sans sexe renseigné
        $genres = $this->analytics->genderCounts();

        // Effectif par direction (top 10)
        $parDirection = Personnel::selectRaw('direction_service, COUNT(*) as c')
            ->whereNotNull('direction_service')
            ->groupBy('direction_service')
            ->orderByDesc('c')
            ->limit(10)
            ->pluck('c', 'direction_service')
            ->toArray();

        // Effectif par poste (top 10)
        $parPoste = Personnel::selectRaw('poste_actuel, COUNT(*) as c')
            ->whereNotNull('poste_actuel')
            ->groupBy('poste_actuel')
            ->orderByDesc('c')
            ->limit(10)
            ->pluck('c', 'poste_actuel')
            ->toArray();

        // Effectif par région
        $parRegion = Personnel::selectRaw('localisation_region, COUNT(*) as c')
            ->whereNotNull('localisation_region')
            ->groupBy('localisation_region')
            ->orderByDesc('c')
            ->pluck('c', 'localisation_region')
            ->toArray();

        // Effectif par statut
        $parStatut = Personnel::selectRaw('statut, COUNT(*) as c')
            ->whereNotNull('statut')
            ->groupBy('statut')
            ->pluck('c', 'statut')
            ->toArray();

        // Évolution recrutements (par année, 6 dernières années)
        $currentYear = (int) date('Y');
        $evolutionRecrutements = [];
        for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
            $evolutionRecrutements[$y] = Personnel::whereYear('date_recrutement_csar', $y)->count();
        }

        // Liste agents les plus anciens (top 5)
        $plusAnciens = Personnel::whereNotNull('date_recrutement_csar')
            ->orderBy('date_recrutement_csar', 'asc')
            ->limit(5)
            ->get();

        // Liste agents les plus récents (top 5)
        $plusRecents = Personnel::whereNotNull('date_recrutement_csar')
            ->orderBy('date_recrutement_csar', 'desc')
            ->limit(5)
            ->get();

        // Types de documents pour la grille
        $documentTypes = [
            ['label' => 'Contrat CDI',               'slug' => 'contrat-cdi',            'icon' => 'fa-file-contract'],
            ['label' => 'Contrat CDD',               'slug' => 'contrat-cdd',            'icon' => 'fa-file-contract'],
            ['label' => 'Contrat stagiaire',         'slug' => 'contrat-stagiaire',      'icon' => 'fa-file-contract'],
            ['label' => 'Certificat de travail',     'slug' => 'certificat-travail',     'icon' => 'fa-file-check'],
            ['label' => 'Attestation de travail',    'slug' => 'attestation-travail',    'icon' => 'fa-file-lines'],
            ['label' => 'Attestation travail & salaire','slug' => 'attestation-travail-salaire','icon' => 'fa-file-invoice-dollar'],
            ['label' => 'Abandon de poste',         'slug' => 'abandon-poste',          'icon' => 'fa-door-open'],
            ['label' => 'Notification absence',     'slug' => 'notification-absence', 'icon' => 'fa-bell'],
            ['label' => 'Avertissement',             'slug' => 'avertissement',          'icon' => 'fa-triangle-exclamation'],
            ['label' => 'Contrat de prêt',           'slug' => 'contrat-pret',           'icon' => 'fa-hand-holding-dollar'],
            ['label' => 'Avance sur salaire',        'slug' => 'avance-salaire',         'icon' => 'fa-money-bill-wave'],
            ['label' => 'Demande de congé',         'slug' => 'decision-conge',         'icon' => 'fa-file-contract'],
            ['label' => 'Récupération',             'slug' => 'demande-recuperation',   'icon' => 'fa-rotate-left'],
            ['label' => 'Domiciliation',           'slug' => 'domiciliation',          'icon' => 'fa-building-columns'],
            ['label' => 'Autorisation absence',     'slug' => 'autorisation-absence',   'icon' => 'fa-user-clock'],
            ['label' => 'Bon de sortie',             'slug' => 'bon-sortie',             'icon' => 'fa-door-open'],
        ];

        // Départements et postes (avec effectifs) pour la navigation
        $departments = $this->analytics->departements();
        $services    = $this->analytics->services();

        return view('admin.drh.dashboard.index', compact(
            'stats', 'tranchesAge', 'anciennete', 'genres', 'conges', 'recup', 'parDirection',
            'parPoste', 'parRegion', 'parStatut',
            'evolutionRecrutements', 'plusAnciens', 'plusRecents',
            'documentTypes', 'departments', 'services'
        ));
    }
}
