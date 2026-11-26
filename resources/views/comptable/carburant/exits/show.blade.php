@extends('layouts.direction-portal')

@section('title', 'Sortie ' . $exit->exit_number . ' - Comptable - CSAR')
@section('page-title', 'Sortie ' . $exit->exit_number)

@section('content')
@include('comptable.carburant._nav', ['tab' => 'exits'])

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger py-2">@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $exit->exit_number }}</strong>
                @include('comptable.carburant._status', ['status' => $exit->status])
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $exit->operation_date?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Magasin</dt><dd class="col-sm-8">{{ $exit->warehouse?->name ?? '-' }}</dd>
                    <dt class="col-sm-4">Carburant</dt><dd class="col-sm-8">{{ $exit->fuelLabel() }}</dd>
                    <dt class="col-sm-4">Bénéficiaire</dt><dd class="col-sm-8">{{ $exit->beneficiary_name }}</dd>
                    <dt class="col-sm-4">Véhicule</dt><dd class="col-sm-8">{{ $exit->vehicle_label ?? '-' }} {{ $exit->vehicle_plate ? '(' . $exit->vehicle_plate . ')' : '' }}</dd>
                    <dt class="col-sm-4">Destination</dt><dd class="col-sm-8">{{ $exit->destination ?? '-' }}</dd>
                    <dt class="col-sm-4">Motif</dt><dd class="col-sm-8">{{ $exit->purpose }}</dd>
                    <dt class="col-sm-4">Mission</dt><dd class="col-sm-8">{{ $exit->mission_number ?? '-' }}
                        @if($exit->mission_start_date) du {{ $exit->mission_start_date->format('d/m/Y') }} au {{ $exit->mission_end_date?->format('d/m/Y') }}@endif</dd>
                    <dt class="col-sm-4">Autorisation</dt><dd class="col-sm-8">{{ $exit->authorization_ref ?? '-' }}</dd>
                    <dt class="col-sm-4">Demandé</dt><dd class="col-sm-8">{{ number_format($exit->quantity_requested, 2, ',', ' ') }} L</dd>
                    <dt class="col-sm-4">Autorisé</dt><dd class="col-sm-8">{{ $exit->quantity_authorized !== null ? number_format($exit->quantity_authorized, 2, ',', ' ') . ' L' : '—' }}</dd>
                    <dt class="col-sm-4">Délivré</dt><dd class="col-sm-8">{{ $exit->quantity_delivered !== null ? number_format($exit->quantity_delivered, 2, ',', ' ') . ' L' : '—' }}</dd>
                    @if($exit->odometer_start || $exit->odometer_end)
                    <dt class="col-sm-4">Compteur</dt><dd class="col-sm-8">{{ $exit->odometer_start ?? '—' }} → {{ $exit->odometer_end ?? '—' }} km</dd>
                    @endif
                    @if($exit->delivered_by_name)<dt class="col-sm-4">Délivré par</dt><dd class="col-sm-8">{{ $exit->delivered_by_name }}</dd>@endif
                    @if($exit->notes)<dt class="col-sm-4">Notes</dt><dd class="col-sm-8">{{ $exit->notes }}</dd>@endif
                    @if($exit->rejection_reason)<dt class="col-sm-4">Motif du rejet</dt><dd class="col-sm-8 text-danger">{{ $exit->rejection_reason }}</dd>@endif
                    <dt class="col-sm-4">Créé par</dt><dd class="col-sm-8">{{ $exit->creator?->name }} — {{ $exit->created_at?->format('d/m/Y H:i') }}</dd>
                    @if($exit->authorizer)<dt class="col-sm-4">Autorisé par</dt><dd class="col-sm-8">{{ $exit->authorizer->name }} — {{ $exit->authorized_at?->format('d/m/Y H:i') }}</dd>@endif
                    @if($exit->validator)<dt class="col-sm-4">Validé par</dt><dd class="col-sm-8">{{ $exit->validator->name }} — {{ $exit->validated_at?->format('d/m/Y H:i') }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><strong>Pièces justificatives</strong></div>
            <div class="card-body">
                @if($exit->isEditable())
                <form method="POST" action="{{ route('comptable.carburant.attachments.store', ['type' => 'exit', 'id' => $exit->id]) }}" enctype="multipart/form-data" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-5"><input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <div class="col-md-4">
                        <select name="kind" class="form-select form-select-sm">
                            <option value="justificatif">Justificatif</option>
                            <option value="ordre_mission">Ordre de mission</option>
                            <option value="recu">Reçu</option>
                        </select>
                    </div>
                    <div class="col-md-3"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-upload me-1"></i>Ajouter</button></div>
                </form>
                @endif
                <ul class="list-group list-group-flush">
                    @forelse($exit->attachments as $a)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas {{ $a->isImage() ? 'fa-image' : 'fa-file-pdf' }} me-1"></i>{{ $a->original_name }}
                            <small class="text-muted">({{ $a->sizeHuman() }}, {{ $a->uploader?->name }})</small></span>
                        <span class="d-flex gap-1">
                            <a href="{{ route('comptable.carburant.attachments.download', $a->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i></a>
                            @if($exit->isEditable())
                            <form method="POST" action="{{ route('comptable.carburant.attachments.delete', $a->id) }}" onsubmit="return confirm('Supprimer cette pièce ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted px-0">Aucune pièce jointe.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><strong>Historique</strong></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($exit->statusLogs as $log)
                    <li class="list-group-item px-0 small">
                        <i class="fas fa-history me-1 text-muted"></i>
                        {{ $log->created_at?->format('d/m/Y H:i') }} — <strong>{{ $log->action }}</strong>
                        ({{ $log->from_status ?? '—' }} → {{ $log->to_status }})
                        @if($log->reason)<br><span class="text-muted">Motif : {{ $log->reason }}</span>@endif
                    </li>
                    @empty
                    <li class="list-group-item text-muted px-0">Aucun événement.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Actions</strong></div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('comptable.carburant.pdf', ['type' => 'exit', 'id' => $exit->id]) }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf me-1"></i>PDF du bon de sortie</a>
                <div class="alert alert-light border small mb-0 py-1">
                    Stock disponible ({{ $exit->fuelLabel() }}, ce magasin) :
                    <strong>{{ number_format($available, 0, ',', ' ') }} L</strong>
                </div>

                @if($exit->status === 'draft')
                <form method="POST" action="{{ route('comptable.carburant.exits.authorize', $exit->id) }}">
                    @csrf
                    <label class="form-label small mb-1">Quantité autorisée (L)</label>
                    <input type="number" step="0.01" min="0.01" name="quantity_authorized" class="form-control form-control-sm mb-1" value="{{ $exit->quantity_requested }}" required>
                    <button class="btn btn-info text-white w-100"><i class="fas fa-stamp me-1"></i>Autoriser</button>
                </form>
                @endif

                @if($exit->status === 'authorized')
                <form method="POST" action="{{ route('comptable.carburant.exits.validate', $exit->id) }}" onsubmit="return confirm('Confirmer la livraison ? Le stock sera débité.')">
                    @csrf
                    <label class="form-label small mb-1">Litres réellement délivrés</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $exit->quantity_authorized }}" name="quantity_delivered" class="form-control form-control-sm mb-1" value="{{ $exit->quantity_authorized }}" required>
                    <button class="btn btn-success w-100"><i class="fas fa-check me-1"></i>Valider la livraison</button>
                </form>
                @endif

                @if(in_array($exit->status, ['draft', 'authorized']))
                <form method="POST" action="{{ route('comptable.carburant.exits.reject', $exit->id) }}">
                    @csrf
                    <input type="text" name="reason" class="form-control form-control-sm mb-1" placeholder="Motif du rejet (obligatoire)" required>
                    <button class="btn btn-outline-danger w-100"><i class="fas fa-times me-1"></i>Rejeter</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
