<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Statistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContentController extends Controller
{
    /**
     * Afficher la liste du contenu
     */
    public function index(Request $request)
    {
        try {
            $query = Content::with('creator')->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('body', 'like', "%{$search}%");
                });
            }

            $contents = $query->paginate(15)->withQueryString();

            $stats = [
                'total' => Content::count(),
                'published' => Content::where('status', 'published')->count(),
                'draft' => Content::where('status', 'draft')->count(),
                'scheduled' => Content::where('status', 'scheduled')->count(),
            ];

            $aboutStats = Statistics::active()->forSection('about')->ordered()->get();

            return view('admin.content.index', compact('contents', 'stats', 'aboutStats'));
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@index', ['error' => $e->getMessage()]);
            return view('admin.content.index', [
                'contents' => collect(),
                'stats' => ['total' => 0, 'published' => 0, 'draft' => 0, 'scheduled' => 0],
                'aboutStats' => collect()
            ]);
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.content.create');
    }

    /**
     * Enregistrer un nouveau contenu
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:contents,slug',
                'type' => 'required|in:page,article,announcement,banner,footer',
                'category' => 'required|in:general,news,announcements,about,home',
                'status' => 'required|in:published,draft,scheduled',
                'body' => 'required|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'featured_image' => 'nullable|string|max:255',
                'published_at' => 'nullable|date',
                'scheduled_at' => 'nullable|date',
                'order' => 'nullable|integer|min:0',
            ]);

            $validated['created_by'] = auth()->id();
            $validated['updated_by'] = auth()->id();

            if ($validated['status'] === 'published' && empty($validated['published_at'])) {
                $validated['published_at'] = now();
            }
            if ($validated['status'] === 'scheduled' && empty($validated['scheduled_at'])) {
                $validated['scheduled_at'] = now()->addDay();
            }

            Content::create($validated);

            return redirect()->route('admin.content.index')
                ->with('success', 'Contenu créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@store', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un contenu
     */
    public function show($id)
    {
        try {
            $content = Content::with('creator', 'updater')->findOrFail($id);
            return view('admin.content.show', compact('content'));
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@show', ['error' => $e->getMessage()]);
            return redirect()->route('admin.content.index')->with('error', 'Contenu non trouvé');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $content = Content::findOrFail($id);
            return view('admin.content.edit', compact('content'));
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@edit', ['error' => $e->getMessage()]);
            return redirect()->route('admin.content.index')->with('error', 'Contenu non trouvé');
        }
    }

    /**
     * Mettre à jour un contenu
     */
    public function update(Request $request, $id)
    {
        try {
            $content = Content::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:contents,slug,' . $id,
                'type' => 'required|in:page,article,announcement,banner,footer',
                'category' => 'required|in:general,news,announcements,about,home',
                'status' => 'required|in:published,draft,scheduled',
                'body' => 'required|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'featured_image' => 'nullable|string|max:255',
                'published_at' => 'nullable|date',
                'scheduled_at' => 'nullable|date',
                'order' => 'nullable|integer|min:0',
            ]);

            $validated['updated_by'] = auth()->id();

            if ($validated['status'] === 'published' && empty($content->published_at)) {
                $validated['published_at'] = now();
            }

            $content->update($validated);

            return redirect()->route('admin.content.index')
                ->with('success', 'Contenu mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@update', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un contenu
     */
    public function destroy($id)
    {
        try {
            $content = Content::findOrFail($id);
            $content->delete();

            return redirect()->route('admin.content.index')
                ->with('success', 'Contenu supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@destroy', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Publier/Dépublier un contenu
     */
    public function toggleStatus($id)
    {
        try {
            $content = Content::findOrFail($id);
            $newStatus = $content->status === 'published' ? 'draft' : 'published';
            $updates = ['status' => $newStatus, 'updated_by' => auth()->id()];
            if ($newStatus === 'published' && empty($content->published_at)) {
                $updates['published_at'] = now();
            }
            $content->update($updates);

            return redirect()->back()
                ->with('success', 'Statut changé en ' . ($newStatus === 'published' ? 'publié' : 'brouillon') . '.');
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@toggleStatus', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur lors du changement de statut');
        }
    }

    /**
     * Aperçu du site
     */
    public function preview()
    {
        try {
            return redirect('/preview');
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@preview', ['error' => $e->getMessage()]);
            return redirect()->route('admin.content.index')
                ->with('error', 'Erreur lors de l\'ouverture de l\'aperçu');
        }
    }

    /**
     * Obtenir les statistiques du contenu
     */
    private function getContentStats()
    {
        try {
            return [
                'total_pages' => Content::count(),
                'published_content' => Content::where('status', 'published')->count(),
                'draft_content' => Content::where('status', 'draft')->count(),
                'scheduled_content' => Content::where('status', 'scheduled')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur dans getContentStats', ['error' => $e->getMessage()]);
            return $this->getDefaultStats();
        }
    }

    /**
     * Statistiques par défaut
     */
    private function getDefaultStats()
    {
        return [
            'total_pages' => 0,
            'published_content' => 0,
            'draft_content' => 0,
            'scheduled_content' => 0
        ];
    }

    /**
     * Générer un slug à partir du titre
     */
    private function generateSlug($title)
    {
        return Str::slug($title);
    }
    
    /**
     * Afficher la gestion des statistiques
     */
    public function statistics()
    {
        try {
            $statistics = Statistics::active()->forSection('about')->ordered()->get();
            
            return view('admin.content.statistics', compact('statistics'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@statistics', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.content.index')
                ->with('error', 'Erreur lors du chargement des statistiques');
        }
    }
    
    /**
     * Mettre à jour une statistique
     */
    public function updateStatistic(Request $request, $id)
    {
        try {
            $request->validate([
                'value' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:7',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'notes' => 'nullable|string'
            ]);
            
            $statistic = Statistics::findOrFail($id);
            
            $statistic->update([
                'value' => $request->value,
                'description' => $request->description,
                'title' => $request->title,
                'icon' => $request->icon,
                'color' => $request->color,
                'order' => $request->order ?? $statistic->order,
                'is_active' => $request->has('is_active'),
                'notes' => $request->notes
            ]);
            
            $this->createNotification(
                'Statistique mise à jour',
                "La statistique '{$statistic->title}' a été mise à jour avec succès.",
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Statistique mise à jour avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@updateStatistic', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'statistic_id' => $id,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la statistique'
            ], 500);
        }
    }
    
    /**
     * Créer une nouvelle statistique
     */
    public function createStatistic(Request $request)
    {
        try {
            $request->validate([
                'key' => 'required|string|max:255|unique:statistics,key',
                'value' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:7',
                'order' => 'nullable|integer|min:0',
                'section' => 'required|string|max:50',
                'notes' => 'nullable|string'
            ]);
            
            $statistic = Statistics::create([
                'key' => $request->key,
                'value' => $request->value,
                'description' => $request->description,
                'title' => $request->title,
                'icon' => $request->icon,
                'color' => $request->color ?? '#22c55e',
                'order' => $request->order ?? 0,
                'section' => $request->section,
                'is_active' => true,
                'notes' => $request->notes
            ]);
            
            $this->createNotification(
                'Nouvelle statistique créée',
                "La statistique '{$statistic->title}' a été créée avec succès.",
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Statistique créée avec succès',
                'statistic' => $statistic
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@createStatistic', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la statistique'
            ], 500);
        }
    }
    
    /**
     * Supprimer une statistique
     */
    public function deleteStatistic($id)
    {
        try {
            $statistic = Statistics::findOrFail($id);
            $title = $statistic->title;
            
            $statistic->delete();
            
            $this->createNotification(
                'Statistique supprimée',
                "La statistique '{$title}' a été supprimée avec succès.",
                'warning'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Statistique supprimée avec succès'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans ContentController@deleteStatistic', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'statistic_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la statistique'
            ], 500);
        }
    }
    
    /**
     * Créer une notification
     */
    private function createNotification($title, $message, $type = 'info')
    {
        try {
            if (class_exists('App\Models\Notification')) {
                \App\Models\Notification::create([
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'user_id' => auth()->id(),
                    'read' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de notification', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
