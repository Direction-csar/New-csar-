<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Services\Drh\HrAnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consultation détaillée des indicateurs du tableau de bord DRH.
 * Toutes les routes sont protégées par le middleware drh-access (guard drh).
 */
class AnalyticsController extends Controller
{
    public function __construct(private readonly HrAnalyticsService $analytics)
    {
    }

    private function filters(Request $request): array
    {
        $v = $request->validate([
            'q'            => 'nullable|string|max:100',
            'departement'  => 'nullable|string|max:255',
            'service'      => 'nullable|string|max:255',
            'sexe'         => 'nullable|in:Féminin,Masculin,nr',
            'type_contrat' => 'nullable|in:CDI,CDD,Stage',
            'tranche'      => 'nullable|string|max:20',
            'horizon'      => 'nullable|integer|min:1|max:3650',
            'periode'      => 'nullable|in:atteint,a_venir',
            'annee'        => 'nullable|integer|min:2000|max:2100',
            'solde'        => 'nullable|in:positif,nul',
            'sort'         => 'nullable|string|max:30',
            'dir'          => 'nullable|in:asc,desc',
            'page'         => 'nullable|integer|min:1',
        ]);

        return array_filter($v, fn ($x) => $x !== null && $x !== '');
    }

    private function metricOrFail(string $metric): array
    {
        $defs = $this->analytics->metrics();
        abort_unless(isset($defs[$metric]), 404);
        return $defs[$metric];
    }

    public function show(Request $request, string $metric)
    {
        $def     = $this->metricOrFail($metric);
        $filters = $this->filters($request);
        $page    = (int) ($filters['page'] ?? 1);
        unset($filters['page']);

        $paginator = $this->analytics->paginate($metric, $filters, $page);
        $rows      = collect($paginator->items())->map(fn ($i) => $this->analytics->row($metric, $i));

        $extra = [];
        if ($metric === 'fins-contrats') {
            $extra['non_reportes'] = $this->analytics->contratsDocumentsNonReportes((int) ($filters['horizon'] ?? HrAnalyticsService::DEFAULT_CONTRACT_HORIZON_DAYS));
        }

        return view('admin.drh.analytics.show', [
            'metric'       => $metric,
            'def'          => $def,
            'labels'       => $this->analytics->columnLabels(),
            'filters'      => $filters,
            'paginator'    => $paginator,
            'rows'         => $rows,
            'departements' => $this->analytics->departements(),
            'services'     => $this->analytics->services(),
            'seniority'    => HrAnalyticsService::SENIORITY_BANDS,
            'ageBands'     => HrAnalyticsService::AGE_BANDS,
            'genders'      => HrAnalyticsService::GENDERS,
            'extra'        => $extra,
        ]);
    }

    public function exportCsv(Request $request, string $metric): StreamedResponse
    {
        $def     = $this->metricOrFail($metric);
        $filters = $this->filters($request);
        unset($filters['page']);

        $labels  = $this->analytics->columnLabels();
        $columns = array_values(array_diff($def['columns'], ['photo']));
        $items   = $this->analytics->all($metric, $filters);
        $file    = 'drh-' . $metric . '-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($items, $metric, $columns, $labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_map(fn ($c) => $labels[$c] ?? $c, $columns), ';');
            foreach ($items as $item) {
                $row = $this->analytics->row($metric, $item);
                fputcsv($out, array_map(fn ($c) => $row[$c] ?? '', $columns), ';');
            }
            fclose($out);
        }, $file, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ]);
    }

    public function print(Request $request, string $metric)
    {
        $def     = $this->metricOrFail($metric);
        $filters = $this->filters($request);
        unset($filters['page']);

        $columns = array_values(array_diff($def['columns'], ['photo', 'contact']));
        $items   = $this->analytics->all($metric, $filters, 2000);
        $rows    = $items->map(fn ($i) => $this->analytics->row($metric, $i));

        return view('admin.drh.analytics.print', [
            'metric'  => $metric,
            'def'     => $def,
            'labels'  => $this->analytics->columnLabels(),
            'columns' => $columns,
            'rows'    => $rows,
            'filters' => $filters,
        ]);
    }

    public function departement(Request $request, string $nom)
    {
        abort_unless($this->analytics->departements()->contains('nom', $nom), 404);
        return $this->scope($request, 'departement', $nom);
    }

    public function service(Request $request, string $nom)
    {
        abort_unless($this->analytics->services()->contains('nom', $nom), 404);
        return $this->scope($request, 'service', $nom);
    }

    /** Fiche synthétique d'un département ou d'un poste / fonction. */
    private function scope(Request $request, string $kind, string $nom)
    {
        $filters = $this->filters($request);
        $page    = (int) ($filters['page'] ?? 1);
        unset($filters['page']);

        $scope = [$kind => $nom];
        $f     = $scope + $filters;

        $paginator = $this->analytics->paginate('genre', $f, $page);
        $rows      = collect($paginator->items())->map(fn ($i) => $this->analytics->row('genre', $i));

        $total = $this->analytics->genreQuery($scope)->count();

        return view('admin.drh.analytics.scope', [
            'kind'          => $kind,
            'nom'           => $nom,
            'total'         => $total,
            'genres'        => $this->analytics->genderCounts($scope),
            'contrats'      => $this->analytics->contractTypeCounts($scope),
            'postes'        => $this->analytics->postes($scope),
            'departementsDuPoste' => $kind === 'service'
                ? $this->analytics->base()->where('poste_actuel', $nom)->whereNotNull('direction_service')
                    ->selectRaw('direction_service as nom, COUNT(*) as c')->groupBy('direction_service')->orderByDesc('c')->get()
                : collect(),
            'anciennete'    => $this->analytics->seniorityByGender($scope),
            'ages'          => $this->analytics->ageByGender($scope),
            'finsContrats'  => $this->analytics->finsContratsQuery($scope)->orderBy('date_fin_contrat')->limit(10)->get()
                ->map(fn ($p) => $this->analytics->row('fins-contrats', $p)),
            'nbFinsContrats' => $this->analytics->countFinsContrats(HrAnalyticsService::DEFAULT_CONTRACT_HORIZON_DAYS, $scope),
            'retraites'     => $this->analytics->retraitesQuery($scope)->orderBy('date_naissance')->limit(10)->get()
                ->map(fn ($p) => $this->analytics->row('retraites', $p)),
            'nbRetraites'   => $this->analytics->countRetraites(HrAnalyticsService::DEFAULT_RETIREMENT_HORIZON_MONTHS, $scope),
            'paginator'     => $paginator,
            'rows'          => $rows,
            'filters'       => $filters,
            'labels'        => $this->analytics->columnLabels(),
            'columns'       => ['nom', 'matricule', 'sexe', 'service', 'departement', 'statut', 'type_contrat', 'date_recrutement', 'anciennete'],
            'genderLabels'  => HrAnalyticsService::GENDERS,
            'seniority'     => HrAnalyticsService::SENIORITY_BANDS,
            'ageBands'      => HrAnalyticsService::AGE_BANDS,
        ]);
    }
}
