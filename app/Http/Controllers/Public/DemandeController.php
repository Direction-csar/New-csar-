<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    public function selection()
    {
        return view('public.demande-selection');
    }

    public function create(Request $request)
    {
        $selectedType = $request->get('type');
        $mode = $request->get('mode');
        if ($mode === 'lettre') {
            return view('public.demande-lettre', compact('selectedType'));
        }
        if ($mode === 'formulaire' || $selectedType) {
            return view('public.demande', compact('selectedType'));
        }
        return view('public.demande-selection', compact('selectedType'));
    }

    /**
     * Dépôt par formulaire en ligne.
     */
    public function store(Request $request)
    {
        $this->normalizeInputs($request);
        $request->validate(array_merge($this->identityRules(), $this->eventRules(), $this->locationRules(), [
            'objet'              => 'required|string|max:255',
            'description'        => 'required|string|max:2000',
            'type_demande'       => 'required|string|in:' . implode(',', array_keys(config('demande.types'))),
            'type_demande_autre' => 'required_if:type_demande,autre|nullable|string|max:255',
            'attachment'         => 'prohibited',
            'justificatifs'      => 'nullable|array|max:' . config('demande.upload.max_attachments'),
            'justificatifs.*'    => 'file|max:' . config('demande.upload.attachment_max_kb'),
            'exactitude'         => 'accepted',
        ]), $this->locationMessages());
        $this->rejectBadMimes($request, 'justificatifs');

        if ($request->type_demande === 'autre' && $request->type_demande_autre) {
            $request->merge(['type_demande' => 'autre : ' . $request->type_demande_autre]);
        }

        return $this->persist($request, 'formulaire', null, $this->storeUploads($request));
    }

    /**
     * Dépôt d'une lettre de demande déjà rédigée — métadonnées obligatoires
     * identiques au formulaire, la description longue vit dans le document.
     */
    public function storeLettre(Request $request)
    {
        $this->normalizeInputs($request);
        $request->validate(array_merge($this->identityRules(), $this->eventRules(), $this->locationRules(), [
            'objet'        => 'required|string|max:255',
            'type_demande' => 'required|string|in:' . implode(',', array_keys(config('demande.types'))),
            'type_demande_autre' => 'required_if:type_demande,autre|nullable|string|max:255',
            'lettre'       => 'required|file|max:' . config('demande.upload.letter_max_kb'),
            'justificatifs'   => 'nullable|array|max:' . config('demande.upload.max_attachments'),
            'justificatifs.*' => 'file|max:' . config('demande.upload.attachment_max_kb'),
            'exactitude'   => 'accepted',
        ]), $this->locationMessages());
        $this->rejectBadMimes($request, 'lettre');
        $this->rejectBadMimes($request, 'justificatifs');

        if ($request->type_demande === 'autre' && $request->type_demande_autre) {
            $request->merge(['type_demande' => 'autre : ' . $request->type_demande_autre]);
        }

        $letterPath = $this->secureStore($request->file('lettre'), config('demande.upload.letter_dir'));
        return $this->persist($request, 'lettre', $letterPath, $this->storeUploads($request));
    }

    private function normalizeInputs(Request $request): void
    {
        $phone = $request->input('telephone');
        if (is_string($phone) || is_int($phone)) {
            $request->merge(['telephone' => trim((string) $phone) === '' ? '' : PublicRequest::normalizePhone((string) $phone)]);
        }
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        foreach (['cni', 'ninea'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => preg_replace('/\s+/', '', $request->input($field))]);
            }
        }
    }

    private function identityRules(): array
    {
        $orgTypes = array_diff(array_keys(config('demande.demandeur_types')), ['particulier']);
        $cniRules = ['nullable', 'string', 'max:30', 'regex:' . config('demande.cni_regex')];
        if (config('demande.cni_required')) {
            $cniRules[0] = 'required_if:demandeur_type,particulier';
        }
        return [
            'nom'            => 'required|string|max:255',
            'prenom'         => 'required|string|max:255',
            'demandeur_type' => 'required|in:' . implode(',', array_keys(config('demande.demandeur_types'))),
            'structure_name' => 'required_if:demandeur_type,' . implode(',', $orgTypes) . '|nullable|string|max:255',
            'cni'            => $cniRules,
            'ninea'          => 'nullable|string|max:30|regex:' . config('demande.ninea_regex'),
            'telephone'      => ['required', 'string', 'regex:' . config('demande.phone_regex')],
            'email'          => 'required|email|max:255',
        ];
    }

    private function eventRules(): array
    {
        return [
            'event_date'         => 'nullable|date|after_or_equal:today',
            'event_location'     => 'nullable|string|max:255',
            'event_participants' => 'nullable|integer|min:1|max:1000000',
        ];
    }

    private function locationRules(): array
    {
        return [
            // Position GPS obligatoire ; à défaut (capture impossible), la région la remplace.
            'latitude'    => 'required_without:region|nullable|numeric|between:-90,90|required_with:longitude',
            'longitude'   => 'required_with:latitude|nullable|numeric|between:-180,180',
            'region'      => 'nullable|string|in:' . implode(',', array_keys(senegal_departements())),
            'departement' => 'nullable|string|max:120',
            'commune'     => 'nullable|string|max:120',
            'adresse'     => 'nullable|string|max:500',
        ];
    }

    private function locationMessages(): array
    {
        return [
            'latitude.required_without' => 'Capturez votre position actuelle ou, à défaut, indiquez votre région.',
            'latitude.required_with'    => 'Position GPS incomplète : capturez à nouveau votre position.',
            'longitude.required_with'   => 'Position GPS incomplète : capturez à nouveau votre position.',
        ];
    }

    /**
     * Contrôle du type MIME réel — l'extension seule ne fait pas foi.
     */
    private function rejectBadMimes(Request $request, string $field): void
    {
        $allowed = config('demande.upload.allowed_mimes');
        $allowedExt = config('demande.upload.allowed_extensions');
        $files = $request->hasFile($field)
            ? (is_array($request->file($field)) ? $request->file($field) : [$request->file($field)])
            : [];
        foreach ($files as $file) {
            $mime = $file->getMimeType();
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($mime, $allowed, true) || !in_array($ext, $allowedExt, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => 'Format de fichier non autorisé (PDF, DOCX, JPG ou PNG uniquement).',
                ]);
            }
        }
    }

    private function storeUploads(Request $request): ?array
    {
        if (!$request->hasFile('justificatifs')) {
            return null;
        }
        $paths = [];
        foreach ($request->file('justificatifs') as $file) {
            $paths[] = $this->secureStore($file, config('demande.upload.attachments_dir'));
        }
        return $paths ?: null;
    }

    /**
     * Stockage privé : nom aléatoire, jamais le nom d'origine exposé,
     * hors de public/ — servi uniquement via les routes agents.
     */
    private function secureStore(\Illuminate\Http\UploadedFile $file, string $dir): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $name = Str::random(40) . '.' . $ext;
        return $file->storeAs($dir, $name, config('demande.upload.disk'));
    }

    private function persist(Request $request, string $mode, ?string $letterPath, ?array $attachments)
    {
        try {
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

            $trackingCode = PublicRequest::generateTrackingCode();
            $requesterId = PublicRequest::generateRequesterId([
                'full_name' => $request->nom . ' ' . $request->prenom,
                'phone' => $request->telephone,
            ]);

            $address = $request->adresse ?: ($request->region ?? '');
            $parts = array_filter([$request->adresse, $request->commune, $request->departement, $request->region]);
            if ($parts) {
                $address = implode(', ', $parts);
            }

            $description = $mode === 'lettre'
                ? 'Lettre de demande déposée — voir le document joint.'
                : $request->description;

            $isParticulier = $request->demandeur_type === 'particulier';
            $publicRequest = PublicRequest::create([
                'name' => $isParticulier ? $request->nom : ($request->structure_name ?? $request->nom),
                'full_name' => $request->nom . ' ' . $request->prenom,
                'email' => $request->email,
                'phone' => PublicRequest::normalizePhone($request->telephone),
                'subject' => $request->objet,
                'type' => $request->type_demande,
                'depot_mode' => $mode,
                'demandeur_type' => $request->demandeur_type,
                'cni' => $isParticulier ? $request->cni : null,
                'ninea' => $isParticulier ? null : $request->ninea,
                'structure_name' => $isParticulier ? null : $request->structure_name,
                'event_date' => $request->event_date,
                'event_location' => $request->event_location,
                'event_participants' => $request->event_participants,
                'address' => $address,
                'description' => $description,
                'tracking_code' => $trackingCode,
                'status' => 'pending',
                'workflow_status' => 'soumise',
                'requester_id' => $requesterId,
                'request_date' => now()->toDateString(),
                'region' => $request->region ?? '',
                'departement' => $request->departement,
                'commune' => $request->commune,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'letter_path' => $letterPath,
                'attachments' => $attachments,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'urgency' => 'medium',
                'preferred_contact' => 'email',
                'sms_sent' => false,
                'is_viewed' => false,
            ]);

            $publicRequest->logWorkflowAction('Demande créée', 'Soumission initiale par le demandeur (' . $mode . ')');

            try {
                event(new \App\Events\DemandeCreated($publicRequest));
            } catch (\Exception $e) {
                \Log::error('Erreur événement DemandeCreated', ['exception_type' => get_class($e)]);
            }

            try {
                \App\Services\NotificationService::notifyNewRequest($publicRequest);
            } catch (\Exception $e) {
                \Log::error('Erreur notification admin', ['exception_type' => get_class($e)]);
            }

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

            try {
                $emailService = new \App\Services\AdminEmailService();
                $emailService->sendRequestNotification($publicRequest);
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email notification demande admin', ['exception_type' => get_class($e)]);
            }

            $successMessage = '✅ Votre demande a bien été transmise ! Code de suivi: ' . $trackingCode;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'tracking_code' => $trackingCode,
                    'is_aide_request' => $request->type_demande === 'aide_alimentaire',
                    'redirect' => route('demande.success', ['code' => $trackingCode])
                ]);
            }

            return redirect()->route('demande.success')->with([
                'success' => $successMessage,
                'tracking_code' => $trackingCode
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la demande', [
                'exception_type' => get_class($e),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la soumission de votre demande. Veuillez réessayer.',
                ], 500);
            }

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
