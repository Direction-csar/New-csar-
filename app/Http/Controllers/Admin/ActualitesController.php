<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActualitesController extends Controller
{
    /**
     * Afficher la liste des actualités
     */
    public function index(Request $request)
    {
        try {
            $actualites = \App\Models\News::orderBy('created_at', 'desc')->get();

            $prefix = $request->routeIs('ctc.*') ? 'ctc' : 'admin';
            Log::info('Accès à la gestion des actualités ' . strtoupper($prefix), ['user_id' => auth()->id()]);

            return view($prefix . '.actualites.index', compact('actualites'));
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des actualités', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du chargement des actualités.');
        }
    }

    /**
     * Afficher le formulaire de création d'une nouvelle actualité
     */
    public function create(Request $request)
    {
        $prefix = $request->routeIs('ctc.*') ? 'ctc' : 'admin';
        Log::info('Accès au formulaire de création d\'actualité ' . strtoupper($prefix), ['user_id' => auth()->id()]);
        return view($prefix . '.actualites.create');
    }

    /**
     * Stocker une nouvelle actualité
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:actualite,mission,communication,formation,evenement,publication',
            'content' => 'required|string',
            'status' => 'required|string|in:draft,published,pending',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'youtube_url' => 'nullable|url',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:51200', // 50MB
            'document_cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'document_title' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'tags' => 'nullable|string',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        try {
            // Générer un slug unique
            $slug = \Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\News::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $data = [
                'title' => $request->title,
                'slug' => $slug,
                'excerpt' => $request->excerpt,
                'content' => $request->content,
                'category' => $request->category,
                'status' => $request->status,
                'is_published' => $request->status === 'published',
                'is_public' => $request->has('is_public'),
                'is_featured' => $request->has('is_featured'),
                'created_by' => auth()->id(),
                'views_count' => 0,
                'downloads_count' => 0,
                'youtube_url' => $request->youtube_url,
                'document_title' => $request->document_title,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'tags' => $request->tags ? explode(',', $request->tags) : null,
            ];

            // Gérer les dates de publication
            if ($request->status === 'published') {
                $data['published_at'] = $request->scheduled_at ?? now();
            } elseif ($request->scheduled_at) {
                $data['scheduled_at'] = $request->scheduled_at;
            }

            // Gérer l'upload de l'image mise en avant
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')->store('news/featured', 'public');
            }

            // Gérer l'upload du document
            if ($request->hasFile('document_file')) {
                $data['document_file'] = $request->file('document_file')->store('news/documents', 'public');
                // Si pas de titre de document fourni, utiliser le nom du fichier
                if (!$data['document_title']) {
                    $data['document_title'] = pathinfo($request->file('document_file')->getClientOriginalName(), PATHINFO_FILENAME);
                }
            }

            // Gérer l'upload de l'image de couverture du document
            if ($request->hasFile('document_cover_image')) {
                $data['document_cover_image'] = $request->file('document_cover_image')->store('news/document-covers', 'public');
            }

            $news = \App\Models\News::create($data);

            // Déclencher l'événement si l'actualité est publiée
            if ($request->status === 'published') {
                event(new \App\Events\CommunicationPublished($news));
            }

            Log::info('Actualité créée par Admin', [
                'user_id' => auth()->id(),
                'news_id' => $news->id,
                'title' => $request->title,
                'status' => $request->status
            ]);

            return redirect()->route(route_prefix() . '.actualites.index')->with('success', 'Actualité créée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'actualité', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Erreur lors de la création de l\'actualité: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une actualité
     */
    public function show($id, Request $request)
    {
        try {
            $actualite = \App\Models\News::findOrFail($id);
            $prefix = $request->routeIs('ctc.*') ? 'ctc' : 'admin';
            
            Log::info('Affichage actualité ' . strtoupper($prefix), ['user_id' => auth()->id(), 'actualite_id' => $id]);
            return view($prefix . '.actualites.show', compact('actualite'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'actualité', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Actualité non trouvée.');
        }
    }

    /**
     * Afficher le formulaire d'édition d'une actualité
     */
    public function edit($id, Request $request)
    {
        try {
            $actualite = \App\Models\News::findOrFail($id);
            $prefix = $request->routeIs('ctc.*') ? 'ctc' : 'admin';
            
            Log::info('Accès au formulaire d\'édition d\'actualité ' . strtoupper($prefix), ['user_id' => auth()->id(), 'actualite_id' => $id]);
            return view($prefix . '.actualites.edit', compact('actualite'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'édition de l\'actualité', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Actualité non trouvée.');
        }
    }

    /**
     * Mettre à jour une actualité
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'status' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'youtube_url' => 'nullable|url',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ]);

        try {
            $actualite = \App\Models\News::findOrFail($id);
            
            $data = [
                'title' => $request->title,
                'category' => $request->category,
                'content' => $request->content,
                'status' => $request->status,
                'is_featured' => $request->has('featured'),
                'published_at' => $request->status === 'published' ? now() : null,
                'youtube_url' => $request->youtube_url,
            ];

            // Gérer l'upload de l'image mise en avant
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')->store('news/featured', 'public');
            }

            // Gérer l'upload de l'image de couverture
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('news/covers', 'public');
            }

            // Gérer l'upload du document
            if ($request->hasFile('document_file')) {
                $data['document_file'] = $request->file('document_file')->store('news/documents', 'public');
            }

            // Gérer l'upload de l'image de couverture du document
            if ($request->hasFile('document_cover_image')) {
                $data['document_cover_image'] = $request->file('document_cover_image')->store('news/document-covers', 'public');
            }

            $wasPublished = $actualite->is_published;
            $actualite->update($data);

            if (!$wasPublished && $request->status === 'published') {
                event(new \App\Events\CommunicationPublished($actualite->fresh()));
            }

            Log::info('Actualité mise à jour par Admin', ['user_id' => auth()->id(), 'actualite_id' => $id, 'data' => $request->all()]);

            return redirect()->route(route_prefix() . '.actualites.index')->with('success', 'Actualité mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'actualité', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour de l\'actualité.');
        }
    }

    /**
     * Supprimer une actualité
     */
    public function destroy($id)
    {
        try {
            $actualite = \App\Models\News::findOrFail($id);
            
            // Supprimer les fichiers associés
            if ($actualite->featured_image && \Storage::disk('public')->exists($actualite->featured_image)) {
                \Storage::disk('public')->delete($actualite->featured_image);
            }
            if ($actualite->document_file && \Storage::disk('public')->exists($actualite->document_file)) {
                \Storage::disk('public')->delete($actualite->document_file);
            }
            
            $actualite->delete();

            Log::info('Actualité supprimée par Admin', ['user_id' => auth()->id(), 'actualite_id' => $id]);

            return redirect()->route(route_prefix() . '.actualites.index')->with('success', 'Actualité supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'actualité', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de la suppression de l\'actualité.');
        }
    }

    /**
     * Télécharger un document associé à une actualité
     */
    public function downloadDocument($id)
    {
        try {
            $actualite = \App\Models\News::findOrFail($id);
            
            if (!$actualite->document_file || !\Storage::disk('public')->exists($actualite->document_file)) {
                return redirect()->back()->with('error', 'Document non trouvé.');
            }

            // Incrémenter le compteur de téléchargements
            $actualite->increment('downloads_count');

            Log::info('Document téléchargé', [
                'user_id' => auth()->id(),
                'actualite_id' => $id,
                'document' => $actualite->document_file
            ]);

            return \Storage::disk('public')->download($actualite->document_file, $actualite->document_name);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du document', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du téléchargement du document.');
        }
    }

    /**
     * Prévisualiser une actualité
     */
    public function preview($id)
    {
        try {
            $actualite = \App\Models\News::findOrFail($id);
            
            Log::info('Prévisualisation actualité Admin', ['user_id' => auth()->id(), 'actualite_id' => $id]);
            
            return view('admin.actualites.preview', compact('actualite'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la prévisualisation de l\'actualité', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Actualité non trouvée.');
        }
    }

    /**
     * Upload d'image pour l'éditeur TinyMCE
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $path = $request->file('file')->store('news/content-images', 'public');
            return response()->json([
                'location' => \Illuminate\Support\Facades\Storage::url($path)
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur upload image TinyMCE', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de l\'upload'], 500);
        }
    }
}
