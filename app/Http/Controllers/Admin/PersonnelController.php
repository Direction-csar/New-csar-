<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PersonnelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Personnel::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('prenoms_nom', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('direction')) {
            $query->where('direction_service', $request->input('direction'));
        }

        if ($request->filled('poste')) {
            $query->where('poste_actuel', $request->input('poste'));
        }

        if ($request->filled('statut_validation')) {
            $query->where('statut_validation', $request->input('statut_validation'));
        }

        $personnel = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totalPersonnel = $query->count();
        $validatedCount = Personnel::where('statut_validation', 'Valide')->count();
        $pendingCount = Personnel::where('statut_validation', 'En attente')->count();
        $rejectedCount = Personnel::where('statut_validation', 'Rejete')->count();

        // Récupérer les directions et postes uniques pour les filtres
        $directions = Personnel::distinct()->pluck('direction_service')->filter()->sort()->values();
        $postes = Personnel::distinct()->pluck('poste_actuel')->filter()->sort()->values();

        // Compter le personnel par direction
        $directionCounts = Personnel::selectRaw('direction_service, COUNT(*) as count')
            ->groupBy('direction_service')
            ->pluck('count', 'direction_service')
            ->toArray();

        $view = request()->routeIs('admin.drh.*') ? 'admin.drh.personnel.index' : 'admin.personnel.index';
        return view($view, compact(
            'personnel',
            'totalPersonnel',
            'validatedCount',
            'pendingCount',
            'rejectedCount',
            'directions',
            'postes',
            'directionCounts'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $isDrh = request()->routeIs('admin.drh.*');
        $view = $isDrh ? 'admin.drh.personnel.create' : 'admin.personnel.create';
        $routePrefix = $isDrh ? 'admin.drh.personnel' : 'admin.personnel';
        return view($view, compact('routePrefix'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // I. Informations personnelles
            'prenoms_nom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'tranche_age' => 'required|in:18-25,26-35,36-45,46-55,56-60',
            'nationalite' => 'required|string|max:255',
            'numero_cni' => 'required|string|max:255',
            'sexe' => 'required|in:Masculin,Féminin',
            'situation_matrimoniale' => 'required|in:Célibataire,Marié (e),Divorcé (e),Veuf,Veuve',
            'nombre_enfants' => 'required|integer|min:0|max:10',
            'contact_telephonique' => 'required|string|max:20',
            'email' => 'required|email|unique:personnel,email',
            'groupe_sanguin' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'adresse_complete' => 'required|string',

            // II. Situation administrative
            'matricule' => 'nullable|string|unique:personnel,matricule',
            'date_recrutement_csar' => 'required|date',
            'date_prise_service_csar' => 'required|date',
            'statut' => 'required|string',
            'poste_actuel' => 'required|string',
            'direction_service' => 'required|string',
            'localisation_region' => 'nullable|string',

            // III. Parcours professionnel
            'dernier_poste_avant_csar' => 'required|string',
            'formations_professionnelles' => 'required|string',
            'diplome_academique' => 'required|string',
            'autres_diplomes_certifications' => 'nullable|string',

            // IV. Compétences spécifiques
            'logiciels_maitrises' => 'required|array',
            'langues_parlees' => 'required|array',
            'autres_aptitudes' => 'nullable|string',

            // V. Aspirations professionnelles
            'aspirations_professionnelles' => 'nullable|string',
            'details_aspirations' => 'nullable|string',
            'interet_nouvelles_responsabilites' => 'required|in:Oui,Non,Neutre',

            // VI. Photo personnelle
            'photo_personnelle' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',

            // VII. Taille vêtements
            'taille_vetements' => 'required|string',

            // VIII. Notification d'urgence
            'contact_urgence_nom' => 'required|string|max:255',
            'contact_urgence_telephone' => 'required|string|max:20',
            'contact_urgence_lien_parente' => 'required|string|max:255',

            // IX. Observations personnelles
            'observations_personnelles' => 'nullable|string',
        ]);

        // Gérer l'upload de la photo
        if ($request->hasFile('photo_personnelle')) {
            $photo = $request->file('photo_personnelle');
            $photoName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/personnel', $photoName);
            $validatedData['photo_personnelle'] = $photoName;
        }

        // Générer un matricule automatique si non fourni
        if (empty($validatedData['matricule'])) {
            $validatedData['matricule'] = 'CSAR-' . date('Y') . '-' . str_pad(Personnel::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        // Convertir les arrays en JSON
        $validatedData['logiciels_maitrises'] = json_encode($validatedData['logiciels_maitrises']);
        $validatedData['langues_parlees'] = json_encode($validatedData['langues_parlees']);

        // Gérer les aspirations professionnelles (peut être un array de checkboxes)
        if (isset($validatedData['aspirations_professionnelles'])) {
            if (is_array($validatedData['aspirations_professionnelles'])) {
                $validatedData['aspirations_professionnelles'] = implode(', ', $validatedData['aspirations_professionnelles']);
            }
        }

        // Définir le statut par défaut
        $validatedData['statut_validation'] = 'En attente';

        Personnel::create($validatedData);

        $redirectRoute = request()->routeIs('admin.drh.*') ? 'admin.drh.personnel.index' : 'admin.personnel.index';
        return redirect()->route($redirectRoute)->with('success', 'Personnel ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Personnel $personnel)
    {
        $isDrh = request()->routeIs('admin.drh.*');
        $view = $isDrh ? 'admin.drh.personnel.show' : 'admin.personnel.show';
        $routePrefix = $isDrh ? 'admin.drh.personnel' : 'admin.personnel';
        return view($view, compact('personnel', 'routePrefix'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $personnel = Personnel::findOrFail($id);
        $isDrh = request()->routeIs('admin.drh.*');
        $view = $isDrh ? 'admin.drh.personnel.edit' : 'admin.personnel.edit';
        $routePrefix = $isDrh ? 'admin.drh.personnel' : 'admin.personnel';
        return view($view, compact('personnel', 'routePrefix'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $personnel = Personnel::findOrFail($id);

        $validatedData = $request->validate([
            // Same validation rules as store
            'prenoms_nom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'tranche_age' => 'required|in:18-25,26-35,36-45,46-55,56-60',
            'nationalite' => 'required|string|max:255',
            'numero_cni' => 'required|string|max:255',
            'sexe' => 'required|in:Masculin,Féminin',
            'situation_matrimoniale' => 'required|in:Célibataire,Marié (e),Divorcé (e),Veuf,Veuve',
            'nombre_enfants' => 'required|integer|min:0|max:10',
            'contact_telephonique' => 'required|string|max:20',
            'email' => 'required|email|unique:personnel,email,' . $personnel->id,
            'groupe_sanguin' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'adresse_complete' => 'required|string',
            'matricule' => 'required|string|unique:personnel,matricule,' . $personnel->id,
            'date_recrutement_csar' => 'required|date',
            'date_prise_service_csar' => 'required|date',
            'statut' => 'required|string',
            'poste_actuel' => 'required|string',
            'direction_service' => 'required|string',
            'localisation_region' => 'nullable|string',
            'dernier_poste_avant_csar' => 'required|string',
            'formations_professionnelles' => 'required|string',
            'diplome_academique' => 'required|string',
            'autres_diplomes_certifications' => 'nullable|string',
            'logiciels_maitrises' => 'required|array',
            'langues_parlees' => 'required|array',
            'autres_aptitudes' => 'nullable|string',
            'aspirations_professionnelles' => 'required|string',
            'interet_nouvelles_responsabilites' => 'required|in:Oui,Non,Neutre',
            'photo_personnelle' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'taille_vetements' => 'required|string',
            'contact_urgence_nom' => 'required|string|max:255',
            'contact_urgence_telephone' => 'required|string|max:20',
            'contact_urgence_lien_parente' => 'required|string|max:255',
            'observations_personnelles' => 'nullable|string',
        ]);

        // Gérer l'upload de la photo
        if ($request->hasFile('photo_personnelle')) {
            // Supprimer l'ancienne photo si elle existe
            if ($personnel->photo_personnelle) {
                Storage::delete('public/personnel/' . $personnel->photo_personnelle);
            }

            $photo = $request->file('photo_personnelle');
            $photoName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $photo->getClientOriginalExtension();
            $photo->storeAs('public/personnel', $photoName);
            $validatedData['photo_personnelle'] = $photoName;
        }

        // Convertir les arrays en JSON
        $validatedData['logiciels_maitrises'] = json_encode($validatedData['logiciels_maitrises']);
        $validatedData['langues_parlees'] = json_encode($validatedData['langues_parlees']);

        // Gérer les aspirations professionnelles (peut être un array de checkboxes)
        if (isset($validatedData['aspirations_professionnelles'])) {
            if (is_array($validatedData['aspirations_professionnelles'])) {
                $validatedData['aspirations_professionnelles'] = implode(', ', $validatedData['aspirations_professionnelles']);
            }
        }

        $personnel->update($validatedData);

        $redirectRoute = request()->routeIs('admin.drh.*') ? 'admin.drh.personnel.index' : 'admin.personnel.index';
        return redirect()->route($redirectRoute)->with('success', 'Personnel mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $personnel = Personnel::findOrFail($id);

        // Supprimer la photo si elle existe
        if ($personnel->photo_personnelle) {
            Storage::delete('public/personnel/' . $personnel->photo_personnelle);
        }

        $personnel->delete();

        $redirectRoute = request()->routeIs('admin.drh.*') ? 'admin.drh.personnel.index' : 'admin.personnel.index';
        return redirect()->route($redirectRoute)->with('success', 'Personnel supprimé avec succès.');
    }

    /**
     * Export personnel list
     */
    public function export()
    {
        // Logic for exporting personnel data
        return redirect()->back()->with('success', 'Export effectué avec succès.');
    }

    /**
     * Toggle personnel status
     */
    public function toggleStatus($id)
    {
        $personnel = Personnel::findOrFail($id);

        if ($personnel->statut_validation === 'En attente') {
            $personnel->update([
                'statut_validation' => 'Valide',
                'valide_par' => Auth::id(),
                'date_validation' => now()
            ]);
            $message = 'Personnel validé avec succès.';
        } else {
            $personnel->update([
                'statut_validation' => 'En attente',
                'valide_par' => null,
                'date_validation' => null
            ]);
            $message = 'Statut mis à jour avec succès.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Réinitialiser le mot de passe d'un personnel
     */
    public function resetPassword($id)
    {
        try {
            $personnel = Personnel::findOrFail($id);

            // Générer un nouveau mot de passe aléatoire
            $newPassword = 'Csar' . rand(1000, 9999);

            // Si le personnel a un compte utilisateur associé, réinitialiser son mot de passe
            $user = \App\Models\User::where('email', $personnel->email)->first();

            if ($user) {
                $user->password = \Hash::make($newPassword);
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Mot de passe réinitialisé avec succès.',
                    'new_password' => $newPassword
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun compte utilisateur associé à ce personnel.'
                ], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la réinitialisation du mot de passe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe.'
            ], 500);
        }
    }
}

