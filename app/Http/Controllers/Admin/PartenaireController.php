<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicalPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartenaireController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalPartner::orderBy('position')->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $partenaires = $query->paginate(20);
        $stats = [
            'total'    => TechnicalPartner::count(),
            'active'   => TechnicalPartner::active()->count(),
            'ong'      => TechnicalPartner::ong()->count(),
            'featured' => TechnicalPartner::featured()->count(),
        ];

        return view('admin.partenaires.index', compact('partenaires', 'stats'));
    }

    public function create()
    {
        return view('admin.partenaires.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'organization'           => 'nullable|string|max:255',
            'type'                   => 'required|in:ong,agency,institution,private,government',
            'role'                   => 'nullable|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'phone'                  => 'nullable|string|max:50',
            'website'                => 'nullable|url|max:255',
            'description'            => 'nullable|string',
            'partnership_type'       => 'nullable|string|max:100',
            'partnership_start_date' => 'nullable|date',
            'partnership_end_date'   => 'nullable|date',
            'status'                 => 'required|in:active,inactive,pending',
            'position'               => 'nullable|integer|min:0',
            'logo'                   => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        try {
            $data = $request->except(['logo', '_token']);
            $data['slug']        = Str::slug($request->name) . '-' . Str::random(4);
            $data['is_featured'] = $request->boolean('is_featured');

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('partenaires/logos', 'public');
            }

            TechnicalPartner::create($data);

            Log::info('Partenaire créé', ['user_id' => auth()->id()]);
            return redirect()->route('admin.partenaires.index')->with('success', 'Partenaire créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur création partenaire', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $partenaire = TechnicalPartner::findOrFail($id);
        return view('admin.partenaires.edit', compact('partenaire'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'organization'           => 'nullable|string|max:255',
            'type'                   => 'required|in:ong,agency,institution,private,government',
            'role'                   => 'nullable|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'phone'                  => 'nullable|string|max:50',
            'website'                => 'nullable|url|max:255',
            'description'            => 'nullable|string',
            'partnership_type'       => 'nullable|string|max:100',
            'partnership_start_date' => 'nullable|date',
            'partnership_end_date'   => 'nullable|date',
            'status'                 => 'required|in:active,inactive,pending',
            'position'               => 'nullable|integer|min:0',
            'logo'                   => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        try {
            $partenaire = TechnicalPartner::findOrFail($id);
            $data = $request->except(['logo', '_token', '_method']);
            $data['is_featured'] = $request->boolean('is_featured');

            if ($request->hasFile('logo')) {
                if ($partenaire->logo) {
                    Storage::disk('public')->delete($partenaire->logo);
                }
                $data['logo'] = $request->file('logo')->store('partenaires/logos', 'public');
            }

            $partenaire->update($data);

            Log::info('Partenaire mis à jour', ['user_id' => auth()->id(), 'partenaire_id' => $id]);
            return redirect()->route('admin.partenaires.index')->with('success', 'Partenaire mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $partenaire = TechnicalPartner::findOrFail($id);
            if ($partenaire->logo) {
                Storage::disk('public')->delete($partenaire->logo);
            }
            $partenaire->delete();
            Log::info('Partenaire supprimé', ['user_id' => auth()->id(), 'partenaire_id' => $id]);
            return redirect()->route('admin.partenaires.index')->with('success', 'Partenaire supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }
}
