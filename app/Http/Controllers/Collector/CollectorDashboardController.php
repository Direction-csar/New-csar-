<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\SimCollector;
use App\Models\SimMobileCollection;
use App\Models\SimSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CollectorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $collectorId = $request->session()->get('collector_id');
        $collector = SimCollector::findOrFail($collectorId);

        $stats = [
            'total_collections' => $collector->total_collections,
            'last_sync' => $collector->last_sync,
            'assigned_zones' => $collector->assigned_zones ?? [],
        ];

        $recentCollections = collect([]);
        $pendingCount = 0;
        $syncHistory = collect([]);

        if (Schema::hasTable('sim_mobile_collections')) {
            $recentCollections = SimMobileCollection::where('collector_id', $collectorId)
                ->orderBy('collection_date', 'desc')
                ->limit(10)
                ->get();

            $pendingCount = SimMobileCollection::where('collector_id', $collectorId)
                ->where('sync_status', 'pending')
                ->count();
        }

        if (Schema::hasTable('sim_sync_logs')) {
            $syncHistory = SimSyncLog::where('collector_id', $collectorId)
                ->orderBy('sync_started_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('collector.dashboard', compact(
            'collector',
            'stats',
            'recentCollections',
            'pendingCount',
            'syncHistory'
        ));
    }
}
