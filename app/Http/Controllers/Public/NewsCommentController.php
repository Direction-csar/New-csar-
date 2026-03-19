<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Http\Request;

class NewsCommentController extends Controller
{
    /**
     * Ajouter un commentaire à une actualité
     */
    public function store(Request $request, $locale, $newsId)
    {
        $request->validate([
            'author_name' => 'required|string|max:100',
            'author_email' => 'required|email|max:255',
            'content' => 'required|string|max:2000',
        ]);

        $news = News::where('is_published', true)->findOrFail($newsId);

        NewsComment::create([
            'news_id' => $news->id,
            'author_name' => $request->author_name,
            'author_email' => $request->author_email,
            'content' => $request->content,
            'is_approved' => true,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Votre commentaire a été ajouté avec succès.');
    }
}
