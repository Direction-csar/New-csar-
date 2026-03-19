<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActualitesController extends Controller
{
    /**
     * Afficher la page publique des actualités
     */
    public function index(Request $request)
    {
        try {
            // Récupérer les actualités depuis la base de données (créées par l'Admin)
            $actualites = \App\Models\News::published()
                ->orderBy('published_at', 'desc')
                ->get()
                ->map(function($news) {
                    $img = $news->cover_image ?? $news->featured_image ?? null;
                    return (object)[
                        'id' => $news->id,
                        'titre' => $news->title,
                        'categorie' => $news->category ?? 'general',
                        'statut' => $news->status,
                        'featured' => $news->is_featured,
                        'contenu' => $news->content,
                        'extrait' => \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($news->excerpt ?? $news->content)), 150),
                        'image' => $img ? asset('storage/' . $img) : null,
                        'auteur' => $news->author->name ?? 'CSAR Communication',
                        'vues' => $news->views_count,
                        'created_at' => $news->created_at,
                        'published_at' => $news->published_at,
                        'youtube_url' => $news->youtube_url ?? null,
                        'document_file' => $news->document_file ?? null,
                        'document_cover_image' => $news->document_cover_image ?? null,
                        'document_title' => $news->document_title ?? null,
                        'downloads_count' => $news->downloads_count ?? 0,
                        'cover_choice' => $news->cover_choice ?? 'auto'
                    ];
                });

            // Si aucune actualité n'est trouvée, retourner une collection vide
            if ($actualites->isEmpty()) {
                $actualites = collect([]);
            }

            // Appliquer les filtres
            if ($request->filled('categorie')) {
                $actualites = $actualites->where('categorie', $request->categorie);
            }
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $actualites = $actualites->filter(function($actualite) use ($search) {
                    return str_contains(strtolower($actualite->titre), $search) ||
                           str_contains(strtolower($actualite->contenu), $search);
                });
            }

            // Actualités à la une
            $featured = $actualites->where('featured', true)->take(3);
            $regular = $actualites->where('featured', false);

            // Statistiques
            $stats = [
                'total' => $actualites->count(),
                'featured' => $featured->count(),
                'this_week' => $actualites->where('created_at', '>=', Carbon::now()->subWeek())->count(),
                'categories' => $actualites->groupBy('categorie')->map->count()
            ];

            Log::info('Accès à la page actualités publique', [
                'ip' => request()->ip(),
                'filters' => $request->only(['categorie', 'search'])
            ]);

            return view('public.actualites.index', compact('actualites', 'featured', 'regular', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des actualités publiques', [
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->back()->with('error', 'Erreur lors du chargement des actualités.');
        }
    }

    /**
     * Afficher une actualité spécifique
     */
    public function show($id)
    {
        try {
            // Récupérer l'actualité depuis la base de données
            $news = \App\Models\News::published()->findOrFail($id);
            
            // Incrémenter le compteur de vues
            $news->incrementViews();
            
            $actualite = (object)[
                'id' => $news->id,
                'titre' => $news->title,
                'categorie' => $news->category ?? 'general',
                'statut' => $news->status,
                'featured' => $news->is_featured,
                'contenu' => $news->content,
                'image' => $news->featured_image ? asset('storage/' . $news->featured_image) : null,
                'auteur' => $news->author->name ?? 'CSAR Communication',
                'vues' => $news->views_count,
                'created_at' => $news->created_at,
                'published_at' => $news->published_at,
                'youtube_url' => $news->youtube_url ?? null,
                'document_file' => $news->document_file ?? null,
                'document_cover_image' => $news->document_cover_image ?? null,
                'document_title' => $news->document_title ?? null,
                'downloads_count' => $news->downloads_count ?? 0,
                'cover_choice' => $news->cover_choice ?? 'auto'
            ];

            // Commentaires approuvés
            $comments = $news->comments()->get();

            // Actualités similaires (même catégorie)
            $related = \App\Models\News::published()
                ->where('id', '!=', $id)
                ->where('category', $news->category)
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get()
                ->map(function($relatedNews) {
                    return (object)[
                        'id' => $relatedNews->id,
                        'titre' => $relatedNews->title,
                        'categorie' => $relatedNews->category,
                        'image' => $relatedNews->featured_image_url,
                        'published_at' => $relatedNews->published_at,
                        'created_at' => $relatedNews->created_at
                    ];
                });

            Log::info('Affichage actualité publique', [
                'actualite_id' => $id,
                'ip' => request()->ip()
            ]);

            return view('public.actualites.show', compact('actualite', 'related', 'comments'));

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'actualité publique', [
                'actualite_id' => $id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->route('public.actualites')->with('error', 'Actualité non trouvée.');
        }
    }

    /**
     * Télécharger un document associé à une actualité
     */
    public function downloadDocument($id)
    {
        try {
            $news = \App\Models\News::published()->findOrFail($id);
            
            if (!$news->document_file || !\Storage::disk('public')->exists($news->document_file)) {
                return redirect()->back()->with('error', 'Document non trouvé.');
            }

            // Incrémenter le compteur de téléchargements
            $news->increment('downloads_count');

            Log::info('Document téléchargé depuis la partie publique', [
                'actualite_id' => $id,
                'document' => $news->document_file,
                'ip' => request()->ip()
            ]);

            return \Storage::disk('public')->download($news->document_file, $news->document_name);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du document public', [
                'actualite_id' => $id,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);
            return redirect()->back()->with('error', 'Erreur lors du téléchargement du document.');
        }
    }
}
