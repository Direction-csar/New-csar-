@extends('layouts.public')
@section('title', 'Suivi sécurisé de votre demande')
@section('content')
<section class="container py-5" style="max-width:900px">
    <h1>Suivi de votre demande</h1>
    @if(session('tracking_error'))
        <p role="alert" class="alert alert-danger">{{ session('tracking_error') }}</p>
    @endif
    @if(session('tracking_message') && !session('request_tracking_challenge') && !session('tracking_prompt'))
        <p role="status" class="alert alert-info">{{ session('tracking_message') }}</p>
    @endif
    @foreach($errors->all() as $error)
        <p role="alert" class="alert alert-danger">{{ $error }}</p>
    @endforeach

    @if($dossier ?? null)
        <h2>{{ $dossier->tracking_code }}</h2>
        <p><strong>Statut : {{ $dossier->status_label }}</strong></p>
        <dl>
            <dt>Demandeur</dt><dd>{{ $dossier->full_name }}</dd>
            <dt>Objet</dt><dd>{{ $dossier->subject }}</dd>
            <dt>Description</dt><dd>{{ $dossier->description }}</dd>
        </dl>
        @if($dossier->public_reason)
            <p><strong>Réponse du CSAR :</strong> {{ $dossier->public_reason }}</p>
        @endif
        <h3>Historique de votre demande</h3>
        <ol>
            @foreach($dossier->timeline as $step)
                <li class="mb-3">
                    <time datetime="{{ $step['timestamp'] }}">{{ \Carbon\Carbon::parse($step['timestamp'])->format('d/m/Y H:i') }}</time>
                    — {{ $step['label'] }}
                    @if($step['reason'])<p>{{ $step['reason'] }}</p>@endif
                </li>
            @endforeach
        </ol>
        <a class="btn btn-primary" href="{{ route('track.download.direct', ['code' => $dossier->tracking_code]) }}">Télécharger le récépissé PDF</a>
        <p class="mt-3">Cet accès expire après 20 minutes. Une nouvelle vérification sera alors nécessaire.</p>
    @else
        @if(session('request_tracking_challenge') || session('tracking_prompt'))
            @php
                $stateTexts = [
                    'pending' => ['info', 'Envoi du code en cours…'],
                    'delayed' => ['warning', "L'envoi prend plus de temps que prévu. Patientez encore un peu ou demandez un nouveau code."],
                    'sent' => ['success', \App\Services\RequestTrackingService::MESSAGE],
                    'failed' => ['danger', "L'envoi du code a échoué. Demandez un nouveau code dans quelques minutes ou contactez le CSAR au +221 77 645 92 42."],
                    'unavailable' => ['danger', \App\Services\RequestTrackingService::UNAVAILABLE],
                ];
                $stateText = $stateTexts[$deliveryState ?? 'pending'] ?? $stateTexts['pending'];
            @endphp
            <p id="deliveryStatus" role="status" aria-live="polite" class="alert alert-{{ $stateText[0] }}" data-state="{{ $deliveryState ?? 'pending' }}">{{ $stateText[1] }}</p>
            <form method="POST" action="{{ route('suivi.track.direct') }}" class="card p-4">
                @csrf
                <input type="hidden" name="action" value="confirm">
                <label for="verification_code">Code reçu par email</label>
                <input class="form-control mb-3" id="verification_code" name="verification_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code">
                <p class="text-muted small">Le code expire après 10 minutes et autorise au maximum cinq essais.</p>
                <button class="btn btn-primary" type="submit">Consulter ma demande</button>
            </form>
            @if(session('request_tracking_reference'))
                <form method="POST" action="{{ route('suivi.track.direct') }}" class="mt-2">
                    @csrf
                    <input type="hidden" name="action" value="lookup">
                    <input type="hidden" name="tracking_code" value="{{ session('request_tracking_reference') }}">
                    <button class="btn btn-link p-0" type="submit">Corriger l'adresse ou demander un nouveau code</button>
                </form>
            @endif
            <script>
            (function () {
                const box = document.getElementById('deliveryStatus');
                const texts = @json($stateTexts);
                let polls = 0;
                function poll() {
                    if (['sent', 'failed', 'unavailable'].includes(box.dataset.state) || polls++ > 40) return;
                    fetch(@json(route('suivi.delivery')), {headers: {'Accept': 'application/json'}, credentials: 'same-origin'})
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d && d.state && texts[d.state]) {
                                box.dataset.state = d.state;
                                box.className = 'alert alert-' + texts[d.state][0];
                                box.textContent = texts[d.state][1];
                            }
                            setTimeout(poll, 3000);
                        })
                        .catch(() => setTimeout(poll, 5000));
                }
                setTimeout(poll, 1500);
            })();
            </script>
        @elseif(session('tracking_verify'))
            <form method="POST" action="{{ route('suivi.track.direct') }}" class="card p-4">
                @csrf
                <label for="tracking_code_v">Référence de la demande</label>
                <input class="form-control mb-3" id="tracking_code_v" name="tracking_code" maxlength="40" readonly value="{{ old('tracking_code') }}">
                <label for="tracking_email">Adresse email utilisée lors du dépôt</label>
                <input class="form-control mb-3" id="tracking_email" name="email" type="email" maxlength="255" required autocomplete="email">
                <p class="text-muted small">Pour votre sécurité, un code temporaire sera envoyé à cette adresse afin de vérifier qu'elle vous appartient.</p>
                <button class="btn btn-primary" type="submit">Recevoir un code de vérification</button>
            </form>
        @else
            @if(!($mailAvailable ?? true))
                <p role="status" class="alert alert-warning">{{ \App\Services\RequestTrackingService::UNAVAILABLE }}</p>
            @endif
            <p>Saisissez la référence indiquée sur votre récépissé de dépôt.</p>
            <form method="POST" action="{{ route('suivi.track.direct') }}" class="card p-4">
                @csrf
                <input type="hidden" name="action" value="lookup">
                <label for="tracking_code">Référence de la demande</label>
                <input class="form-control mb-3" id="tracking_code" name="tracking_code" maxlength="40" required value="{{ old('tracking_code', $reference ?? '') }}" placeholder="CSAR-XXXXXX-XXXX" autocomplete="off">
                <button class="btn btn-primary" type="submit">Consulter ma demande</button>
            </form>
        @endif
        <p class="mt-3">Si vous n’avez plus accès à l’adresse du dossier, contactez le CSAR pour une vérification d’identité.</p>
    @endif
</section>
@endsection
