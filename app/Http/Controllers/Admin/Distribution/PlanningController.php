<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Planning;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index()
    {
        $plannings = Planning::with('campaign', 'warehouse')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.distribution.plannings.index', compact('plannings'));
    }

    public function create()
    {
        $campaigns = Campaign::where('status', '!=', 'archived')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        return view('admin.distribution.plannings.form', compact('campaigns', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'planned_quota_kg' => 'required|numeric|min:0',
            'alert_threshold_kg' => 'required|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'required|in:active,full,closed',
        ]);

        $validated['executed_quota_kg'] = 0;
        Planning::create($validated);

        return redirect()->route('admin.distribution.plannings.index')
            ->with('success', 'Planning créé avec succès.');
    }

    public function edit(Planning $planning)
    {
        $campaigns = Campaign::where('status', '!=', 'archived')->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        return view('admin.distribution.plannings.form', compact('planning', 'campaigns', 'warehouses'));
    }

    public function update(Request $request, Planning $planning)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'planned_quota_kg' => 'required|numeric|min:0',
            'alert_threshold_kg' => 'required|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'required|in:active,full,closed',
        ]);

        $planning->update($validated);

        return redirect()->route('admin.distribution.plannings.index')
            ->with('success', 'Planning mis à jour.');
    }

    public function destroy(Planning $planning)
    {
        $planning->delete();
        return redirect()->route('admin.distribution.plannings.index')
            ->with('success', 'Planning supprimé.');
    }
}
