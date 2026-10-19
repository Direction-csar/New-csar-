<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Afficher la liste des produits
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            // Tri
            $sortBy = $request->get('sort', 'name');
            $sortDirection = $request->get('direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $products = $query->paginate(15);

            return view('admin.products.index', compact('products'));
        } catch (\Exception $e) {
            Log::error('Erreur dans ProductController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des produits.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Enregistrer un nouveau produit
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'name' => $request->name,
                'type' => $request->type,
                'unit' => $request->unit,
                'description' => $request->filled('description')
                    ? $request->description
                    : json_encode(['formats_kg' => [5, 10, 25, 50, 100]]),
                'unit_price' => $request->unit_price,
                'category' => $request->category,
                'image' => $request->hasFile('image') ? $request->file('image')->store('products', 'public') : null,
                'is_active' => $request->has('is_active')
            ]);

            // Créer une notification
            Notification::create([
                'type' => 'product_created',
                'title' => 'Nouveau produit créé',
                'message' => "Un nouveau produit {$product->name} a été créé",
                'data' => ['product_id' => $product->id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produit créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du produit: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du produit.');
        }
    }

    /**
     * Afficher un produit spécifique
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return redirect()->route('admin.products.edit', $product->id);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du produit: ' . $e->getMessage());
            return redirect()->route('admin.products.index')
                ->with('error', 'Produit non trouvé.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $product = Product::findOrFail($id);
            return view('admin.products.edit', compact('product'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'édition du produit: ' . $e->getMessage());
            return redirect()->route('admin.products.index')
                ->with('error', 'Produit non trouvé.');
        }
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $id,
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            $image = $product->image;
            if ($request->hasFile('image')) {
                if ($image) {
                    Storage::disk('public')->delete($image);
                }
                $image = $request->file('image')->store('products', 'public');
            } elseif ($request->boolean('remove_image') && $image) {
                Storage::disk('public')->delete($image);
                $image = null;
            }

            $product->update([
                'name' => $request->name,
                'type' => $request->type,
                'unit' => $request->unit,
                'description' => $request->has('description') ? $request->description : $product->description,
                'unit_price' => $request->unit_price,
                'category' => $request->category,
                'image' => $image,
                'is_active' => $request->has('is_active')
            ]);

            // Créer une notification
            Notification::create([
                'type' => 'product_updated',
                'title' => 'Produit mis à jour',
                'message' => "Le produit {$product->name} a été mis à jour",
                'data' => ['product_id' => $product->id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produit mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du produit: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du produit.');
        }
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);
            $name = $product->name;
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();

            // Créer une notification
            Notification::create([
                'type' => 'product_deleted',
                'title' => 'Produit supprimé',
                'message' => "Le produit {$name} a été supprimé",
                'data' => ['product_id' => $id],
                'user_id' => null
            ]);

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Produit supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du produit: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression du produit.');
        }
    }

    /**
     * API pour obtenir les produits (pour AJAX)
     */
    public function getProducts(Request $request)
    {
        try {
            $products = Product::active()
                ->when($request->filled('search'), function($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%");
                })
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'type', 'category']);

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des produits: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des produits'], 500);
        }
    }

    /**
     * API pour créer un produit rapidement (pour AJAX)
     */
    public function quickCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:50'
        ]);

        try {
            $product = Product::create([
                'name' => $request->name,
                'type' => $request->type,
                'unit' => $request->unit,
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Produit créé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création rapide du produit: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création du produit'], 500);
        }
    }
}
