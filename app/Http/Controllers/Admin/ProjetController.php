<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjetController extends Controller
{
    public function index(Request $request)
    {
        $query = Projet::orderBy('position')->orderBy('created_at', 'desc');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $projets = $query->paginate(20);
        $stats = [
            'total'    => Projet::count(),
            'actif'    => Projet::where('statut', 'actif')->count(),
            'termine'  => Projet::where('statut', 'termine')->count(),
            'suspendu' => Projet::where('statut', 'suspendu')->count(),
        ];

        return view('admin.projets.index', compact('projets', 'stats'));
    }

    public function create()
    {
        return view('admin.projets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'        => 'required|string|max:255',
            'description'  => 'required|string',
            'statut'       => 'required|in:actif,termine,suspendu',
            'icon'         => 'nullable|string|max:50',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'date_debut'   => 'nullable|date',
            'date_fin'     => 'nullable|date|after_or_equal:date_debut',
            'region'       => 'nullable|string|max:100',
            'budget'       => 'nullable|string|max:100',
            'position'     => 'nullable|integer|min:0',
        ]);

        try {
            $data = $request->except(['image', '_token']);
            $data['lien_sim']     = $request->boolean('lien_sim');
            $data['is_published'] = $request->boolean('is_published');
            $data['created_by']   = auth()->id();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('projets', 'public');
            }

            Projet::create($data);

            Log::info('Projet créé', ['user_id' => auth()->id()]);
            return redirect()->route('admin.projets.index')->with('success', 'Projet créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur création projet', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $projet = Projet::findOrFail($id);
        return view('admin.projets.edit', compact('projet'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'required|string',
            'statut'      => 'required|in:actif,termine,suspendu',
            'icon'        => 'nullable|string|max:50',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date|after_or_equal:date_debut',
            'region'      => 'nullable|string|max:100',
            'budget'      => 'nullable|string|max:100',
            'position'    => 'nullable|integer|min:0',
        ]);

        try {
            $projet = Projet::findOrFail($id);
            $data = $request->except(['image', '_token', '_method']);
            $data['lien_sim']     = $request->boolean('lien_sim');
            $data['is_published'] = $request->boolean('is_published');

            if ($request->hasFile('image')) {
                if ($projet->image) {
                    Storage::disk('public')->delete($projet->image);
                }
                $data['image'] = $request->file('image')->store('projets', 'public');
            }

            $projet->update($data);

            Log::info('Projet mis à jour', ['user_id' => auth()->id(), 'projet_id' => $id]);
            return redirect()->route('admin.projets.index')->with('success', 'Projet mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour projet', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $projet = Projet::findOrFail($id);
            if ($projet->image) {
                Storage::disk('public')->delete($projet->image);
            }
            $projet->delete();
            Log::info('Projet supprimé', ['user_id' => auth()->id(), 'projet_id' => $id]);
            return redirect()->route('admin.projets.index')->with('success', 'Projet supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }
}
