<?php

namespace App\Services\Drh;

use App\Models\Personnel;
use App\Models\RhDocument;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as PaginatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Source unique de vérité pour les indicateurs du tableau de bord DRH
 * et leurs listes détaillées. Chaque compteur du dashboard est calculé
 * par la même requête que la liste correspondante.
 */
class HrAnalyticsService
{
    public const RETIREMENT_AGE = 60;
    public const DEFAULT_RETIREMENT_HORIZON_MONTHS = 24;
    public const DEFAULT_CONTRACT_HORIZON_DAYS = 90;
    public const PER_PAGE = 25;

    public const SENIORITY_BANDS = [
        'lt3'    => ['label' => 'Moins de 3 ans',        'min' => 0,  'max' => 3],
        '3-5'    => ['label' => 'De 3 à moins de 5 ans',  'min' => 3,  'max' => 5],
        '5-10'   => ['label' => 'De 5 à moins de 10 ans', 'min' => 5,  'max' => 10],
        '10-15'  => ['label' => 'De 10 à moins de 15 ans','min' => 10, 'max' => 15],
        '15plus' => ['label' => '15 ans et plus',         'min' => 15, 'max' => null],
    ];

    public const AGE_BANDS = [
        'lt30'   => ['label' => 'Moins de 30 ans', 'min' => 0,  'max' => 30],
        '30-39'  => ['label' => '30 – 39 ans',     'min' => 30, 'max' => 40],
        '40-49'  => ['label' => '40 – 49 ans',     'min' => 40, 'max' => 50],
        '50-59'  => ['label' => '50 – 59 ans',     'min' => 50, 'max' => 60],
        '60plus' => ['label' => '60 ans et plus',  'min' => 60, 'max' => null],
    ];

    public const GENDERS = [
        'Féminin'  => 'Femme',
        'Masculin' => 'Homme',
    ];

    public const DEMISSION_STATUSES = ['Demission', 'Démission', 'Démissionnaire'];

    private const SQL_AGE       = 'TIMESTAMPDIFF(YEAR, personnel.date_naissance, CURDATE())';
    private const SQL_SENIORITY = 'TIMESTAMPDIFF(YEAR, personnel.date_recrutement_csar, CURDATE())';
    private const SQL_RETIRE_AT = 'DATE_ADD(personnel.date_naissance, INTERVAL ' . self::RETIREMENT_AGE . ' YEAR)';

