<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\DoublonDetecte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoublonController extends Controller
{
    public function index()
    {
        $doublons = DoublonDetecte::with('entity1', 'entity2', 'planning1', 'planning2')
            ->orderBy('status')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.distribution.doublons.index', compact('doublons'));
    }

    public function update(Request $request, DoublonDetecte $doublon)
    {
        $validated = $request->validate([
            'status' => 'required|in:a_verifier,confirme,faux_positif',
            'justification' => 'nullable|string',
        ]);

        $validated['resolved_by'] = Auth::id();
        $validated['resolved_at'] = now();
        $doublon->update($validated);

        return back()->with('success', 'Doublon mis à jour.');
    }
}
