@extends('layouts.public')
@section('title', 'Suivi sécurisé de votre demande')
@section('content')
<section class="container py-5" style="max-width:900px">
    <h1>Suivi de votre demande</h1>
    @if(session('tracking_error'))
        <p role="alert" class="alert alert-danger">{{ session('tracking_error') }}</p>
    @endif
    @if(session('tracking_message'))
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
        <p>Saisissez votre référence et l’adresse email renseignée lors du dépôt. Un code temporaire permettra de vérifier que cette adresse vous appartient.</p>
        <form method="POST" action="{{ route('suivi.track.direct') }}" class="card p-4 mb-4">
            @csrf
            <label for="tracking_code">Référence de la demande</label>
            <input class="form-control mb-3" id="tracking_code" name="tracking_code" maxlength="40" required value="{{ $reference ?? '' }}" autocomplete="off">
            <label for="tracking_email">Adresse email du dossier</label>
            <input class="form-control mb-3" id="tracking_email" name="email" type="email" maxlength="255" required autocomplete="email">
            <button class="btn btn-primary" type="submit">Recevoir un code de vérification</button>
        </form>
        @if(session('request_tracking_challenge') || session('tracking_prompt'))
            <form method="POST" action="{{ route('suivi.track.direct') }}" class="card p-4">
                @csrf
                <input type="hidden" name="action" value="confirm">
                <label for="verification_code">Code reçu par email</label>
                <input class="form-control mb-3" id="verification_code" name="verification_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code">
                <p>Le code expire après 10 minutes et autorise au maximum cinq essais.</p>
                <button class="btn btn-primary" type="submit">Consulter ma demande</button>
            </form>
        @endif
        <p class="mt-3">Le suivi par SMS n’est pas disponible. Si vous n’avez plus accès à l’adresse du dossier, contactez le CSAR pour une vérification d’identité.</p>
    @endif
</section>
@endsection
