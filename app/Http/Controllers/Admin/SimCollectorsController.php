<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimCollector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SimCollectorsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = SimCollector::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $collectors = $query->latest()->paginate(20);

            return view('admin.sim.collectors.index', compact('collectors'));
        } catch (\Exception $e) {
            Log::error('SimCollectorsController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du chargement des collecteurs.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:sim_collectors,email',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'status'   => 'required|in:active,inactive,suspended',
        ]);

        try {
            SimCollector::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'password_hash' => password_hash($request->password, PASSWORD_BCRYPT),
                'status'        => $request->status,
                'assigned_zones' => [],
            ]);

            return redirect()->route('admin.sim.collectors')
                ->with('success', "Collecteur {$request->name} créé avec succès.");
        } catch (\Exception $e) {
            Log::error('SimCollectorsController@store: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Erreur lors de la création du collecteur.');
        }
    }

    public function update(Request $request, $id)
    {
        $collector = SimCollector::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:100',
            'email'  => 'required|email|unique:sim_collectors,email,' . $id,
            'phone'  => 'required|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            $data = [
                'name'   => $request->name,
                'email'  => $request->email,
                'phone'  => $request->phone,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $request->validate(['password' => 'min:6|confirmed']);
                $data['password_hash'] = password_hash($request->password, PASSWORD_BCRYPT);
            }

            $collector->update($data);

            return redirect()->route('admin.sim.collectors')
                ->with('success', "Collecteur {$collector->name} mis à jour.");
        } catch (\Exception $e) {
            Log::error('SimCollectorsController@update: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour.');
        }
    }

    public function destroy($id)
    {
        try {
            $collector = SimCollector::findOrFail($id);
            $name = $collector->name;
            $collector->delete();

            return redirect()->route('admin.sim.collectors')
                ->with('success', "Collecteur {$name} supprimé.");
        } catch (\Exception $e) {
            Log::error('SimCollectorsController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la suppression.');
        }
    }
}
