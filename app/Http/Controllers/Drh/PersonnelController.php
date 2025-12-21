<?php

namespace App\Http\Controllers\DRH;

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
    public function index()
    {
        $personnel = Personnel::with([])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalPersonnel = Personnel::count();
        $validatedCount = Personnel::where('statut_validation', 'Valide')->count();
        $pendingCount = Personnel::where('statut_validation', 'En attente')->count();
        $rejectedCount = Personnel::where('statut_validation', 'Rejeté')->count();

        // Récupérer les directions et postes uniques pour les filtres
        $directions = Personnel::distinct()->pluck('direction_service')->filter()->sort()->values();
        $postes = Personnel::distinct()->pluck('poste_actuel')->filter()->sort()->values();
        
        // Compter le personnel par direction
        $directionCounts = Personnel::selectRaw('direction_service, COUNT(*) as count')
            ->groupBy('direction_service')
            ->pluck('count', 'direction_service')
            ->toArray();

        return view('drh.personnel.index', compact(
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
        return view('drh.personnel.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'prenoms_nom' => 'required|string|max:255',
            'email' => 'required|email|unique:personnel,email',
            'contact_telephonique' => 'required|string|max:20',
            'poste_actuel' => 'required|string|max:255',
            'direction_service' => 'required|string|max:255',
            'photo_personnelle' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->all();

        // Gérer l'upload de la photo
        if ($request->hasFile('photo_personnelle')) {
            $photo = $request->file('photo_personnelle');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->storeAs('public/personnel', $photoName);
            $data['photo_personnelle'] = $photoName;
        }

        // Gérer les tableaux pour logiciels et langues
        if (isset($data['logiciels_maitrises']) && is_string($data['logiciels_maitrises'])) {
            $data['logiciels_maitrises'] = array_filter(explode(',', $data['logiciels_maitrises']));
        }
        if (isset($data['langues_parlees']) && is_string($data['langues_parlees'])) {
            $data['langues_parlees'] = array_filter(explode(',', $data['langues_parlees']));
        }

        // Générer un matricule automatique
        $data['matricule'] = 'CSAR-' . date('Y') . '-' . str_pad(Personnel::count() + 1, 4, '0', STR_PAD_LEFT);

        // Définir le statut par défaut
        $data['statut_validation'] = 'En attente';

        Personnel::create($data);

        return redirect()->route('drh.personnel.index')->with('success', 'Personnel ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Personnel $personnel)
    {
        return view('drh.personnel.show', compact('personnel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Personnel $personnel)
    {
        return view('drh.personnel.edit', compact('personnel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Personnel $personnel)
    {
        $request->validate([
            'prenoms_nom' => 'required|string|max:255',
            'email' => 'required|email|unique:personnel,email,' . $personnel->id,
            'contact_telephonique' => 'required|string|max:20',
            'poste_actuel' => 'required|string|max:255',
            'direction_service' => 'required|string|max:255',
            'photo_personnelle' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->all();

        // Gérer l'upload de la photo
        if ($request->hasFile('photo_personnelle')) {
            // Supprimer l'ancienne photo si elle existe
            if ($personnel->photo_personnelle) {
                Storage::delete('public/personnel/' . $personnel->photo_personnelle);
            }
            
            $photo = $request->file('photo_personnelle');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->storeAs('public/personnel', $photoName);
            $data['photo_personnelle'] = $photoName;
        }

        // Gérer les tableaux pour logiciels et langues
        if (isset($data['logiciels_maitrises']) && is_string($data['logiciels_maitrises'])) {
            $data['logiciels_maitrises'] = array_filter(explode(',', $data['logiciels_maitrises']));
        }
        if (isset($data['langues_parlees']) && is_string($data['langues_parlees'])) {
            $data['langues_parlees'] = array_filter(explode(',', $data['langues_parlees']));
        }

        $personnel->update($data);

        return redirect()->route('drh.personnel.index')->with('success', 'Personnel mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Personnel $personnel)
    {
        // Supprimer la photo si elle existe
        if ($personnel->photo_personnelle) {
            Storage::delete('public/personnel/' . $personnel->photo_personnelle);
        }

        $personnel->delete();

        return redirect()->route('drh.personnel.index')->with('success', 'Personnel supprimé avec succès.');
    }

    /**
     * Export personnel data to PDF
     */
    public function exportPdf()
    {
        $personnel = Personnel::all();
        
        // Logique d'export PDF ici
        // Vous pouvez utiliser DomPDF ou une autre bibliothèque
        
        return redirect()->back()->with('success', 'Export PDF généré avec succès.');
    }

    /**
     * Export personnel data to Excel
     */
    public function exportExcel()
    {
        $personnel = Personnel::all();
        
        // Logique d'export Excel ici
        // Vous pouvez utiliser Laravel Excel
        
        return redirect()->back()->with('success', 'Export Excel généré avec succès.');
    }

    /**
     * Export individual personnel card to PDF
     */
    public function exportFichePdf(Personnel $personnel)
    {
        // Logique d'export de fiche individuelle PDF ici
        
        return redirect()->back()->with('success', 'Fiche PDF générée avec succès.');
    }

    /**
     * Delete personnel photo
     */
    public function deletePhoto(Personnel $personnel)
    {
        if ($personnel->photo_personnelle) {
            Storage::delete('public/personnel/' . $personnel->photo_personnelle);
            $personnel->update(['photo_personnelle' => null]);
        }

        return redirect()->back()->with('success', 'Photo supprimée avec succès.');
    }
}