    /** Métriques exposées avec libellés et colonnes. */
    public function metrics(): array
    {
        return [
            'retraites' => [
                'title'    => 'Retraités à venir',
                'subtitle' => 'Agents atteignant l\'âge légal de ' . self::RETIREMENT_AGE . ' ans (date théorique calculée à partir de la date de naissance)',
                'icon'     => 'fa-user-clock',
                'columns'  => ['photo', 'matricule', 'nom', 'sexe', 'date_naissance', 'age', 'date_retraite', 'reste', 'departement', 'service', 'statut', 'date_recrutement', 'anciennete', 'contact'],
                'sort'     => 'date_retraite',
                'dir'      => 'asc',
            ],
            'anciennete' => [
                'title'    => 'Ancienneté',
                'subtitle' => 'Calculée à partir de la date de recrutement au CSAR',
                'icon'     => 'fa-hourglass-half',
                'columns'  => ['nom', 'matricule', 'sexe', 'date_recrutement', 'anciennete', 'departement', 'service', 'statut', 'type_contrat'],
                'sort'     => 'date_recrutement',
                'dir'      => 'asc',
            ],
            'fins-contrats' => [
                'title'    => 'Fins de contrats',
                'subtitle' => 'Contrats dont la date de fin est renseignée et arrive à échéance',
                'icon'     => 'fa-file-signature',
                'columns'  => ['nom', 'matricule', 'type_contrat', 'date_debut_contrat', 'date_fin_contrat', 'jours_restants', 'departement', 'service', 'statut'],
                'sort'     => 'date_fin_contrat',
                'dir'      => 'asc',
            ],
            'genre' => [
                'title'    => 'Employés par genre',
                'subtitle' => 'Répartition selon le sexe enregistré dans la fiche agent',
                'icon'     => 'fa-venus-mars',
                'columns'  => ['nom', 'matricule', 'sexe', 'departement', 'service', 'statut', 'type_contrat'],
                'sort'     => 'nom',
                'dir'      => 'asc',
            ],
            'age' => [
                'title'    => 'Tranches d\'âge',
                'subtitle' => 'Âge calculé à partir de la date de naissance',
                'icon'     => 'fa-birthday-cake',
                'columns'  => ['nom', 'matricule', 'age', 'sexe', 'date_naissance', 'departement', 'service', 'type_contrat'],
                'sort'     => 'age',
                'dir'      => 'desc',
            ],
            'conges' => [
                'title'    => 'Congés',
                'subtitle' => 'Jours de congé issus des décisions de congé enregistrées (Documents RH)',
                'icon'     => 'fa-umbrella-beach',
                'columns'  => ['nom', 'matricule', 'departement', 'service', 'jours_pris', 'nb_decisions', 'derniere_decision'],
                'sort'     => 'jours_pris',
                'dir'      => 'desc',
            ],
            'recuperation' => [
                'title'    => 'Récupération',
                'subtitle' => 'Jours issus des demandes de récupération enregistrées (Documents RH)',
                'icon'     => 'fa-rotate-left',
                'columns'  => ['nom', 'matricule', 'departement', 'service', 'jours_recup', 'nb_demandes', 'derniere_demande'],
                'sort'     => 'jours_recup',
                'dir'      => 'desc',
            ],
            'accidents-travail' => [
                'title'    => 'Accidents de travail (AT)',
                'subtitle' => 'Aucun module d\'accidents de travail n\'est enregistré sur la plateforme',
                'icon'     => 'fa-kit-medical',
                'columns'  => [],
                'sort'     => null,
                'dir'      => 'asc',
            ],
        ];
    }

    public function columnLabels(): array
    {
        return [
            'photo'              => 'Photo',
            'matricule'          => 'Matricule',
            'nom'                => 'Nom et prénom',
            'sexe'               => 'Sexe',
            'date_naissance'     => 'Date de naissance',
            'age'                => 'Âge',
            'date_retraite'      => 'Date théorique 60 ans',
            'reste'              => 'Échéance',
            'departement'        => 'Département',
            'service'            => 'Poste / fonction',
            'statut'             => 'Statut',
            'date_recrutement'   => 'Date de recrutement',
            'anciennete'         => 'Ancienneté',
            'contact'            => 'Contact',
            'type_contrat'       => 'Type de contrat',
            'date_debut_contrat' => 'Début du contrat',
            'date_fin_contrat'   => 'Fin du contrat',
            'jours_restants'     => 'Jours restants',
            'jours_pris'         => 'Jours pris',
            'nb_decisions'       => 'Décisions',
            'derniere_decision'  => 'Dernière décision',
            'jours_recup'        => 'Jours de récupération',
            'nb_demandes'        => 'Demandes',
            'derniere_demande'   => 'Dernière demande',
        ];
    }

    /** Colonnes triables (clé publique => expression SQL). */
    private function sortable(): array
    {
        return [
            'nom'                => 'personnel.prenoms_nom',
            'matricule'          => 'personnel.matricule',
            'sexe'               => 'personnel.sexe',
            'date_naissance'     => 'personnel.date_naissance',
            'age'                => 'personnel.date_naissance',
            'date_retraite'      => 'personnel.date_naissance',
            'reste'              => 'personnel.date_naissance',
            'departement'        => 'personnel.direction_service',
            'service'            => 'personnel.poste_actuel',
            'statut'             => 'personnel.statut',
            'date_recrutement'   => 'personnel.date_recrutement_csar',
            'anciennete'         => 'personnel.date_recrutement_csar',
            'type_contrat'       => 'personnel.type_contrat',
            'date_debut_contrat' => 'personnel.date_debut_contrat',
            'date_fin_contrat'   => 'personnel.date_fin_contrat',
            'jours_restants'     => 'personnel.date_fin_contrat',
        ];
    }

