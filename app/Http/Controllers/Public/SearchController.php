<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\SimReport;
use Illuminate\Http\Request;

/**
 * Outil de recherche pour la plateforme CSAR (public).
 * Recherche dans : actualités, rapports SIM, projets.
 */
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));
        $results = [];

        if (strlen($query) >= 2) {
            $results = $this->performSearch($query);
        }

        return view('public.search.index', compact('query', 'results'));
    }

    private function performSearch(string $query): array
    {
        $results = [];

        // Actualités (News)
        $news = News::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderBy('published_at', 'desc')
            ->take(15)
            ->get();

        foreach ($news as $n) {
            $results[] = [
                'type' => 'actualite',
                'title' => $n->title,
                'excerpt' => \Str::limit(strip_tags($n->excerpt ?? $n->content), 120),
                'url' => route('news.show', ['locale' => app()->getLocale(), 'id' => $n->id]),
                'date' => $n->published_at?->format('d/m/Y'),
            ];
        }

        // Rapports SIM
        $reports = SimReport::public()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('published_at', 'desc')
            ->take(10)
            ->get();

        foreach ($reports as $r) {
            $results[] = [
                'type' => 'rapport',
                'title' => $r->title,
                'excerpt' => \Str::limit(strip_tags($r->description ?? ''), 120),
                'url' => route('sim.show', ['locale' => app()->getLocale(), 'simReport' => $r]),
                'date' => $r->published_at?->format('d/m/Y'),
            ];
        }

        return $results;
    }
}
