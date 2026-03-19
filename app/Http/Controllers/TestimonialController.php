<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    /**
     * Afficher le formulaire de soumission de témoignage
     */
    public function create()
    {
        return view('public.testimonials.create');
    }

    /**
     * Enregistrer un nouveau témoignage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'organization' => 'nullable|string|max:255',
            'message' => 'required|string|min:10',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Testimonial::create([
            'name' => $request->name,
            'email' => $request->email,
            'organization' => $request->organization,
            'message' => $request->message,
            'rating' => $request->rating,
            'status' => 'pending', // En attente de validation par défaut
        ]);

        return back()->with('success', 'Merci pour votre témoignage ! Il sera publié après validation par notre équipe.');
    }

    /**
     * Afficher tous les témoignages approuvés (pour la page publique)
     */
    public function index()
    {
        $testimonials = Testimonial::approved()
            ->latest()
            ->paginate(12);

        return view('public.testimonials.index', compact('testimonials'));
    }

    /**
     * Récupérer les témoignages pour la page d'accueil
     */
    public function getFeatured()
    {
        return Testimonial::approved()
            ->featured()
            ->latest()
            ->take(6)
            ->get();
    }
}
