<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CollectorLocation;
use App\Models\SimCollector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectorLocationController extends Controller
{
    public function updateLocation(Request $request)
    {
        $collector = $request->user() instanceof SimCollector
            ? $request->user()
            : SimCollector::find($request->input('collector_id'));

        if (!$collector) {
            return response()->json(['success' => false, 'message' => 'Collecteur non identifié'], 401);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'status' => 'nullable|in:active,collecting,paused,offline',
            'current_market' => 'nullable|string',
            'collections_today' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $location = CollectorLocation::updateOrCreate(
            ['collector_id' => $collector->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'status' => $request->status ?? 'active',
                'current_market' => $request->current_market,
                'collections_today' => $request->collections_today ?? 0,
                'last_activity_at' => now(),
                'metadata' => $request->metadata ?? [],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Position mise à jour',
            'data' => $location
        ]);
    }

    public function getActiveCollectors()
    {
        $locations = CollectorLocation::with('collector')
            ->where('last_activity_at', '>=', now()->subMinutes(10))
            ->whereIn('status', ['active', 'collecting', 'paused'])
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'collector_id' => $location->collector_id,
                    'collector_name' => $location->collector->name,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                    'accuracy' => (float) $location->accuracy,
                    'status' => $location->status,
                    'current_market' => $location->current_market,
                    'collections_today' => $location->collections_today,
                    'last_activity' => $location->last_activity_at->diffForHumans(),
                    'is_online' => $location->isOnline(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }
}
