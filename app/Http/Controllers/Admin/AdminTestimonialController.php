<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class AdminTestimonialController extends Controller
{
    /**
     * Afficher la liste de tous les témoignages
     */
    public function index()
    {
        $testimonials = Testimonial::latest()->paginate(20);
        $pendingCount = Testimonial::pending()->count();
        
        return view('admin.testimonials.index', compact('testimonials', 'pendingCount'));
    }

    /**
     * Approuver un témoignage
     */
    public function approve($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['status' => 'approved']);

        return back()->with('success', 'Témoignage approuvé avec succès !');
    }

    /**
     * Rejeter un témoignage
     */
    public function reject($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['status' => 'rejected']);

        return back()->with('success', 'Témoignage rejeté.');
    }

    /**
     * Mettre en vedette un témoignage
     */
    public function toggleFeatured($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_featured' => !$testimonial->is_featured]);

        $message = $testimonial->is_featured 
            ? 'Témoignage mis en vedette !' 
            : 'Témoignage retiré de la vedette.';

        return back()->with('success', $message);
    }

    /**
     * Supprimer un témoignage
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return back()->with('success', 'Témoignage supprimé avec succès !');
    }

    /**
     * Afficher les détails d'un témoignage
     */
    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return view('admin.testimonials.show', compact('testimonial'));
    }
}
