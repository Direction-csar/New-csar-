<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Services\RequestTrackingService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TrackController extends Controller
{
    public function __construct(private RequestTrackingService $tracking)
    {
    }

    public function index(Request $request)
    {
        $reference = (string) $request->query('code', '');
        $dossier = $this->tracking->authorized($request, $reference);
        return view('public.track', [
            'reference' => $reference,
            'dossier' => $dossier?->publicSummary(),
            'mailAvailable' => RequestTrackingService::mailChannelAvailable(),
            'deliveryState' => $this->tracking->deliveryState($request),
        ]);
    }

    /** État d'envoi du code (JSON neutre) pour l'écran de saisie du code. */
    public function deliveryState(Request $request)
    {
        return response()->json(['state' => $this->tracking->deliveryState($request)]);
    }

    public function track(Request $request)
    {
        if ($request->input('action') === 'confirm') {
            $request->validate(['verification_code' => 'required|digits:6']);
            $dossier = $this->tracking->confirm($request, $request->input('verification_code'));
            if (!$dossier) {
                return back()->with('tracking_error', 'Code invalide ou expiré. Demandez un nouveau code si nécessaire.');
            }
            return redirect()->route('suivi.direct', ['code' => $dossier->tracking_code]);
        }

        if ($request->input('action') === 'lookup') {
            $request->validate(['tracking_code' => 'required|string|max:40']);
            $reference = RequestTrackingService::normalizeReference((string) $request->input('tracking_code'));
            if (!PublicRequest::where('tracking_code', $reference)->exists()) {
                return back()->with('tracking_error', 'Référence introuvable. Vérifiez le numéro indiqué sur votre récépissé.')
                    ->withInput(['tracking_code' => $reference]);
            }
            if (!RequestTrackingService::mailChannelAvailable()) {
                return back()->with('tracking_error', RequestTrackingService::UNAVAILABLE)->withInput(['tracking_code' => $reference]);
            }
            // Nouvelle saisie d'adresse : l'ancien défi est abandonné pour revenir à l'écran de l'adresse.
            $request->session()->forget(['request_tracking_challenge', 'request_tracking_reference']);
            return back()->with('tracking_verify', true)->withInput(['tracking_code' => $reference]);
        }

        $request->validate(['tracking_code' => 'required|string|max:40', 'email' => 'required|email|max:255']);
        $reference = RequestTrackingService::normalizeReference((string) $request->input('tracking_code'));
        $request->session()->put('request_tracking_reference', $reference);
        return match ($this->tracking->issue($request, $reference, $request->input('email'))) {
            'unavailable' => back()->with('tracking_error', RequestTrackingService::UNAVAILABLE),
            'throttled' => back()->with('tracking_error', 'Trop de demandes de code pour cette référence. Réessayez dans une heure.')
                ->with('tracking_verify', true)->withInput(['tracking_code' => $reference]),
            default => back()->with('tracking_prompt', true)->with('tracking_message', RequestTrackingService::MESSAGE),
        };
    }

    public function verify(Request $request, $locale, $code = null)
    {
        // The localized route is /{locale}/verifier/{code}: the first segment is
        // the locale, never a dossier reference.
        return redirect()->route('suivi.direct', ['code' => $code ?? $locale]);
    }

    public function download(Request $request, $code)
    {
        $dossier = $this->tracking->authorized($request, $code);
        if (!$dossier) {
            return redirect()->route('suivi.direct', ['code' => $code])
                ->with('tracking_error', 'Vérifiez votre adresse email pour accéder au récépissé.');
        }
        return Pdf::loadView('public.pdf.request', ['request' => $dossier->publicSummary()])
            ->download("demande-{$dossier->tracking_code}.pdf");
    }
}
