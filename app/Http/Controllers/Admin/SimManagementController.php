<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimCollectorAssignment;
use App\Models\SimCollection;
use App\Models\SimCollectionStatus;
use App\Models\SimDataAccessRequest;
use App\Models\SimDepartment;
use App\Models\SimMarket;
use App\Models\SimProduct;
use App\Models\SimProductCategory;
use App\Models\SimRegion;
use App\Models\User;
use App\Services\SimAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SimManagementController extends Controller
{
    public function __construct(
        protected SimAnalyticsService $analytics
    ) {
    }

    public function dashboard()
    {
        $overview = $this->analytics->adminOverview();
        $liveCollections = $this->analytics->liveCollections();
        $recentStatuses = $this->analytics->recentStatuses();
        $pendingRequests = class_exists(SimDataAccessRequest::class)
            ? SimDataAccessRequest::latest()->take(10)->get()
            : collect();

        $regionsCount = SimRegion::count();
        $departmentsCount = SimDepartment::count();

        return view('admin.sim.dashboard', compact('overview', 'liveCollections', 'recentStatuses', 'pendingRequests', 'regionsCount', 'departmentsCount'));
    }

    public function markets(Request $request)
    {
        $query = SimMarket::with(['department.region', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('commune', 'like', "%{$search}%")
                    ->orWhere('market_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('sim_department_id', $request->integer('department_id'));
        }

        $markets = $query->latest()->paginate(15);
        $regions = SimRegion::where('is_active', true)->orderBy('display_order')->get();
        $departments = SimDepartment::where('is_active', true)->orderBy('name')->get();

        return view('admin.sim.markets.index', compact('markets', 'regions', 'departments'));
    }

    public function storeMarket(Request $request)
    {
        $validated = $request->validate([
            'sim_department_id' => 'required|exists:sim_departments,id',
            'name' => 'required|string|max:255',
            'commune' => 'nullable|string|max:255',
            'market_type' => 'required|string|max:50',
            'market_day' => 'nullable|string|max:50',
            'is_permanent' => 'nullable|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['slug'] = $this->uniqueSlug(SimMarket::class, $validated['name']);
        $validated['is_permanent'] = $request->boolean('is_permanent');
        $validated['is_active'] = $request->boolean('is_active', true);

        SimMarket::create($validated);

        return redirect()->route('admin.sim.markets')->with('success', 'Marché ajouté avec succès.');
    }

    public function editMarket(SimMarket $simMarket)
    {
        $regions = SimRegion::where('is_active', true)->orderBy('display_order')->get();
        $departments = SimDepartment::where('is_active', true)->orderBy('name')->get();

        return view('admin.sim.markets.edit', compact('simMarket', 'regions', 'departments'));
    }

    public function updateMarket(Request $request, SimMarket $simMarket)
    {
        $validated = $request->validate([
            'sim_department_id' => 'required|exists:sim_departments,id',
            'name' => 'required|string|max:255',
            'commune' => 'nullable|string|max:255',
            'market_type' => 'required|string|max:50',
            'market_day' => 'nullable|string|max:50',
            'is_permanent' => 'nullable|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($simMarket->name !== $validated['name']) {
            $validated['slug'] = $this->uniqueSlug(SimMarket::class, $validated['name'], $simMarket->id);
        }

        $validated['is_permanent'] = $request->boolean('is_permanent');
        $validated['is_active'] = $request->boolean('is_active');

        $simMarket->update($validated);

        return redirect()->route('admin.sim.markets')->with('success', 'Marché mis à jour avec succès.');
    }

    public function destroyMarket(SimMarket $simMarket)
    {
        if ($simMarket->collections()->exists()) {
            $simMarket->update(['is_active' => false]);
            return redirect()->route('admin.sim.markets')->with('success', 'Marché désactivé car il est déjà lié à des collectes.');
        }

        $simMarket->delete();

        return redirect()->route('admin.sim.markets')->with('success', 'Marché supprimé avec succès.');
    }

    public function categories(Request $request)
    {
        $query = SimProductCategory::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $categories = $query->withCount('products')->orderBy('display_order')->paginate(15);

        return view('admin.sim.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sim_product_categories,name',
            'description' => 'nullable|string|max:2000',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->uniqueSlug(SimProductCategory::class, $validated['name']);
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        SimProductCategory::create($validated);

        return redirect()->route('admin.sim.categories')->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function editCategory(SimProductCategory $simProductCategory)
    {
        return view('admin.sim.categories.edit', compact('simProductCategory'));
    }

    public function updateCategory(Request $request, SimProductCategory $simProductCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sim_product_categories,name,' . $simProductCategory->id,
            'description' => 'nullable|string|max:2000',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($simProductCategory->name !== $validated['name']) {
            $validated['slug'] = $this->uniqueSlug(SimProductCategory::class, $validated['name'], $simProductCategory->id);
        }

        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $simProductCategory->update($validated);

        return redirect()->route('admin.sim.categories')->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroyCategory(SimProductCategory $simProductCategory)
    {
        if ($simProductCategory->products()->exists()) {
            return redirect()->route('admin.sim.categories')->with('error', 'Impossible de supprimer une catégorie liée à des produits.');
        }

        $simProductCategory->delete();

        return redirect()->route('admin.sim.categories')->with('success', 'Catégorie supprimée avec succès.');
    }

    public function products(Request $request)
    {
        $query = SimProduct::with('category');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('origin_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('sim_product_category_id', $request->integer('category_id'));
        }

        $products = $query->orderBy('display_order')->paginate(20);
        $categories = SimProductCategory::where('is_active', true)->orderBy('display_order')->get();

        return view('admin.sim.products.index', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'sim_product_category_id' => 'required|exists:sim_product_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'origin_type' => 'required|string|max:20',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['slug'] = $this->uniqueSlug(SimProduct::class, $validated['name']);
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        SimProduct::create($validated);

        return redirect()->route('admin.sim.products')->with('success', 'Produit ajouté avec succès.');
    }

    public function editProduct(SimProduct $simProduct)
    {
        $categories = SimProductCategory::where('is_active', true)->orderBy('display_order')->get();

        return view('admin.sim.products.edit', compact('simProduct', 'categories'));
    }

    public function updateProduct(Request $request, SimProduct $simProduct)
    {
        $validated = $request->validate([
            'sim_product_category_id' => 'required|exists:sim_product_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'origin_type' => 'required|string|max:20',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($simProduct->name !== $validated['name']) {
            $validated['slug'] = $this->uniqueSlug(SimProduct::class, $validated['name'], $simProduct->id);
        }

        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        $simProduct->update($validated);

        return redirect()->route('admin.sim.products')->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroyProduct(SimProduct $simProduct)
    {
        if ($simProduct->collectionItems()->exists()) {
            $simProduct->update(['is_active' => false]);
            return redirect()->route('admin.sim.products')->with('success', 'Produit désactivé car déjà utilisé dans des collectes.');
        }

        $simProduct->delete();

        return redirect()->route('admin.sim.products')->with('success', 'Produit supprimé avec succès.');
    }

    public function collections(Request $request)
    {
        $query = SimCollection::with(['market.department.region', 'collector', 'supervisor']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('market_id')) {
            $query->where('sim_market_id', $request->integer('market_id'));
        }

        $collections = $query->latest('collected_on')->paginate(20);
        $markets = SimMarket::where('is_active', true)->orderBy('name')->get();

        return view('admin.sim.collections.index', compact('collections', 'markets'));
    }

    public function showCollection(SimCollection $simCollection)
    {
        $simCollection->load([
            'market.department.region',
            'collector',
            'supervisor',
            'validator',
            'rejector',
            'items.product.category',
            'statuses.user',
            'positions.user',
        ]);

        return view('admin.sim.collections.show', compact('simCollection'));
    }

    public function validateCollection(Request $request, SimCollection $simCollection)
    {
        $simCollection->update([
            'status' => SimCollection::STATUS_VALIDATED,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        SimCollectionStatus::create([
            'sim_collection_id' => $simCollection->id,
            'user_id' => auth()->id(),
            'status' => SimCollection::STATUS_VALIDATED,
            'label' => 'Collecte validée par l’admin',
        ]);

        return redirect()->route('admin.sim.collections.show', $simCollection)->with('success', 'Collecte validée avec succès.');
    }

    public function rejectCollection(Request $request, SimCollection $simCollection)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:2000',
        ]);

        $simCollection->update([
            'status' => SimCollection::STATUS_REJECTED,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        SimCollectionStatus::create([
            'sim_collection_id' => $simCollection->id,
            'user_id' => auth()->id(),
            'status' => SimCollection::STATUS_REJECTED,
            'label' => 'Collecte rejetée par l’admin',
        ]);

        return redirect()->route('admin.sim.collections.show', $simCollection)->with('success', 'Collecte rejetée.');
    }

    public function publishCollection(SimCollection $simCollection)
    {
        $simCollection->update([
            'status' => SimCollection::STATUS_PUBLISHED,
        ]);

        SimCollectionStatus::create([
            'sim_collection_id' => $simCollection->id,
            'user_id' => auth()->id(),
            'status' => SimCollection::STATUS_PUBLISHED,
            'label' => 'Collecte publiée',
        ]);

        return redirect()->route('admin.sim.collections.show', $simCollection)->with('success', 'Collecte publiée.');
    }

    public function accessRequests(Request $request)
    {
        $query = SimDataAccessRequest::with('reviewer');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $requests = $query->latest()->paginate(20);

        return view('admin.sim.access-requests.index', compact('requests'));
    }

    public function showAccessRequest(SimDataAccessRequest $simDataAccessRequest)
    {
        return view('admin.sim.access-requests.show', compact('simDataAccessRequest'));
    }

    public function decideAccessRequest(Request $request, SimDataAccessRequest $simDataAccessRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,restricted',
            'admin_notes' => 'nullable|string|max:3000',
            'expires_at' => 'nullable|date',
        ]);

        $validated['reviewed_by'] = auth()->id();
        $validated['reviewed_at'] = now();

        $simDataAccessRequest->update($validated);

        return redirect()->route('admin.sim.access-requests.show', $simDataAccessRequest)->with('success', 'Décision enregistrée avec succès.');
    }

    // --- Régions ---
    public function regions(Request $request)
    {
        $query = SimRegion::withCount('departments');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }
        $regions = $query->orderBy('display_order')->paginate(15);
        return view('admin.sim.regions.index', compact('regions'));
    }

    public function storeRegion(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sim_regions,name',
            'code' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);
        SimRegion::create($validated);
        return redirect()->route('admin.sim.regions')->with('success', 'Région ajoutée.');
    }

    public function editRegion(SimRegion $simRegion)
    {
        return view('admin.sim.regions.edit', compact('simRegion'));
    }

    public function updateRegion(Request $request, SimRegion $simRegion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sim_regions,name,' . $simRegion->id,
            'code' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');
        $simRegion->update($validated);
        return redirect()->route('admin.sim.regions')->with('success', 'Région mise à jour.');
    }

    public function destroyRegion(SimRegion $simRegion)
    {
        if ($simRegion->departments()->exists()) {
            return redirect()->route('admin.sim.regions')->with('error', 'Impossible de supprimer une région qui a des départements.');
        }
        $simRegion->delete();
        return redirect()->route('admin.sim.regions')->with('success', 'Région supprimée.');
    }

    // --- Départements ---
    public function departments(Request $request)
    {
        $query = SimDepartment::with('region')->withCount('markets');
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }
        if ($request->filled('region_id')) {
            $query->where('sim_region_id', $request->integer('region_id'));
        }
        $departments = $query->orderBy('name')->paginate(15);
        $regions = SimRegion::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.sim.departments.index', compact('departments', 'regions'));
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'sim_region_id' => 'required|exists:sim_regions,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        if (SimDepartment::where('sim_region_id', $validated['sim_region_id'])->where('name', $validated['name'])->exists()) {
            return redirect()->route('admin.sim.departments')->with('error', 'Ce département existe déjà pour cette région.');
        }
        SimDepartment::create($validated);
        return redirect()->route('admin.sim.departments')->with('success', 'Département ajouté.');
    }

    public function editDepartment(SimDepartment $simDepartment)
    {
        $regions = SimRegion::where('is_active', true)->orderBy('display_order')->get();
        return view('admin.sim.departments.edit', compact('simDepartment', 'regions'));
    }

    public function updateDepartment(Request $request, SimDepartment $simDepartment)
    {
        $validated = $request->validate([
            'sim_region_id' => 'required|exists:sim_regions,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $simDepartment->update($validated);
        return redirect()->route('admin.sim.departments')->with('success', 'Département mis à jour.');
    }

    public function destroyDepartment(SimDepartment $simDepartment)
    {
        if ($simDepartment->markets()->exists()) {
            return redirect()->route('admin.sim.departments')->with('error', 'Impossible de supprimer un département qui a des marchés.');
        }
        $simDepartment->delete();
        return redirect()->route('admin.sim.departments')->with('success', 'Département supprimé.');
    }

    // --- Assignations collecteur / superviseur ↔ marché ---
    public function assignments(Request $request)
    {
        $query = SimCollectorAssignment::with(['market.department.region', 'collector', 'supervisor', 'assignedBy']);
        if ($request->filled('market_id')) {
            $query->where('sim_market_id', $request->integer('market_id'));
        }
        if ($request->filled('collector_id')) {
            $query->where('collector_id', $request->integer('collector_id'));
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        $assignments = $query->latest()->paginate(20);
        $markets = SimMarket::where('is_active', true)->orderBy('name')->get();
        $collectors = User::where('role', 'agent')->orderBy('name')->get();
        $supervisors = User::where('role', 'responsable')->orderBy('name')->get();
        return view('admin.sim.assignments.index', compact('assignments', 'markets', 'collectors', 'supervisors'));
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'sim_market_id' => 'required|exists:sim_markets,id',
            'collector_id' => 'required|exists:users,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['assigned_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        SimCollectorAssignment::create($validated);
        return redirect()->route('admin.sim.assignments')->with('success', 'Assignation créée.');
    }

    public function updateAssignment(Request $request, SimCollectorAssignment $simCollectorAssignment)
    {
        $validated = $request->validate([
            'supervisor_id' => 'nullable|exists:users,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);
        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }
        $simCollectorAssignment->update($validated);
        return redirect()->route('admin.sim.assignments')->with('success', 'Assignation mise à jour.');
    }

    public function destroyAssignment(SimCollectorAssignment $simCollectorAssignment)
    {
        $simCollectorAssignment->delete();
        return redirect()->route('admin.sim.assignments')->with('success', 'Assignation supprimée.');
    }

    public function live()
    {
        return response()->json([
            'liveCollections' => $this->analytics->liveCollections()->values(),
            'recentStatuses' => $this->analytics->recentStatuses()->values(),
        ]);
    }

    protected function uniqueSlug(string $modelClass, string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $base = $slug ?: 'element-sim';
        $slug = $base;
        $counter = 1;

        while ($modelClass::when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
