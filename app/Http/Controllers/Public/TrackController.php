<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class TrackController extends Controller
{
    public function index()
    {
        // Utiliser public.suivi si la route est /suivi, sinon public.track
        $view = request()->is('suivi') || request()->is('*/suivi') ? 'public.suivi' : 'public.track';
        return view($view);
    }
    
    public function track(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);
        
        Log::info('TRACK DEBUG: code=[' . $request->tracking_code . '] len=' . strlen($request->tracking_code));
        $publicRequest = Demande::where('tracking_code', $request->tracking_code)->first();
        
        // Déterminer quelle vue utiliser
        $view = request()->is('suivi') || request()->is('*/suivi') ? 'public.suivi' : 'public.track';
        
        if (!$publicRequest) {
            return view($view, ['notFound' => true]);
        }
        
        // Optional phone verification
        if ($request->phone && $publicRequest->telephone !== $request->phone) {
            return view($view, ['notFound' => true]);
        }
        
        return view($view, ['request' => $publicRequest]);
    }
    
    public function verify($code)
    {
        $demande = Demande::where('tracking_code', $code)->first();
        $view = 'public.track';
        if (!$demande) {
            return view($view, ['notFound' => true]);
        }
        return view($view, ['request' => $demande]);
    }

    public function download($code)
    {
        $publicRequest = Demande::where('tracking_code', $code)->first();
        
        if (!$publicRequest) {
            abort(404);
        }
        
        $pdf = Pdf::loadView('public.pdf.request', ['request' => $publicRequest]);
        
        return $pdf->download("demande-{$publicRequest->tracking_code}.pdf");
    }
}
