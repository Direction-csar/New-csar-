<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Convertir le nom du rôle en role_id
     */
    private function getRoleIdFromRoleName($roleName)
    {
        $roleMap = [
            'admin' => 1,
            'dg' => 2,
            'responsable' => 3,
            'agent' => 4,
            'drh' => 5,
            'entrepot' => 3 // Alias pour responsable
        ];
        
        return $roleMap[$roleName] ?? 4; // Par défaut: agent
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'actif' ? 1 : 0);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Tri
            $sortBy = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $users = $query->paginate(15);

            // Statistiques
            $stats = $this->getUsersStats();

            // Données pour les graphiques
            $chartData = $this->getChartData();

            return view('admin.users.index', compact('users', 'stats', 'chartData'));
        } catch (\Exception $e) {
            Log::error('Erreur dans UserController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des utilisateurs.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:admin,dg,drh,entrepot,agent',
            'is_active' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'role_id' => $this->getRoleIdFromRoleName($request->role),
                'is_active' => $request->is_active,
                'password' => Hash::make('password123') // Mot de passe par défaut
            ]);

            // Créer une notification
            Notification::create([
                'type' => 'user_created',
                'title' => 'Nouvel utilisateur créé',
                'message' => "Un nouvel utilisateur {$user->name} a été créé avec le rôle {$user->role}",
                'data' => ['user_id' => $user->id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'utilisateur.');
        }
    }

    /**
     * Afficher un utilisateur spécifique
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'utilisateur: ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                ->with('error', 'Utilisateur non trouvé.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit', compact('user'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire d\'édition: ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                ->with('error', 'Utilisateur non trouvé.');
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:admin,dg,drh,entrepot,agent',
            'is_active' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'role_id' => $this->getRoleIdFromRoleName($request->role),
                'is_active' => $request->is_active
            ]);

            // Créer une notification
            Notification::create([
                'type' => 'user_updated',
                'title' => 'Utilisateur modifié',
                'message' => "L'utilisateur {$user->name} a été modifié",
                'data' => ['user_id' => $user->id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de l\'utilisateur.');
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $userName = $user->name;
            $user->delete();

            // Créer une notification
            Notification::create([
                'type' => 'user_deleted',
                'title' => 'Utilisateur supprimé',
                'message' => "L'utilisateur {$userName} a été supprimé",
                'data' => ['user_id' => $id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de l\'utilisateur.');
        }
    }

    /**
     * Basculer le statut d'un utilisateur
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activé' : 'désactivé';
            
            return response()->json([
                'success' => true,
                'message' => "Utilisateur {$status} avec succès.",
                'is_active' => $user->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du basculement du statut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du basculement du statut.'
            ], 500);
        }
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->password = Hash::make('password123');
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès. Nouveau mot de passe: password123'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation du mot de passe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe.'
            ], 500);
        }
    }

    /**
     * Exporter les utilisateurs
     */
    public function export(Request $request)
    {
        try {
            $query = User::query();

            // Appliquer les mêmes filtres que dans index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'actif' ? 1 : 0);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            // Vérifier s'il y a des données à exporter
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée à exporter pour le moment.'
                ], 404);
            }

            // Générer le nom du fichier
            $filename = 'utilisateurs_csar_' . now()->format('Y-m-d_H-i-s') . '.csv';

            // Créer le contenu CSV
            $csvContent = "\xEF\xBB\xBF"; // BOM pour UTF-8
            $csvContent .= "Nom Complet,Email,Téléphone,Rôle,Statut,Date d'inscription\n";

            foreach ($users as $user) {
                $csvContent .= '"' . str_replace('"', '""', $user->name) . '",';
                $csvContent .= '"' . str_replace('"', '""', $user->email) . '",';
                $csvContent .= '"' . str_replace('"', '""', $user->phone ?? 'N/A') . '",';
                $csvContent .= '"' . str_replace('"', '""', ucfirst($user->role)) . '",';
                $csvContent .= '"' . str_replace('"', '""', $user->is_active ? 'Actif' : 'Inactif') . '",';
                $csvContent .= '"' . str_replace('"', '""', $user->created_at->format('d/m/Y H:i')) . '"';
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
            Log::error('Erreur lors de l\'export des utilisateurs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export des utilisateurs.'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des utilisateurs
     */
    private function getUsersStats()
    {
        try {
            return [
                'total' => User::count(),
                'active' => User::where('is_active', 1)->count(),
                'inactive' => User::where('is_active', 0)->count(),
                'admin' => User::where('role', 'admin')->count(),
                'dg' => User::where('role', 'dg')->count(),
                'drh' => User::where('role', 'drh')->count(),
                'entrepot' => User::where('role', 'entrepot')->count(),
                'agent' => User::where('role', 'agent')->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'new_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des statistiques utilisateurs: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'admin' => 0,
                'dg' => 0,
                'drh' => 0,
                'entrepot' => 0,
                'agent' => 0,
                'new_today' => 0,
                'new_this_week' => 0,
                'new_this_month' => 0
            ];
        }
    }

    /**
     * Obtenir les données pour les graphiques
     */
    private function getChartData()
    {
        try {
            // Données pour le graphique des rôles
            $rolesData = User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role')
                ->toArray();

            // Données pour le graphique des inscriptions par mois (6 derniers mois)
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $count = User::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
                $monthlyData[] = [
                    'month' => $date->format('M Y'),
                    'count' => $count
                ];
            }

            return [
                'roles' => $rolesData,
                'monthly_registrations' => $monthlyData
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des données graphiques: ' . $e->getMessage());
            return [
                'roles' => [],
                'monthly_registrations' => []
            ];
        }
    }
}