<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GalerieController extends Controller
{
    /**
     * Get the current authenticated user ID based on the guard
     */
    private function getCurrentUserId()
    {
        return auth('ctc')->check() ? auth('ctc')->id() : auth()->id();
    }

    /**
     * Get the correct route prefix based on current request
     */
    private function getRoutePrefix()
    {
        return request()->routeIs('ctc.*') ? 'ctc' : 'admin';
    }

    /**
     * Afficher la galerie d'images
     */
    public function index(Request $request)
    {
        try {
            // Récupérer les images depuis la base de données
            $query = \App\Models\GalleryImage::query();

            // Appliquer les filtres
            if ($request->filled('categorie')) {
                $query->where('category', $request->categorie);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $images = $query->orderBy('created_at', 'desc')->get();

            // Statistiques
            $stats = [
                'total' => $images->count(),
                'actif' => $images->where('status', 'active')->count(),
                'inactif' => $images->where('status', 'inactive')->count(),
                'taille_totale' => $images->sum('file_size') / (1024 * 1024), // Convertir en MB
                'par_categorie' => $images->groupBy('category')->map->count()->toArray()
            ];

            Log::info('Accès à la gestion de galerie Admin', [
                'user_id' => $this->getCurrentUserId(),
                'filters' => $request->only(['categorie', 'search'])
            ]);

            return view('admin.galerie.index', compact('images', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement de la galerie Admin', [
                'user_id' => $this->getCurrentUserId(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Erreur lors du chargement de la galerie.');
        }
    }

    /**
     * Afficher le formulaire d'ajout d'image
     */
    public function create()
    {
        Log::info('Accès au formulaire d\'ajout d\'image Admin', ['user_id' => $this->getCurrentUserId()]);
        return view('admin.galerie.create');
    }

    /**
     * Stocker une nouvelle image
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $imageFile = $request->file('image');
            $imagePath = $imageFile->store('gallery', 'public');

            \App\Models\GalleryImage::create([
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'file_path' => $imagePath,
                'file_name' => $imageFile->getClientOriginalName(),
                'file_size' => $imageFile->getSize(),
                'file_type' => $imageFile->getMimeType(),
                'alt_text' => $request->title,
                'status' => 'active'
            ]);

            Log::info('Image ajoutée à la galerie par Admin', [
                'user_id' => $this->getCurrentUserId(),
                'data' => $request->except(['image'])
            ]);

            return redirect()->route($this->getRoutePrefix() . '.galerie.index')->with('success', 'Image ajoutée avec succès à la galerie.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout d\'image à la galerie', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            return redirect()->back()->with('error', 'Erreur lors de l\'ajout de l\'image.');
        }
    }

    /**
     * Afficher les détails d'une image
     */
    public function show($id)
    {
        try {
            $image = \App\Models\GalleryImage::findOrFail($id);
            
            Log::info('Affichage image galerie Admin', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id
            ]);
            
            return view('admin.galerie.show', compact('image'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'image', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route($this->getRoutePrefix() . '.galerie.index')->with('error', 'Image non trouvée.');
        }
    }

    /**
     * Afficher le formulaire d'édition d'une image
     */
    public function edit($id)
    {
        try {
            $image = \App\Models\GalleryImage::findOrFail($id);
            
            Log::info('Accès au formulaire d\'édition d\'image Admin', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id
            ]);
            
            return view('admin.galerie.edit', compact('image'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'accès au formulaire d\'édition', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route($this->getRoutePrefix() . '.galerie.index')->with('error', 'Image non trouvée.');
        }
    }

    /**
     * Mettre à jour une image
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|string|in:active,inactive,pending',
        ]);

        try {
            $image = \App\Models\GalleryImage::findOrFail($id);
            $image->update([
                'title' => $request->title,
                'category' => $request->category,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            Log::info('Image galerie mise à jour par Admin', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'data' => $request->all()
            ]);

            return redirect()->route($this->getRoutePrefix() . '.galerie.index')->with('success', 'Image mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'image', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour de l\'image.');
        }
    }

    /**
     * Supprimer une image
     */
    public function destroy($id)
    {
        try {
            $image = \App\Models\GalleryImage::findOrFail($id);
            
            // Supprimer le fichier physique
            if ($image->file_path && \Storage::disk('public')->exists($image->file_path)) {
                \Storage::disk('public')->delete($image->file_path);
            }
            
            $image->delete();

            Log::info('Image galerie supprimée par Admin', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id
            ]);

            return redirect()->route($this->getRoutePrefix() . '.galerie.index')->with('success', 'Image supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'image', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors de la suppression de l\'image.');
        }
    }

    /**
     * Basculer le statut d'une image
     */
    public function toggleStatus($id)
    {
        try {
            $image = \App\Models\GalleryImage::findOrFail($id);
            $image->status = $image->status === 'active' ? 'inactive' : 'active';
            $image->save();

            Log::info('Statut image galerie basculé par Admin', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'new_status' => $image->status
            ]);

            return response()->json(['success' => true, 'message' => 'Statut de l\'image mis à jour.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du basculement du statut de l\'image', [
                'user_id' => $this->getCurrentUserId(),
                'image_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut.']);
        }
    }
}



