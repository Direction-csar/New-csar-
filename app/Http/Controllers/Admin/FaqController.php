<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::orderBy('position')->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('locale')) {
            $query->where('locale', $request->locale);
        }

        $faqs = $query->paginate(20);
        $stats = [
            'total'     => Faq::count(),
            'published' => Faq::published()->count(),
            'usager'    => Faq::byCategory('usager')->count(),
            'bailleur'  => Faq::byCategory('bailleur')->count(),
        ];

        return view('admin.faqs.index', compact('faqs', 'stats'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'category'     => 'required|in:usager,bailleur,general',
            'locale'       => 'required|in:fr,en,ar',
            'position'     => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        try {
            Faq::create([
                'question'     => $request->question,
                'answer'       => $request->answer,
                'category'     => $request->category,
                'locale'       => $request->locale,
                'position'     => $request->position ?? 0,
                'is_published' => $request->boolean('is_published'),
            ]);

            Log::info('FAQ créée', ['user_id' => auth()->id()]);
            return redirect()->route('admin.faqs.index')->with('success', 'Question FAQ créée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur création FAQ', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création.');
        }
    }

    public function edit($id)
    {
        $faq = Faq::findOrFail($id);
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'category'     => 'required|in:usager,bailleur,general',
            'locale'       => 'required|in:fr,en,ar',
            'position'     => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        try {
            $faq = Faq::findOrFail($id);
            $faq->update([
                'question'     => $request->question,
                'answer'       => $request->answer,
                'category'     => $request->category,
                'locale'       => $request->locale,
                'position'     => $request->position ?? 0,
                'is_published' => $request->boolean('is_published'),
            ]);

            Log::info('FAQ mise à jour', ['user_id' => auth()->id(), 'faq_id' => $id]);
            return redirect()->route('admin.faqs.index')->with('success', 'FAQ mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour FAQ', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour.');
        }
    }

    public function destroy($id)
    {
        try {
            Faq::findOrFail($id)->delete();
            Log::info('FAQ supprimée', ['user_id' => auth()->id(), 'faq_id' => $id]);
            return redirect()->route('admin.faqs.index')->with('success', 'FAQ supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    public function togglePublished($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->update(['is_published' => !$faq->is_published]);
        return redirect()->back()->with('success', 'Statut mis à jour.');
    }
}
