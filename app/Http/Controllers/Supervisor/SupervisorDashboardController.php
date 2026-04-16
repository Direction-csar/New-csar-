<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\SimMobileCollection;
use App\Models\SimCollector;
use App\Models\SimSyncLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        // Statistiques globales
        $stats = [
            'total_collectors' => SimCollector::count(),
            'active_collectors' => SimCollector::where('status', 'active')->count(),
            'total_collections' => SimMobileCollection::count(),
            'collections_today' => SimMobileCollection::whereDate('collection_date', today())->count(),
            'pending_sync' => SimMobileCollection::where('sync_status', 'pending')->count(),
        ];

        // Collecteurs avec leurs statistiques
        $collectors = SimCollector::withCount([
            'mobileCollections as total_collections',
            'mobileCollections as pending_collections' => function ($query) {
                $query->where('sync_status', 'pending');
            },
        ])
        ->with(['mobileCollections' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->get()
        ->map(function ($collector) {
            return [
                'id' => $collector->id,
                'name' => $collector->name,
                'username' => $collector->username,
                'phone' => $collector->phone,
                'is_active' => $collector->is_active,
                'total_collections' => $collector->total_collections,
                'pending_collections' => $collector->pending_collections,
                'last_collection' => $collector->mobileCollections->first()?->collection_date,
                'assigned_zones' => $collector->assigned_zones ? json_decode($collector->assigned_zones) : [],
            ];
        });

        // Dernières collectes
        $recentCollections = SimMobileCollection::with('collector')
            ->latest('collection_date')
            ->limit(20)
            ->get();

        // Historique de synchronisation
        $syncHistory = SimSyncLog::latest('sync_started_at')
            ->limit(10)
            ->get();

        return view('supervisor.dashboard', compact('stats', 'collectors', 'recentCollections', 'syncHistory'));
    }

    public function collectorDetails($id)
    {
        $collector = SimCollector::findOrFail($id);

        $collections = SimMobileCollection::where('collector_id', $id)
            ->with(['market:id,name', 'product:id,name,unit'])
            ->latest('collection_date')
            ->paginate(50);

        $stats = [
            'total_collections' => SimMobileCollection::where('collector_id', $id)->count(),
            'synced' => SimMobileCollection::where('collector_id', $id)->where('sync_status', 'synced')->count(),
            'pending' => SimMobileCollection::where('collector_id', $id)->where('sync_status', 'pending')->count(),
            'failed' => SimMobileCollection::where('collector_id', $id)->where('sync_status', 'failed')->count(),
        ];

        return view('supervisor.collector-details', compact('collector', 'collections', 'stats'));
    }

    public function collections()
    {
        $collections = SimMobileCollection::with(['collector:id,name', 'market:id,name', 'product:id,name,unit'])
            ->latest('collection_date')
            ->paginate(100);

        return view('supervisor.collections', compact('collections'));
    }
}
