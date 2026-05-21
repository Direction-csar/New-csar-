<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicDocument;

/**
 * Espace documentaire : rapports, données, cartes, FAQ.
 * Conforme au cahier des charges – hub pour chercheurs, journalistes, partenaires.
 */
class RessourcesController extends Controller
{
    public function index()
    {
        $documents = PublicDocument::published()
            ->notExpired()
            ->orderBy('published_at', 'desc')
            ->get();

        return view('public.ressources.index', compact('documents'));
    }
}
