<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsCommentController extends Controller
{
    /**
     * Afficher les commentaires d'une actualité
     */
    public function index($id)
    {
        try {
            $news = News::findOrFail($id);
            $comments = $news->allComments()->paginate(20);
            return view('admin.news.comments', compact('news', 'comments'));
        } catch (\Exception $e) {
            Log::error('Erreur chargement commentaires', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors du chargement des commentaires.');
        }
    }

    /**
     * Supprimer un commentaire (modération admin)
     */
    public function destroy($id)
    {
        try {
            $comment = NewsComment::findOrFail($id);
            $newsId = $comment->news_id;
            $authorName = $comment->author_name;
            $comment->delete();

            Log::info('Commentaire supprimé par admin', [
                'comment_id' => $id,
                'author' => $authorName,
                'news_id' => $newsId,
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Commentaire de {$authorName} supprimé avec succès."
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur suppression commentaire', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du commentaire.'
            ], 500);
        }
    }
}
