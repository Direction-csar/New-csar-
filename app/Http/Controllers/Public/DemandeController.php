<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
    public function selection()
    {
        return view('public.demande-selection');
    }

    public function create(Request $request)
    {
        $selectedType = $request->get('type');
        return view('public.demande', compact('selectedType'));
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:30',
            'objet' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'type_demande' => 'required|string|max:255',
            'region' => 'required_if:type_demande,aide_alimentaire|nullable|string|max:255',
            'adresse' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        try {
            // Générer un code de suivi unique
            $trackingCode = PublicRequest::generateTrackingCode();
            
            // Préparer l'adresse (combiner région et adresse si fournie)
            $address = $request->adresse ?: $request->region;
            if ($request->adresse && $request->region) {
                $address = $request->adresse . ', ' . $request->region;
            }

            // Créer la demande en base de données
            $publicRequest = PublicRequest::create([
                'name' => $request->nom,
                'full_name' => $request->nom . ' ' . $request->prenom,
                'email' => $request->email,
                'phone' => $request->telephone,
                'subject' => $request->objet,
                'type' => $request->type_demande ?? 'aide_alimentaire',
                'address' => $address,
                'description' => $request->description,
                'tracking_code' => $trackingCode,
                'status' => 'pending',
                'request_date' => now()->toDateString(),
                'region' => $request->region,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'urgency' => 'medium',
                'preferred_contact' => 'email',
                'sms_sent' => false,
                'is_viewed' => false,
            ]);

            // Déclencher l'événement pour créer une notification
            try {
                event(new \App\Events\DemandeCreated($publicRequest));
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la soumission
                \Log::error('Erreur événement DemandeCreated: ' . $e->getMessage());
            }

            // Créer une notification pour l'admin
            try {
                \App\Services\NotificationService::notifyNewRequest($publicRequest);
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la soumission
                \Log::error('Erreur notification admin: ' . $e->getMessage());
            }

            // Message de succès
            $successMessage = '✅ Votre demande a bien été transmise ! Code de suivi: ' . $trackingCode;
            
            // Vérifier si c'est une requête AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'tracking_code' => $trackingCode,
                    'is_aide_request' => $request->type_demande === 'aide_alimentaire',
                    'redirect' => route('request.success', ['code' => $trackingCode])
                ]);
            }
            
            // Rediriger vers la page de succès
            return redirect()->route('request.success')->with([
                'success' => $successMessage,
                'tracking_code' => $trackingCode
            ]);

        } catch (\Exception $e) {
            // Logger l'erreur détaillée
            \Log::error('Erreur lors de la création de la demande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token'])
            ]);
            
            // Vérifier si c'est une requête AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la soumission de votre demande. Veuillez réessayer.',
                    'error_details' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            // En cas d'erreur, rediriger avec un message d'erreur
            return redirect()->back()->withErrors([
                'error' => 'Une erreur est survenue lors de la soumission de votre demande. Veuillez réessayer.'
            ])->withInput();
        }
    }
    
    public function success()
    {
        return view('public.demande-succes');
    }
}