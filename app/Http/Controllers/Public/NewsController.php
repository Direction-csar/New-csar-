<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::where('is_published', true)
                     ->where('is_public', true);
        
        // Filtre pour les publications (actualités avec documents)
        if ($request->has('filter') && $request->filter === 'publications') {
            $query = $query->whereNotNull('document_file')
                          ->where('document_file', '!=', '');
        }
        
        $news = $query->orderBy('published_at', 'desc')->paginate(10);
        
        return view('public.news', compact('news'));
    }
    
    public function show($id)
    {
        $news = News::where('is_published', true)->findOrFail($id);
        return view('public.news.show', compact('news'));
    }
}