    // ------------------------------------------------------------------
    // Requêtes de base
    // ------------------------------------------------------------------

    public function base(): Builder
    {
        return Personnel::query();
    }

    /** Applique les filtres communs (recherche, département, poste, sexe). */
    public function applyCommonFilters(Builder $q, array $f): Builder
    {
        if (!empty($f['q'])) {
            $term = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($f['q'])) . '%';
            $q->where(function (Builder $w) use ($term) {
                $w->where('personnel.prenoms_nom', 'like', $term)
                  ->orWhere('personnel.matricule', 'like', $term);
            });
        }
        if (!empty($f['departement'])) {
            $q->where('personnel.direction_service', $f['departement']);
        }
        if (!empty($f['service'])) {
            $q->where('personnel.poste_actuel', $f['service']);
        }
        if (!empty($f['sexe'])) {
            if ($f['sexe'] === 'nr') {
                $q->where(function (Builder $w) {
                    $w->whereNull('personnel.sexe')->orWhereNotIn('personnel.sexe', array_keys(self::GENDERS));
                });
            } else {
                $q->where('personnel.sexe', $f['sexe']);
            }
        }
        if (!empty($f['type_contrat'])) {
            if ($f['type_contrat'] === 'Stage') {
                $q->where('personnel.statut', 'Stagiaire');
            } else {
                $q->where('personnel.type_contrat', $f['type_contrat']);
            }
        }
        return $q;
    }

    public function applySort(Builder $q, string $metric, array $f): Builder
    {
        $def   = $this->metrics()[$metric] ?? null;
        $sort  = $f['sort'] ?? ($def['sort'] ?? 'nom');
        $dir   = strtolower($f['dir'] ?? ($def['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $cols  = $this->sortable();
        $expr  = $cols[$sort] ?? $cols['nom'];

        // Pour l'âge / ancienneté, "asc" signifie le plus jeune / le moins ancien en premier,
        // ce qui correspond à la date la plus récente : on inverse l'ordre de la date.
        if (in_array($sort, ['age', 'anciennete'], true)) {
            $dir = $dir === 'asc' ? 'desc' : 'asc';
        }

        return $q->orderBy($expr, $dir)->orderBy('personnel.prenoms_nom');
    }

    // ------------------------------------------------------------------
    // Métriques basées sur la table personnel
    // ------------------------------------------------------------------

    public function retraitesQuery(array $f = []): Builder
    {
        $horizon = (int) ($f['horizon'] ?? self::DEFAULT_RETIREMENT_HORIZON_MONTHS);
        $horizon = in_array($horizon, [6, 12, 24, 36, 60], true) ? $horizon : self::DEFAULT_RETIREMENT_HORIZON_MONTHS;

        $q = $this->base()
            ->whereNotNull('personnel.date_naissance')
            ->whereNotIn('personnel.statut', self::DEMISSION_STATUSES)
            ->whereRaw(self::SQL_RETIRE_AT . ' <= DATE_ADD(CURDATE(), INTERVAL ? MONTH)', [$horizon]);

        if (($f['periode'] ?? '') === 'atteint') {
            $q->whereRaw(self::SQL_RETIRE_AT . ' <= CURDATE()');
        } elseif (($f['periode'] ?? '') === 'a_venir') {
            $q->whereRaw(self::SQL_RETIRE_AT . ' > CURDATE()');
        }

        return $this->applyCommonFilters($q, $f);
    }

    public function ancienneteQuery(array $f = []): Builder
    {
        $q = $this->base()->whereNotNull('personnel.date_recrutement_csar');
        if (!empty($f['tranche']) && isset(self::SENIORITY_BANDS[$f['tranche']])) {
            $this->applyBand($q, self::SQL_SENIORITY, self::SENIORITY_BANDS[$f['tranche']]);
        }
        return $this->applyCommonFilters($q, $f);
    }

    public function finsContratsQuery(array $f = []): Builder
    {
        $days = (int) ($f['horizon'] ?? self::DEFAULT_CONTRACT_HORIZON_DAYS);
        $days = in_array($days, [30, 60, 90, 180, 365], true) ? $days : self::DEFAULT_CONTRACT_HORIZON_DAYS;

        $q = $this->base()
            ->whereNotNull('personnel.date_fin_contrat')
            ->whereDate('personnel.date_fin_contrat', '>=', Carbon::today())
            ->whereDate('personnel.date_fin_contrat', '<=', Carbon::today()->addDays($days));

        return $this->applyCommonFilters($q, $f);
    }

    public function genreQuery(array $f = []): Builder
    {
        return $this->applyCommonFilters($this->base(), $f);
    }

    public function ageQuery(array $f = []): Builder
    {
        $q = $this->base()->whereNotNull('personnel.date_naissance');
        if (!empty($f['tranche']) && isset(self::AGE_BANDS[$f['tranche']])) {
            $this->applyBand($q, self::SQL_AGE, self::AGE_BANDS[$f['tranche']]);
        }
        return $this->applyCommonFilters($q, $f);
    }

    private function applyBand(Builder $q, string $expr, array $band): void
    {
        $q->whereRaw("$expr >= ?", [$band['min']]);
        if ($band['max'] !== null) {
            $q->whereRaw("$expr < ?", [$band['max']]);
        }
    }

    // ------------------------------------------------------------------
    // Métriques basées sur rh_documents (congés / récupération)
    // ------------------------------------------------------------------

    /** Agrège les documents d'un type par agent (jours = champ "duree"). */
    private function aggregateDocuments(string $type, array $f, string $dateField): Collection
    {
        $year = (int) ($f['annee'] ?? date('Y'));

        $docs = RhDocument::query()
            ->where('type', $type)
            ->whereNotNull('personnel_id')
            ->with('personnel')
            ->get()
            ->filter(function (RhDocument $d) use ($year, $dateField) {
                $date = $d->data[$dateField] ?? null;
                if (!$date) {
                    return $year === (int) $d->created_at->format('Y');
                }
                try {
                    return (int) Carbon::parse($date)->format('Y') === $year;
                } catch (\Throwable) {
                    return false;
                }
            });

        $rows = $docs->groupBy('personnel_id')->map(function (Collection $group) use ($dateField) {
            /** @var Personnel|null $p */
            $p = $group->first()->personnel;
            if (!$p) {
                return null;
            }
            $jours = $group->sum(fn (RhDocument $d) => (int) preg_replace('/\D+/', '', (string) ($d->data['duree'] ?? 0)));
            $last  = $group->map(function (RhDocument $d) use ($dateField) {
                try {
                    return $d->data[$dateField] ? Carbon::parse($d->data[$dateField]) : $d->created_at;
                } catch (\Throwable) {
                    return $d->created_at;
                }
            })->max();

            return [
                'personnel' => $p,
                'jours'     => $jours,
                'nb'        => $group->count(),
                'derniere'  => $last,
            ];
        })->filter()->values();

        // Filtres communs appliqués en mémoire (volume faible : documents RH)
        $rows = $rows->filter(function (array $r) use ($f) {
            $p = $r['personnel'];
            if (!empty($f['q'])) {
                $t = mb_strtolower($f['q']);
                if (!str_contains(mb_strtolower((string) $p->prenoms_nom), $t) && !str_contains(mb_strtolower((string) $p->matricule), $t)) {
                    return false;
                }
            }
            if (!empty($f['departement']) && $p->direction_service !== $f['departement']) return false;
            if (!empty($f['service']) && $p->poste_actuel !== $f['service']) return false;
            if (!empty($f['sexe']) && $f['sexe'] !== 'nr' && $p->sexe !== $f['sexe']) return false;
            if (($f['solde'] ?? '') === 'positif' && $r['jours'] <= 0) return false;
            if (($f['solde'] ?? '') === 'nul' && $r['jours'] !== 0) return false;
            return true;
        })->values();

        $sort = $f['sort'] ?? 'jours';
        $desc = strtolower($f['dir'] ?? 'desc') === 'desc';
        $rows = $rows->sortBy(function (array $r) use ($sort) {
            return match ($sort) {
                'nom'         => mb_strtolower((string) $r['personnel']->prenoms_nom),
                'matricule'   => (string) $r['personnel']->matricule,
                'departement' => (string) $r['personnel']->direction_service,
                'service'     => (string) $r['personnel']->poste_actuel,
                'nb_decisions', 'nb_demandes' => $r['nb'],
                'derniere_decision', 'derniere_demande' => $r['derniere']?->timestamp ?? 0,
                default       => $r['jours'],
            };
        }, SORT_REGULAR, $desc)->values();

        return $rows;
    }

    public function congesRows(array $f = []): Collection
    {
        return $this->aggregateDocuments('decision-conge', $f, 'date_debut');
    }

    public function recuperationRows(array $f = []): Collection
    {
        return $this->aggregateDocuments('demande-recuperation', $f, 'date_recuperation');
    }

    // ------------------------------------------------------------------
    // Pagination unifiée
    // ------------------------------------------------------------------

    public function paginate(string $metric, array $f, int $page, ?int $perPage = null): PaginatorContract
    {
        $perPage = $perPage ?: self::PER_PAGE;

        if (in_array($metric, ['conges', 'recuperation'], true)) {
            $rows = $metric === 'conges' ? $this->congesRows($f) : $this->recuperationRows($f);
            return new LengthAwarePaginator(
                $rows->forPage($page, $perPage)->values(),
                $rows->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $f]
            );
        }

        if ($metric === 'accidents-travail') {
            return new LengthAwarePaginator(collect(), 0, $perPage, $page, ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $f]);
        }

        $q = $this->queryFor($metric, $f);
        $this->applySort($q, $metric, $f);
        return $q->paginate($perPage, ['personnel.*'], 'page', $page)->appends($f);
    }

    public function queryFor(string $metric, array $f): Builder
    {
        return match ($metric) {
            'retraites'     => $this->retraitesQuery($f),
            'anciennete'    => $this->ancienneteQuery($f),
            'fins-contrats' => $this->finsContratsQuery($f),
            'genre'         => $this->genreQuery($f),
            'age'           => $this->ageQuery($f),
            default         => throw new \InvalidArgumentException("Métrique inconnue : $metric"),
        };
    }

    /** Toutes les lignes (pour export), bornées pour éviter les exports démesurés. */
    public function all(string $metric, array $f, int $max = 5000): Collection
    {
        if (in_array($metric, ['conges', 'recuperation'], true)) {
            return ($metric === 'conges' ? $this->congesRows($f) : $this->recuperationRows($f))->take($max);
        }
        if ($metric === 'accidents-travail') {
            return collect();
        }
        $q = $this->queryFor($metric, $f);
        $this->applySort($q, $metric, $f);
        return $q->limit($max)->get();
    }

    // ------------------------------------------------------------------
    // Présentation d'une ligne (valeurs affichables / exportables)
    // ------------------------------------------------------------------

    public function row(string $metric, mixed $item): array
    {
        if (in_array($metric, ['conges', 'recuperation'], true)) {
            /** @var Personnel $p */
            $p = $item['personnel'];
            $base = [
                'nom'         => $p->prenoms_nom,
                'matricule'   => $p->matricule,
                'departement' => $p->direction_service,
                'service'     => $p->poste_actuel,
                '_id'         => $p->id,
            ];
            if ($metric === 'conges') {
                return $base + [
                    'jours_pris'        => $item['jours'],
                    'nb_decisions'      => $item['nb'],
                    'derniere_decision' => $item['derniere']?->format('d/m/Y'),
                ];
            }
            return $base + [
                'jours_recup'      => $item['jours'],
                'nb_demandes'      => $item['nb'],
                'derniere_demande' => $item['derniere']?->format('d/m/Y'),
            ];
        }

        /** @var Personnel $p */
        $p = $item;
        $today = Carbon::today();
        $retireAt = $p->date_naissance?->copy()->addYears(self::RETIREMENT_AGE);
        $reste = null;
        if ($retireAt) {
            if ($retireAt->lte($today)) {
                $m = (int) $retireAt->diffInMonths($today);
                $reste = 'Âge atteint depuis ' . ($m >= 1 ? $m . ' mois' : (int) $retireAt->diffInDays($today) . ' j');
            } else {
                $d = (int) $today->diffInDays($retireAt);
                $m = (int) $today->diffInMonths($retireAt);
                $reste = 'Dans ' . ($m >= 2 ? $m . ' mois (' . $d . ' j)' : $d . ' j');
            }
        }

        $anc = null;
        if ($p->date_recrutement_csar) {
            $diff = $p->date_recrutement_csar->diff($today);
            $anc = $diff->y . ' an' . ($diff->y > 1 ? 's' : '') . ($diff->m ? ' ' . $diff->m . ' mois' : '');
        }

        $typeContrat = $p->statut === 'Stagiaire' ? 'Stage' : ($p->type_contrat ?: null);

        return [
            '_id'                => $p->id,
            'photo'              => $p->photo_personnelle ? $p->photo_url : null,
            'matricule'          => $p->matricule,
            'nom'                => $p->prenoms_nom,
            'sexe'               => $p->sexe && isset(self::GENDERS[$p->sexe]) ? self::GENDERS[$p->sexe] : ($p->sexe ?: 'Non renseigné'),
            'date_naissance'     => $p->date_naissance?->format('d/m/Y'),
            'age'                => $p->date_naissance ? $p->date_naissance->age . ' ans' : null,
            'date_retraite'      => $retireAt?->format('d/m/Y'),
            'reste'              => $reste,
            'departement'        => $p->direction_service,
            'service'            => $p->poste_actuel,
            'statut'             => $p->statut,
            'date_recrutement'   => $p->date_recrutement_csar?->format('d/m/Y'),
            'anciennete'         => $anc,
            'contact'            => trim(implode(' · ', array_filter([$p->contact_telephonique, $p->email]))) ?: null,
            'type_contrat'       => $typeContrat,
            'date_debut_contrat' => $p->date_debut_contrat?->format('d/m/Y'),
            'date_fin_contrat'   => $p->date_fin_contrat?->format('d/m/Y'),
            'jours_restants'     => $p->date_fin_contrat ? max(0, $today->diffInDays($p->date_fin_contrat, false)) : null,
        ];
    }

    // ------------------------------------------------------------------
    // Agrégats pour le dashboard et les fiches département / poste
    // ------------------------------------------------------------------

    public function countRetraites(int $horizonMonths = self::DEFAULT_RETIREMENT_HORIZON_MONTHS, array $f = []): int
    {
        return $this->retraitesQuery($f + ['horizon' => $horizonMonths])->count();
    }

    public function countFinsContrats(int $days = self::DEFAULT_CONTRACT_HORIZON_DAYS, array $f = []): int
    {
        return $this->finsContratsQuery($f + ['horizon' => $days])->count();
    }

    /** Répartition [band => [sexe => n]] pour l'ancienneté. */
    public function seniorityByGender(array $f = []): array
    {
        return $this->bandsByGender(self::SQL_SENIORITY, self::SENIORITY_BANDS, 'date_recrutement_csar', $f);
    }

    /** Répartition [band => [sexe => n]] pour l'âge. */
    public function ageByGender(array $f = []): array
    {
        return $this->bandsByGender(self::SQL_AGE, self::AGE_BANDS, 'date_naissance', $f);
    }

    private function bandsByGender(string $expr, array $bands, string $notNull, array $f): array
    {
        $caseParts = [];
        foreach ($bands as $key => $b) {
            $cond = "$expr >= {$b['min']}" . ($b['max'] !== null ? " AND $expr < {$b['max']}" : '');
            $caseParts[] = "WHEN $cond THEN '" . $key . "'";
        }
        $case = 'CASE ' . implode(' ', $caseParts) . " ELSE 'autre' END";

        $q = $this->applyCommonFilters($this->base()->whereNotNull("personnel.$notNull"), $f);
        $rows = $q->selectRaw("$case as band, personnel.sexe, COUNT(*) as c")
            ->groupByRaw("$case, personnel.sexe")
            ->get();

        $out = [];
        foreach ($bands as $key => $b) {
            $out[$key] = ['label' => $b['label'], 'Féminin' => 0, 'Masculin' => 0, 'nr' => 0, 'total' => 0];
        }
        foreach ($rows as $r) {
            if (!isset($out[$r->band])) continue;
            $g = isset(self::GENDERS[$r->sexe]) ? $r->sexe : 'nr';
            $out[$r->band][$g] += (int) $r->c;
            $out[$r->band]['total'] += (int) $r->c;
        }
        return $out;
    }

    /** Effectif par sexe (incluant non renseigné). */
    public function genderCounts(array $f = []): array
    {
        $rows = $this->applyCommonFilters($this->base(), $f)
            ->selectRaw('personnel.sexe, COUNT(*) as c')->groupBy('personnel.sexe')->get();
        $out = ['Féminin' => 0, 'Masculin' => 0, 'nr' => 0];
        foreach ($rows as $r) {
            $g = isset(self::GENDERS[$r->sexe]) ? $r->sexe : 'nr';
            $out[$g] += (int) $r->c;
        }
        return $out;
    }

    public function contractTypeCounts(array $f = []): array
    {
        $q = $this->applyCommonFilters($this->base(), $f);
        $stag = (clone $q)->where('personnel.statut', 'Stagiaire')->count();
        $rows = (clone $q)->where(function (Builder $w) {
                $w->whereNull('personnel.statut')->orWhere('personnel.statut', '!=', 'Stagiaire');
            })
            ->selectRaw('personnel.type_contrat, COUNT(*) as c')->groupBy('personnel.type_contrat')->get();
        $out = ['CDI' => 0, 'CDD' => 0, 'Stage' => $stag, 'Non renseigné' => 0];
        foreach ($rows as $r) {
            $k = $r->type_contrat ?: 'Non renseigné';
            $out[$k] = ($out[$k] ?? 0) + (int) $r->c;
        }
        return array_filter($out, fn ($v) => $v > 0) ?: ['Non renseigné' => 0];
    }

    /** Postes / fonctions d'un périmètre avec effectifs. */
    public function postes(array $f = []): Collection
    {
        return $this->applyCommonFilters($this->base()->whereNotNull('personnel.poste_actuel'), $f)
            ->selectRaw('personnel.poste_actuel as poste, COUNT(*) as c')
            ->groupBy('personnel.poste_actuel')->orderByDesc('c')->orderBy('poste')->get();
    }

    public function departements(): Collection
    {
        return $this->base()->whereNotNull('direction_service')
            ->selectRaw('direction_service as nom, COUNT(*) as c')
            ->groupBy('direction_service')->orderBy('nom')->get();
    }

    public function services(): Collection
    {
        return $this->base()->whereNotNull('poste_actuel')
            ->selectRaw('poste_actuel as nom, COUNT(*) as c')
            ->groupBy('poste_actuel')->orderBy('nom')->get();
    }

    /** Contrats CDD/stage générés via Documents RH dont la date de fin n'est pas reportée sur la fiche. */
    public function contratsDocumentsNonReportes(int $days = self::DEFAULT_CONTRACT_HORIZON_DAYS): Collection
    {
        $from = Carbon::today();
        $to   = Carbon::today()->addDays($days);

        return RhDocument::query()
            ->whereIn('type', ['contrat-cdd', 'contrat-stagiaire'])
            ->whereNotNull('personnel_id')
            ->with('personnel')
            ->latest()
            ->get()
            ->filter(function (RhDocument $d) use ($from, $to) {
                $p = $d->personnel;
                if (!$p || $p->date_fin_contrat) return false;
                try {
                    $fin = isset($d->data['date_fin']) ? Carbon::parse($d->data['date_fin']) : null;
                } catch (\Throwable) {
                    return false;
                }
                return $fin && $fin->between($from, $to);
            })
            ->unique('personnel_id')
            ->values();
    }
}
