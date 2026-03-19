<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

/**
 * Espace documentaire : rapports, données, cartes, FAQ.
 * Conforme au cahier des charges – hub pour chercheurs, journalistes, partenaires.
 */
class RessourcesController extends Controller
{
    public function index()
    {
        return view('public.ressources.index');
    }
}
