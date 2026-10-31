<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
        return view('public.track', ['reference' => $reference, 'dossier' => $dossier?->publicSummary()]);
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

        $request->validate(['tracking_code' => 'required|string|max:40', 'email' => 'required|email|max:255']);
        $this->tracking->issue($request, $request->input('tracking_code'), $request->input('email'));
        return back()->with('tracking_message', RequestTrackingService::MESSAGE)->with('tracking_prompt', true);
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
