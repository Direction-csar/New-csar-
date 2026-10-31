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
        if (is_string($request->input('telephone'))) {
            $request->merge(['telephone' => PublicRequest::normalizePhone($request->input('telephone'))]);
        }
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        // Validation des données
        $request->validate([
            'nom'                => 'required|string|max:255',
            'prenom'             => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'telephone'          => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'attachment'         => 'prohibited',
            'objet'              => 'required|string|max:255',
            'description'        => 'required|string|max:2000',
            'type_demande'       => 'required|string|in:aide_alimentaire,demande_audience,information_generale,autre',
            'type_demande_autre' => 'required_if:type_demande,autre|nullable|string|max:255',
            'region'             => 'required_if:type_demande,aide_alimentaire|nullable|string|max:255',
            'adresse'            => 'nullable|string|max:500',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
        ]);

        if ($request->type_demande === 'autre' && $request->type_demande_autre) {
            $request->merge(['type_demande' => 'autre : ' . $request->type_demande_autre]);
        }

        try {
            // Vérifier les doublons (anti-duplication)
            $duplicate = PublicRequest::checkDuplicate([
                'full_name' => $request->nom . ' ' . $request->prenom,
                'phone' => $request->telephone,
                'subject' => $request->objet,
            ], 24);

            if ($duplicate) {
                $msg = 'Cette soumission ne peut pas être enregistrée. Utilisez le suivi sécurisé ou contactez le CSAR.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 409);
                }
                return redirect()->back()->withErrors(['duplicate' => $msg])->withInput();
            }

            // Générer un code de suivi unique
            $trackingCode = PublicRequest::generateTrackingCode();
            $requesterId = PublicRequest::generateRequesterId([
                'full_name' => $request->nom . ' ' . $request->prenom,
                'phone' => $request->telephone,
            ]);

            // Préparer l'adresse (combiner région et adresse si fournie)
            $address = $request->adresse ?: ($request->region ?? '');
            if ($request->adresse && $request->region) {
                $address = $request->adresse . ', ' . $request->region;
            }

            // Créer la demande en base de données
            $publicRequest = PublicRequest::create([
                'name' => $request->nom,
                'full_name' => $request->nom . ' ' . $request->prenom,
                'email' => $request->email,
                'phone' => PublicRequest::normalizePhone($request->telephone),
                'subject' => $request->objet,
                'type' => $request->type_demande ?? 'aide_alimentaire',
                'address' => $address,
                'description' => $request->description,
                'tracking_code' => $trackingCode,
                'status' => 'pending',
                'workflow_status' => 'soumise',
                'requester_id' => $requesterId,
                'request_date' => now()->toDateString(),
                'region' => $request->region ?? '',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'urgency' => 'medium',
                'preferred_contact' => 'email',
                'sms_sent' => false,
                'is_viewed' => false,
            ]);

            // Log initial du workflow
            $publicRequest->logWorkflowAction('Demande créée', 'Soumission initiale par le demandeur');

            // Déclencher l'événement pour créer une notification
            try {
                event(new \App\Events\DemandeCreated($publicRequest));
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la soumission
                \Log::error('Erreur événement DemandeCreated', ['exception_type' => get_class($e)]);
            }

            // Créer une notification pour l'admin
            try {
                \App\Services\NotificationService::notifyNewRequest($publicRequest);
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la soumission
                \Log::error('Erreur notification admin', ['exception_type' => get_class($e)]);
            }

            // Email de confirmation à l'utilisateur
            try {
                $emailService = new \App\Services\AdminEmailService();
                $emailService->sendUserConfirmation($request->email, 'request', [
                    'name'          => $request->nom . ' ' . $request->prenom,
                    'tracking_code' => $trackingCode,
                    'type'          => $publicRequest->type,
                    'objet'         => $request->objet,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email confirmation demande', ['exception_type' => get_class($e)]);
            }

            // Email notification admin
            try {
                $emailService = new \App\Services\AdminEmailService();
                $emailService->sendRequestNotification($publicRequest);
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email notification demande admin', ['exception_type' => get_class($e)]);
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
                    'redirect' => route('demande.success', ['code' => $trackingCode])
                ]);
            }

            // Rediriger vers la page de succès
            return redirect()->route('demande.success')->with([
                'success' => $successMessage,
                'tracking_code' => $trackingCode
            ]);

        } catch (\Exception $e) {
            // Do not log submitted personal data, SQL bindings or provider payloads.
            \Log::error('Erreur lors de la création de la demande', [
                'exception_type' => get_class($e),
            ]);

            // Vérifier si c'est une requête AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la soumission de votre demande. Veuillez réessayer.',
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
