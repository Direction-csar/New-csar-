<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\SimCollector;
use App\Models\SimMobileCollection;
use App\Models\SimMarket as Market;
use App\Models\SimProduct as Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class SimCollectionController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $collector = SimCollector::where('email', $request->email)->first();

        if (!$collector || !password_verify($request->password, $collector->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$collector->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active'
            ], 403);
        }

        // Créer le token Sanctum
        $token = $collector->createToken('mobile-app', ['mobile-collect'])->plainTextToken;

        // Mettre à jour le device token si fourni
        if ($request->device_token) {
            $collector->update(['device_token' => $request->device_token]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'collector' => [
                    'id' => $collector->id,
                    'name' => $collector->name,
                    'email' => $collector->email,
                    'phone' => $collector->phone,
                    'assigned_zones' => $collector->assigned_zones,
                    'total_collections' => $collector->total_collections,
                    'last_sync' => $collector->last_sync
                ]
            ]
        ]);
    }

    public function getMarkets(): JsonResponse
    {
        $collector = Auth::user();
        
        $markets = Market::where('is_active', true)
            ->select('id', 'name', 'commune', 'market_type', 'latitude', 'longitude')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $markets
        ]);
    }

    public function getProducts(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->with('category:id,name')
            ->select('id', 'sim_product_category_id', 'name', 'unit', 'origin_type', 'display_order')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'unit'     => $p->unit,
                'origin_type' => $p->origin_type,
                'category' => $p->category?->name ?? 'Autres',
            ]);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function submitCollection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'market_id'          => 'required|exists:sim_markets,id',
            'product_id'         => 'required|exists:sim_products,id',
            'provenance'         => 'nullable|string|max:100',
            'quantity_collected' => 'nullable|numeric|min:0',
            'price'              => 'nullable|numeric|min:0',
            'retail_price'       => 'nullable|numeric|min:0',
            'wholesale_price'    => 'nullable|numeric|min:0',
            'collection_date'    => 'required|date|before_or_equal:today',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'photos'             => 'nullable|array|max:5',
            'photos.*'           => 'string',
            'metadata'           => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $collector = $this->getCollectorFromToken($request);
        if (!$collector) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        // Vérifier zone du collecteur si des zones sont assignées
        if ($collector->assigned_zones) {
            $market = Market::find($request->market_id);
            if ($market && !$collector->canAccessZone($market->commune)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à collecter dans cette zone'
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $collection = SimMobileCollection::create([
                'collector_id'       => $collector->id,
                'market_id'          => $request->market_id,
                'product_id'         => $request->product_id,
                'provenance'         => $request->provenance,
                'quantity_collected' => $request->quantity_collected,
                'price'              => $request->price ?? 0,
                'retail_price'       => $request->retail_price,
                'wholesale_price'    => $request->wholesale_price,
                'collection_date'    => $request->collection_date,
                'latitude'           => $request->latitude,
                'longitude'          => $request->longitude,
                'photos'             => $request->photos,
                'metadata'           => $request->metadata,
                'sync_status'        => 'pending'
            ]);

            $collector->incrementCollectionCount();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Collection submitted successfully',
                'data' => [
                    'id' => $collection->id,
                    'sync_status' => $collection->sync_status,
                    'created_at' => $collection->created_at
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit collection',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }

    public function getCollections(Request $request): JsonResponse
    {
        $collector = $this->getCollectorFromToken($request);
        if (!$collector) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $collections = SimMobileCollection::where('collector_id', $collector->id)
            ->with(['market:id,name,commune', 'product:id,name,unit'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(fn($c) => [
                'id'           => $c->id,
                'market'       => ['name' => $c->market?->name ?? ''],
                'product'      => ['name' => $c->product?->name ?? ''],
                'price'        => $c->price,
                'retail_price' => $c->retail_price,
                'collected_at' => $c->collection_date,
                'sync_status'  => $c->sync_status,
            ]);

        return response()->json(['success' => true, 'data' => $collections]);
    }

    public function syncPendingCollections(): JsonResponse
    {
        $collector = Auth::user();

        $pendingCollections = SimMobileCollection::where('collector_id', $collector->id)
            ->where('sync_status', 'pending')
            ->with(['market:id,name,commune', 'product:id,name,unit'])
            ->orderBy('created_at')
            ->get();

        try {
            DB::beginTransaction();

            $syncedCount = 0;
            $syncedIds = [];

            foreach ($pendingCollections as $collection) {
                // Ici vous pourriez ajouter la logique pour synchroniser avec le système SIM principal
                // Pour l'instant, on marque juste comme synchronisé
                
                $collection->markAsSynced();
                $syncedCount++;
                $syncedIds[] = $collection->id;
            }

            // Créer le log de synchronisation
            $collector->syncLogs()->create([
                'data_count' => $syncedCount,
                'sync_type' => 'manual',
                'status' => 'success',
                'synced_data_ids' => $syncedIds,
                'sync_started_at' => now(),
                'sync_completed_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'data' => [
                    'synced_count' => $syncedCount,
                    'synced_ids' => $syncedIds,
                    'last_sync' => now()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Créer le log d'échec
            $collector->syncLogs()->create([
                'data_count' => $pendingCollections->count(),
                'sync_type' => 'manual',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sync_started_at' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Sync error'
            ], 500);
        }
    }

    public function getPendingCollections(): JsonResponse
    {
        $collector = Auth::user();

        $collections = SimMobileCollection::where('collector_id', $collector->id)
            ->where('sync_status', 'pending')
            ->with(['market:id,name,commune', 'product:id,name,unit'])
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $collections
        ]);
    }

    public function getSyncHistory(): JsonResponse
    {
        $collector = Auth::user();

        $syncLogs = $collector->syncLogs()
            ->orderBy('sync_started_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $syncLogs
        ]);
    }

    public function getCollectorStats(Request $request): JsonResponse
    {
        $collector = $this->getCollectorFromToken($request);
        if (!$collector) {
            return response()->json(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'today'        => SimMobileCollection::where('collector_id', $collector->id)->whereDate('collection_date', $today)->count(),
            'this_week'    => SimMobileCollection::where('collector_id', $collector->id)->where('collection_date', '>=', $thisWeek)->count(),
            'this_month'   => SimMobileCollection::where('collector_id', $collector->id)->where('collection_date', '>=', $thisMonth)->count(),
            'total'        => SimMobileCollection::where('collector_id', $collector->id)->count(),
            'pending_sync' => SimMobileCollection::where('collector_id', $collector->id)->where('sync_status', 'pending')->count(),
            'last_sync'    => $collector->last_sync,
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    private function getCollectorFromToken(Request $request): ?SimCollector
    {
        $bearer = $request->bearerToken();
        if (!$bearer) return null;
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
        if (!$token || !$token->tokenable) return null;
        return $token->tokenable instanceof SimCollector ? $token->tokenable : null;
    }

    public function getProfile(Request $request): JsonResponse
    {
        $collector = $this->getCollectorFromToken($request);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $collector->id,
                'name' => $collector->name,
                'email' => $collector->email,
                'phone' => $collector->phone,
                'assigned_zones' => $collector->assigned_zones,
                'status' => $collector->status,
                'total_collections' => $collector->total_collections,
                'last_sync' => $collector->last_sync,
                'created_at' => $collector->created_at
            ]
        ]);
    }

    public function logout(): JsonResponse
    {
        $collector = Auth::user();
        
        // Révoquer tous les tokens
        $collector->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
