<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RequestController extends Controller
{
    public function index()
    {
        try {
            // Récupérer les demandes avec pagination
            $requests = PublicRequest::latest()->paginate(20);
            
            // Statistiques des demandes
            $stats = [
                'total' => PublicRequest::count(),
                'pending' => PublicRequest::where('status', 'pending')->count(),
                'approved' => PublicRequest::where('status', 'approved')->count(),
                'rejected' => PublicRequest::where('status', 'rejected')->count(),
            ];
            
            Log::info('Accès aux demandes DG', [
                'user_id' => auth()->id(),
                'timestamp' => Carbon::now()
            ]);
            
            return view('dg.requests.index', compact('requests', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des demandes DG', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Erreur lors du chargement des demandes');
        }
    }
    
    public function show($id)
    {
        try {
            $request = PublicRequest::findOrFail($id);
            
            Log::info('Consultation demande DG', [
                'user_id' => auth()->id(),
                'request_id' => $id,
                'timestamp' => Carbon::now()
            ]);
            
            return view('dg.requests.show', compact('request'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la consultation de la demande DG', [
                'user_id' => auth()->id(),
                'request_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Demande non trouvée');
        }
    }
}



































