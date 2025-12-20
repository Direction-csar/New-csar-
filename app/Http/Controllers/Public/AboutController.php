<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AboutController extends Controller
{
    /**
     * Afficher la page À propos publique
     */
    public function index()
    {
        try {
            // Récupérer les statistiques depuis la nouvelle table chiffres_cles
            $stats = $this->getPublicStats();

            Log::info('Accès à la page À propos publique', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return view('public.about.index', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la page À propos publique', [
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            // En cas d'erreur, utiliser des données par défaut
            $stats = $this->getDefaultStats();
            return view('public.about.index', compact('stats'));
        }
    }

    /**
     * API pour récupérer les statistiques publiques
     */
    public function getStats()
    {
        try {
            $stats = $this->getPublicStats();
            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Erreur API statistiques publiques', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Récupérer les statistiques publiques depuis la nouvelle table chiffres_cles
     */
    private function getPublicStats()
    {
        try {
            // PERF: cache stats to avoid repeated DB/schema work on every request
            return Cache::remember('public_about_stats_v1', now()->addMinutes(10), function () {
            // Vérifier si la table existe avant de l'utiliser
            if (!\Illuminate\Support\Facades\Schema::hasTable('chiffres_cles')) {
                Log::warning('Table chiffres_cles n\'existe pas, utilisation des données par défaut');
                return $this->getDefaultStats();
            }
            
            // Essayer de récupérer les chiffres clés, avec fallback en cas d'erreur
            try {
                $chiffresCles = \App\Models\ChiffreCle::safeGetActifs()->keyBy('titre');
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la récupération des chiffres clés, utilisation des données par défaut', [
                    'error' => $e->getMessage()
                ]);
                return $this->getDefaultStats();
            }
                
            return [
                'agents' => [
                    'value' => $chiffresCles->get('Agents mobilisés', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-users',
                    'description' => 'Agents recensés',
                    'color' => '#22c55e'
                ],
                'entrepots' => [
                    'value' => $chiffresCles->get('Entrepôts de stockage', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-warehouse',
                    'description' => 'Magasins de stockage',
                    'color' => '#3b82f6'
                ],
                'capacite_tonnes' => [
                    'value' => $chiffresCles->get('Capacité en tonnes', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-weight-hanging',
                    'description' => 'Capacité (tonnes)',
                    'color' => '#f59e0b'
                ],
                'regions' => [
                    'value' => $chiffresCles->get('Régions couvertes', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-map-marker-alt',
                    'description' => 'Nombre de régions',
                    'color' => '#8b5cf6'
                ],
                'annees_experience' => [
                    'value' => $chiffresCles->get('Années d\'expérience', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-calendar-alt',
                    'description' => 'Années d\'expérience',
                    'color' => '#06b6d4'
                ],
                'demandes_traitees' => [
                    'value' => $chiffresCles->get('Demandes traitées', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-chart-bar',
                    'description' => 'Demandes traitées',
                    'color' => '#ef4444'
                ],
                'taux_satisfaction' => [
                    'value' => $chiffresCles->get('Taux de satisfaction', (object)['valeur' => '0'])->valeur ?? '0',
                    'icon' => 'fas fa-star',
                    'description' => 'Taux de satisfaction',
                    'color' => '#10b981'
                ]
            ];
            });
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des chiffres clés', [
                'error' => $e->getMessage()
            ]);
            return $this->getDefaultStats();
        }
    }

    /**
     * Données par défaut en cas d'erreur
     */
    private function getDefaultStats()
    {
        return [
            'agents' => [
                'value' => '0',
                'icon' => 'fas fa-users',
                'description' => 'Agents recensés',
                'color' => '#22c55e'
            ],
            'entrepots' => [
                'value' => '0',
                'icon' => 'fas fa-warehouse',
                'description' => 'Magasins de stockage',
                'color' => '#3b82f6'
            ],
            'capacite_tonnes' => [
                'value' => '0',
                'icon' => 'fas fa-weight-hanging',
                'description' => 'Capacité (tonnes)',
                'color' => '#f59e0b'
            ],
            'regions' => [
                'value' => '0',
                'icon' => 'fas fa-map-marker-alt',
                'description' => 'Nombre de régions',
                'color' => '#8b5cf6'
            ],
            'annees_experience' => [
                'value' => '0',
                'icon' => 'fas fa-calendar-alt',
                'description' => 'Années d\'expérience',
                'color' => '#06b6d4'
            ],
            'demandes_traitees' => [
                'value' => '0',
                'icon' => 'fas fa-chart-bar',
                'description' => 'Demandes traitées',
                'color' => '#ef4444'
            ],
            'taux_satisfaction' => [
                'value' => '0',
                'icon' => 'fas fa-star',
                'description' => 'Taux de satisfaction',
                'color' => '#10b981'
            ]
        ];
    }
}