<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.distribution.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.distribution.campaigns.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'initial_stock_kg' => 'required|numeric|min:0',
            'status' => 'required|in:active,closed,archived',
        ]);

        Campaign::create($validated);

        return redirect()->route('admin.distribution.campaigns.index')
            ->with('success', 'Campagne créée avec succès.');
    }

    public function edit(Campaign $campaign)
    {
        return view('admin.distribution.campaigns.form', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'initial_stock_kg' => 'required|numeric|min:0',
            'status' => 'required|in:active,closed,archived',
        ]);

        $campaign->update($validated);

        return redirect()->route('admin.distribution.campaigns.index')
            ->with('success', 'Campagne mise à jour.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('admin.distribution.campaigns.index')
            ->with('success', 'Campagne supprimée.');
    }
}
