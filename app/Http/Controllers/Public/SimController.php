<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SimDataAccessRequest;
use App\Models\SimReport;
use App\Services\SimAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SimController extends Controller
{
    public function __construct(
        protected SimAnalyticsService $analytics
    ) {
    }

    /**
     * Page Présentation du SIM / Rapport d'atelier (contexte, objectifs, dispositif, marchés, produits, fiche de collecte)
     */
    public function presentation()
    {
        return view('public.sim.presentation');
    }

    /**
     * Afficher les rapports SIM publics
     */
    public function index(Request $request)
    {
        // Vérifier si la table existe avant de faire la requête
        if (!\Schema::hasTable('sim_reports')) {
            $reports = collect([]);
            return view('public.sim.index', compact('reports'));
        }
        
        $query = SimReport::public();

        // Filtres
        if ($request->filled('report_type')) {
            $query->byType($request->report_type);
        }

        if ($request->filled('region')) {
            $query->byRegion($request->region);
        }

        if ($request->filled('market_sector')) {
            $query->bySector($request->market_sector);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $reports = $query->with('generator')
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // Dernières actualités (si disponibles)
        try {
            $latestNews = \App\Models\News::where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->take(2)
                ->get();
        } catch (\Throwable $e) {
            $latestNews = collect();
        }

        return view('public.sim.index', compact('reports', 'latestNews'));
    }

    /**
     * Afficher un rapport SIM public
     */
    public function show(SimReport $simReport)
    {
        // Vérifier que le rapport est public
        if (!$simReport->isPublic()) {
            abort(404);
        }

        $simReport->incrementViewCount();
        
        return view('public.sim.show', compact('simReport'));
    }

    /**
     * Télécharger un rapport SIM public
     */
    public function download(SimReport $simReport)
    {
        // Vérifier que le rapport est public
        if (!$simReport->isPublic()) {
            abort(404);
        }

        $simReport->incrementDownloadCount();

        // Si un PDF est disponible, proposer le téléchargement du fichier
        if ($simReport->document_file) {
            $path = storage_path('app/public/' . $simReport->document_file);
            if (file_exists($path)) {
                return response()->download($path, basename($path), [
                    'Content-Type' => 'application/pdf'
                ]);
            }
        }

        // Sinon, fallback: générer un contenu texte
        $content = $this->generateReportContent($simReport);
        $filename = 'rapport_sim_' . $simReport->id . '_' . now()->format('Y-m-d') . '.txt';
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Générer le contenu du rapport pour téléchargement
     */
    protected function generateReportContent(SimReport $simReport)
    {
        $content = "RAPPORT SIM - SURVEILLANCE DES INDICATEURS DE MARCHÉ\n";
        $content .= "=" . str_repeat("=", 50) . "\n\n";
        
        $content .= "Titre: {$simReport->title}\n";
        $content .= "Type: {$simReport->report_type_label}\n";
        $content .= "Période: {$simReport->formatted_period}\n";
        $content .= "Région: " . ($simReport->region ?: 'Toutes') . "\n";
        $content .= "Secteur: {$simReport->sector_label}\n";
        $content .= "Publié le: {$simReport->published_at->format('d/m/Y H:i')}\n";
        $content .= "Généré par: " . ($simReport->generator ? $simReport->generator->name : 'Système') . "\n\n";
        
        if ($simReport->description) {
            $content .= "DESCRIPTION\n";
            $content .= "-" . str_repeat("-", 20) . "\n";
            $content .= $simReport->description . "\n\n";
        }
        
        if ($simReport->summary) {
            $content .= "RÉSUMÉ\n";
            $content .= "-" . str_repeat("-", 20) . "\n";
            $content .= $simReport->summary . "\n\n";
        }
        
        if ($simReport->recommendations) {
            $content .= "RECOMMANDATIONS\n";
            $content .= "-" . str_repeat("-", 20) . "\n";
            $content .= $simReport->recommendations . "\n\n";
        }
        
        $content .= "---\n";
        $content .= "Rapport généré automatiquement par la plateforme CSAR\n";
        $content .= "Date de génération: " . now()->format('d/m/Y H:i:s') . "\n";
        
        return $content;
    }

    /**
     * Afficher la page des magasins de stockage
     */
    public function magasins()
    {
        // Récupérer les entrepôts actifs
        $warehouses = \App\Models\Warehouse::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                    'lat' => $warehouse->latitude,
                    'lng' => $warehouse->longitude,
                    'capacity' => $warehouse->capacity,
                    'type' => 'warehouse'
                ];
            });

        // Statistiques générales
        $stats = [
            'total_warehouses' => $warehouses->count(),
            'total_capacity' => $warehouses->sum('capacity'),
            'regions_covered' => 14,
            'availability' => '24/7'
        ];

        return view('public.sim.magasins', compact('warehouses', 'stats'));
    }

    /**
     * Afficher la page des approvisionnements
     */
    public function supply()
    {
        return view('public.sim.supply');
    }

    /**
     * Afficher la page régionale
     */
    public function regional()
    {
        return view('public.sim.regional');
    }

    /**
     * Afficher la page des distributions
     */
    public function distributions()
    {
        return view('public.sim.distributions');
    }

    /**
     * Afficher la page des opérations
     */
    public function operations()
    {
        return view('public.sim.operations');
    }

    public function dashboard()
    {
        $overview = $this->analytics->adminOverview();
        $prices = $this->analytics->publicPrices();
        $latestReports = Schema::hasTable('sim_reports')
            ? SimReport::public()->latest('published_at')->take(6)->get()
            : collect();
        $marketsForMap = $this->analytics->getMarketsForMap();
        $priceTable = $this->analytics->getPriceConsultationData(null, null, null, 15);

        return view('public.sim.dashboard', compact('overview', 'prices', 'latestReports', 'marketsForMap', 'priceTable'));
    }

    /** Consultation des prix & Export de données (onglets catégories, recherche, tri, pagination, export) — 100 % BDD */
    public function consultationPrix(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : (int) now()->year;
        $month = $request->filled('month') ? (int) $request->month : null;
        $region = $request->filled('region') ? $request->region : null;
        $categoryId = $request->filled('category') ? (int) $request->category : null;
        $search = $request->get('search');
        $sortCol = $request->get('sort', 'year');
        $sortDir = $request->get('dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $result = $this->analytics->getPriceConsultationData($year, $month, $region, $categoryId, $search, $sortCol, $sortDir, $perPage);
        $regions = \Illuminate\Support\Facades\Schema::hasTable('sim_regions')
            ? \App\Models\SimRegion::orderBy('name')->pluck('name', 'name')->toArray()
            : [];
        $categories = $this->analytics->getProductCategoriesForTabs();

        return view('public.sim.consultation-prix', [
            'data' => $result['data'],
            'total' => $result['total'],
            'paginator' => $result['paginator'],
            'year' => $year,
            'month' => $month,
            'region' => $region,
            'regions' => $regions,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'search' => $search,
            'sortCol' => $sortCol,
            'sortDir' => $sortDir,
            'perPage' => $perPage,
        ]);
    }

    /** Carte des marchés SIM */
    public function carteMarches()
    {
        $markets = $this->analytics->getMarketsForMap();
        return view('public.sim.carte-marches', compact('markets'));
    }

    public function prices(Request $request)
    {
        $year = (int) ($request->get('year') ?: now()->year);
        $prices = $this->analytics->publicPrices($year);

        return view('public.sim.prices', compact('prices', 'year'));
    }

    public function requestAccess()
    {
        return view('public.sim.request-access');
    }

    public function storeAccessRequest(Request $request)
    {
        $validated = $request->validate([
            'requester_name' => 'required|string|max:255',
            'organization' => 'required|string|max:255',
            'role_title' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'request_subject' => 'required|string|max:255',
            'requested_scope' => 'required|string|max:30',
            'requested_data_types' => 'nullable|array',
            'requested_data_types.*' => 'string|max:100',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'purpose' => 'required|string|max:5000',
        ]);

        SimDataAccessRequest::create($validated);

        return redirect()
            ->route('sim.request-access', ['locale' => app()->getLocale()])
            ->with('success', 'Votre demande d\'accès aux données a été envoyée avec succès.');
    }
}