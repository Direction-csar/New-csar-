<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SimReport;
use App\Models\News;
use Illuminate\Http\Request;

/**
 * Page publique Projets et Interventions – actions concrètes du CSAR.
 * Conforme au cahier des charges : projets nationaux, résultats par région, documents.
 */
class ProjetsController extends Controller
{
    public function index(Request $request)
    {
        // Projets nationaux (structure pour affichage ; à terme depuis BDD ou config)
        $projets = [
            [
                'titre' => 'Renforcement du SIM (Système d’Information sur les Marchés)',
                'description' => 'Modernisation et déploiement du système d’information sur les marchés céréaliers pour une meilleure transparence des prix et une aide à la décision.',
                'lien_sim' => true,
                'icon' => 'chart-line',
            ],
            [
                'titre' => 'Achats locaux et soutien aux producteurs',
                'description' => 'Programmes d’achats locaux pour renforcer les filières céréalières et soutenir les producteurs sénégalais.',
                'lien_sim' => false,
                'icon' => 'store',
            ],
            [
                'titre' => 'Réponses d’urgence et résilience',
                'description' => 'Coordination des interventions d’urgence en cas de crise alimentaire et renforcement de la résilience des populations.',
                'lien_sim' => false,
                'icon' => 'truck-loading',
            ],
        ];

        // Rapports / études récents (PDF, bilans)
        $rapports = SimReport::public()
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();

        $publications = News::publications()
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();

        // Régions pour indicateurs (liste standard Sénégal)
        $regions = [
            'Dakar', 'Thiès', 'Diourbel', 'Fatick', 'Kaolack', 'Kolda', 'Louga',
            'Matam', 'Saint-Louis', 'Tambacounda', 'Ziguinchor', 'Kaffrine', 'Kédougou', 'Sédhiou',
        ];

        return view('public.projets.index', compact('projets', 'rapports', 'publications', 'regions'));
    }
}
