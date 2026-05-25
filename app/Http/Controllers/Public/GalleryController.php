<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        // Récupérer toutes les images actives
        $images = GalleryImage::where('status', 'active')
                             ->orderBy('created_at', 'desc')
                             ->get();

        return view('public.gallery.index', compact('images'));
    }

    public function missions()
    {
        // Récupérer toutes les images actives pour la page missions
        $images = GalleryImage::where('status', 'active')
                             ->orderBy('created_at', 'desc')
                             ->get();

        return view('public.missions', compact('images'));
    }
}
