<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = Campaign::orderBy('name')->get();
        $selected = $request->input('campaign_id');

        if (!$selected) {
            return view('admin.distribution.reports.index', compact('campaigns'));
        }

        $campaign = Campaign::with('plannings.beneficiaires.bonMatieres', 'plannings.warehouse')->findOrFail($selected);

        $report = [
            'total_planned_kg' => $campaign->plannings->sum('planned_quota_kg'),
            'total_executed_kg' => $campaign->plannings->sum('executed_quota_kg'),
            'total_beneficiaires' => $campaign->plannings->sum(fn ($p) => $p->beneficiaires->count()),
            'total_bons_livres' => $campaign->plannings->sum(
                fn ($p) => $p->beneficiaires->sum(
                    fn ($b) => $b->bonMatieres->where('statut', 'livre')->count()
                )
            ),
            'plannings' => $campaign->plannings->map(function ($p) {
                return [
                    'name' => $p->name,
                    'category' => $p->category,
                    'planned' => $p->planned_quota_kg,
                    'executed' => $p->executed_quota_kg,
                    'beneficiaires' => $p->beneficiaires->count(),
                    'livres' => $p->beneficiaires->sum(fn ($b) => $b->bonMatieres->where('statut', 'livre')->count()),
                ];
            }),
        ];

        return view('admin.distribution.reports.index', compact('campaigns', 'campaign', 'report'));
    }
}
