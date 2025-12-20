<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EntrepotsController extends Controller
{
    /**
     * Afficher la liste des entrepôts
     */
    public function index(Request $request)
    {
        try {
            $query = Warehouse::query();

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('region', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('region')) {
                $query->where('region', $request->region);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $entrepots = $query->paginate(15);

            // Statistiques
            $stats = $this->getWarehousesStats();

            // Données pour les graphiques
            $chartData = $this->getChartData();

            return view('admin.entrepots.index', compact('entrepots', 'stats', 'chartData'));
        } catch (\Exception $e) {
            Log::error('Erreur dans EntrepotsController@index: ' . $e->getMessage());
            
            // En cas d'erreur, retourner une vue avec des données vides
            $entrepots = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $stats = [
                'total' => 0,
                'actifs' => 0,
                'inactifs' => 0,
                'maintenance' => 0,
                'capacite_totale' => 0,
                'occupation_moyenne' => 0,
                'ce_mois' => 0
            ];
            $chartData = [
                'regions' => [],
                'types' => [],
                'statuts' => [],
                'occupation' => []
            ];
            
            return view('admin.entrepots.index', compact('entrepots', 'stats', 'chartData'))
                ->with('error', 'Erreur lors du chargement des entrepôts. Veuillez vérifier la connexion à la base de données.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.entrepots.create');
    }

    /**
     * Enregistrer un nouvel entrepôt
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'capacite' => 'required|numeric|min:0',
            'unite_capacite' => 'required|string|max:50',
            'type' => 'required|string|in:principal,secondaire,depot',
            'statut' => 'required|string|in:actif,inactif,maintenance',
            'responsable' => 'required|string|max:255',
            'telephone_responsable' => 'required|string|max:20',
            'email_responsable' => 'required|email|max:255',
            'description' => 'nullable|string',
            'date_creation' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $entrepot = Warehouse::create([
                'name' => $request->nom,
                'address' => $request->adresse,
                'city' => $request->ville,
                'region' => $request->region,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'capacity' => $request->capacite,
                'capacity_unit' => $request->unite_capacite,
                'occupancy_rate' => 0,
                'type' => $request->type,
                'status' => $request->statut,
                'manager_name' => $request->responsable,
                'manager_phone' => $request->telephone_responsable,
                'email' => $request->email_responsable,
                'description' => $request->description,
                'created_date' => $request->date_creation,
                'is_active' => $request->statut === 'actif' ? 1 : 0
            ]);

            // Créer une notification
            Notification::create([
                'type' => 'entrepot_created',
                'title' => 'Nouvel entrepôt créé',
                'message' => "Un nouvel entrepôt {$entrepot->name} a été créé à {$entrepot->city}",
                'data' => ['entrepot_id' => $entrepot->id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.entrepots.index')
                ->with('success', 'Entrepôt créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'entrepôt: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'entrepôt.');
        }
    }

    /**
     * Afficher un entrepôt spécifique
     */
    public function show($id)
    {
        try {
            $entrepot = Warehouse::findOrFail($id);
            return view('admin.entrepots.show', compact('entrepot'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'entrepôt: ' . $e->getMessage());
            return redirect()->route('admin.entrepots.index')
                ->with('error', 'Entrepôt non trouvé.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $entrepot = Warehouse::findOrFail($id);
            return view('admin.entrepots.edit', compact('entrepot'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'édition de l\'entrepôt: ' . $e->getMessage());
            return redirect()->route('admin.entrepots.index')
                ->with('error', 'Entrepôt non trouvé.');
        }
    }

    /**
     * Mettre à jour un entrepôt
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'capacite' => 'required|numeric|min:0',
            'unite_capacite' => 'required|string|max:50',
            'taux_occupation' => 'nullable|numeric|min:0|max:100',
            'type' => 'required|string|in:principal,secondaire,depot',
            'statut' => 'required|string|in:actif,inactif,maintenance',
            'responsable' => 'required|string|max:255',
            'telephone_responsable' => 'required|string|max:20',
            'email_responsable' => 'required|email|max:255',
            'description' => 'nullable|string',
            'date_creation' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $entrepot = Warehouse::findOrFail($id);
            $oldStatus = $entrepot->status;

            $entrepot->update([
                'name' => $request->nom,
                'address' => $request->adresse,
                'city' => $request->ville,
                'region' => $request->region,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'capacity' => $request->capacite,
                'status' => $request->statut === 'actif' ? 'active' : 'inactive',
                'email' => $request->email_responsable,
                'phone' => $request->telephone_responsable,
                'description' => $request->description,
                'is_active' => $request->statut === 'actif' ? true : false
            ]);

            // Créer une notification si le statut a changé
            if ($oldStatus !== $request->statut) {
                Notification::create([
                    'type' => 'entrepot_updated',
                    'title' => 'Entrepôt mis à jour',
                    'message' => "L'entrepôt {$entrepot->name} a été mis à jour (statut: {$entrepot->status})",
                    'data' => ['entrepot_id' => $entrepot->id],
                    'user_id' => null
                ]);
            }

            DB::commit();

            return redirect()->route('admin.entrepots.index')
                ->with('success', 'Entrepôt mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'entrepôt: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de l\'entrepôt.');
        }
    }

    /**
     * Supprimer un entrepôt
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $entrepot = Warehouse::findOrFail($id);
            $nom = $entrepot->name;
            $entrepot->delete();

            // Créer une notification
            Notification::create([
                'type' => 'entrepot_deleted',
                'title' => 'Entrepôt supprimé',
                'message' => "L'entrepôt {$nom} a été supprimé",
                'data' => ['entrepot_id' => $id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.entrepots.index')
                ->with('success', 'Entrepôt supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'entrepôt: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de l\'entrepôt.');
        }
    }

    /**
     * Toggle le statut d'un entrepôt
     */
    public function toggleStatus($id)
    {
        try {
            $entrepot = Warehouse::findOrFail($id);
            $oldStatus = $entrepot->status;
            $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';
            
            $entrepot->update(['status' => $newStatus]);

            // Créer une notification
            Notification::create([
                'type' => 'entrepot_status_changed',
                'title' => 'Statut d\'entrepôt modifié',
                'message' => "L'entrepôt {$entrepot->name} est maintenant {$newStatus}",
                'data' => ['entrepot_id' => $entrepot->id],
                'user_id' => null
            ]);

            return response()->json([
                'success' => true,
                'new_status' => $newStatus,
                'message' => "Statut changé vers {$newStatus}"
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Obtenir les statistiques des entrepôts
     */
    private function getWarehousesStats()
    {
        try {
            // Statistiques de base
            $total = Warehouse::count();
            $actifs = Warehouse::where('is_active', true)->count();
            $inactifs = Warehouse::where('is_active', false)->count();
            $maintenance = Warehouse::where('status', 'maintenance')->count();
            $capacite_totale = Warehouse::sum('capacity') ?? 0;
            $occupation_moyenne = Warehouse::avg('current_stock') ?? 0;
            $ce_mois = Warehouse::whereMonth('created_at', now()->month)->count();

            // Données par région pour le graphique
            $regionsData = Warehouse::select('region', DB::raw('count(*) as count'))
                ->groupBy('region')
                ->pluck('count', 'region')
                ->toArray();

            // Données d'occupation pour le graphique
            $occupationData = [
                'occupation_0_20' => Warehouse::whereBetween('current_stock', [0, 20])->count(),
                'occupation_21_40' => Warehouse::whereBetween('current_stock', [21, 40])->count(),
                'occupation_41_60' => Warehouse::whereBetween('current_stock', [41, 60])->count(),
                'occupation_61_80' => Warehouse::whereBetween('current_stock', [61, 80])->count(),
                'occupation_81_100' => Warehouse::whereBetween('current_stock', [81, 100])->count()
            ];

            return [
                'total' => $total,
                'actifs' => $actifs,
                'inactifs' => $inactifs,
                'maintenance' => $maintenance,
                'capacite_totale' => $capacite_totale,
                'occupation_moyenne' => $occupation_moyenne,
                'ce_mois' => $ce_mois,
                // Données pour les graphiques
                'dakar' => $regionsData['Dakar'] ?? 0,
                'thies' => $regionsData['Thiès'] ?? 0,
                'kaolack' => $regionsData['Kaolack'] ?? 0,
                'saint_louis' => $regionsData['Saint-Louis'] ?? 0,
                'ziguinchor' => $regionsData['Ziguinchor'] ?? 0,
                'autres' => array_sum($regionsData) - ($regionsData['Dakar'] ?? 0) - ($regionsData['Thiès'] ?? 0) - ($regionsData['Kaolack'] ?? 0) - ($regionsData['Saint-Louis'] ?? 0) - ($regionsData['Ziguinchor'] ?? 0),
                // Données d'occupation
                'occupation_0_20' => $occupationData['occupation_0_20'],
                'occupation_21_40' => $occupationData['occupation_21_40'],
                'occupation_41_60' => $occupationData['occupation_41_60'],
                'occupation_61_80' => $occupationData['occupation_61_80'],
                'occupation_81_100' => $occupationData['occupation_81_100']
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans getEntrepotsStats: ' . $e->getMessage());
            return [
                'total' => 0,
                'actifs' => 0,
                'inactifs' => 0,
                'maintenance' => 0,
                'capacite_totale' => 0,
                'occupation_moyenne' => 0,
                'ce_mois' => 0,
                'dakar' => 0,
                'thies' => 0,
                'kaolack' => 0,
                'saint_louis' => 0,
                'ziguinchor' => 0,
                'autres' => 0,
                'occupation_0_20' => 0,
                'occupation_21_40' => 0,
                'occupation_41_60' => 0,
                'occupation_61_80' => 0,
                'occupation_81_100' => 0
            ];
        }
    }

    /**
     * Obtenir les données pour les graphiques
     */
    private function getChartData()
    {
        try {
            return [
                'regions' => Warehouse::select('region', DB::raw('count(*) as count'))
                    ->groupBy('region')
                    ->pluck('count', 'region')
                    ->toArray(),
                'statuses' => Warehouse::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray(),
                'occupation' => Warehouse::select('name', 'current_stock')
                    ->orderBy('current_stock', 'desc')
                    ->limit(10)
                    ->pluck('current_stock', 'name')
                    ->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans getChartData: ' . $e->getMessage());
            return [
                'regions' => [],
                'statuses' => [],
                'occupation' => []
            ];
        }
    }

    /**
     * Exporter la liste des entrepôts
     */
    public function export(Request $request)
    {
        try {
            // Récupérer tous les entrepôts (sans pagination)
            $query = Warehouse::query();

            // Appliquer les mêmes filtres que dans index()
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('region', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('region')) {
                $query->where('region', $request->region);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $entrepots = $query->orderBy('created_at', 'desc')->get();

            // Vérifier s'il y a des données à exporter
            if ($entrepots->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée à exporter pour le moment.'
                ], 404);
            }

            // Générer le nom du fichier
            $filename = 'entrepots_csar_' . now()->format('Y-m-d_H-i-s') . '.csv';

            // Créer le contenu CSV
            $csvContent = "\xEF\xBB\xBF"; // BOM pour UTF-8
            $csvContent .= "Nom,Adresse,Ville,Région,Capacité,Unité,Taux Occupation,Type,Statut,Responsable,Téléphone,Email,Date Création\n";

            foreach ($entrepots as $entrepot) {
                $csvContent .= '"' . str_replace('"', '""', $entrepot->nom) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->adresse) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->ville) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->region) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->capacite) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->unite_capacite) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->taux_occupation ?? 0) . '",';
                $csvContent .= '"' . str_replace('"', '""', ucfirst($entrepot->type)) . '",';
                $csvContent .= '"' . str_replace('"', '""', ucfirst($entrepot->statut)) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->responsable) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->telephone_responsable) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->email_responsable) . '",';
                $csvContent .= '"' . str_replace('"', '""', $entrepot->date_creation->format('d/m/Y')) . '"';
                $csvContent .= "\n";
            }

            // Retourner le fichier CSV
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export des entrepôts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des entrepôts.'
            ], 500);
        }
    }
}