@extends('layouts.direction-portal')

@section('title', 'Entrée ' . $entry->reference . ' - Comptable - CSAR')
@section('page-title', 'Entrée ' . $entry->reference)

@section('content')
@include('comptable.carburant._nav', ['tab' => 'entries'])

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger py-2">@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ $entry->reference }}</strong>
                @include('comptable.carburant._status', ['status' => $entry->status])
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $entry->operation_date?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Magasin</dt><dd class="col-sm-8">{{ $entry->warehouse?->name ?? '-' }}</dd>
                    <dt class="col-sm-4">Fournisseur</dt><dd class="col-sm-8">{{ $entry->supplier }}</dd>
                    <dt class="col-sm-4">Carburant</dt><dd class="col-sm-8">{{ $entry->fuelLabel() }}</dd>
                    <dt class="col-sm-4">Quantité</dt><dd class="col-sm-8">{{ number_format($entry->quantity, 2, ',', ' ') }} L</dd>
                    <dt class="col-sm-4">Prix unitaire</dt><dd class="col-sm-8">{{ number_format($entry->unit_price, 0, ',', ' ') }} FCFA/L</dd>
                    <dt class="col-sm-4">Total</dt><dd class="col-sm-8"><strong>{{ number_format($entry->total_amount, 0, ',', ' ') }} FCFA</strong></dd>
                    <dt class="col-sm-4">Facture</dt><dd class="col-sm-8">{{ $entry->invoice_number ?? '-' }} {{ $entry->invoice_date ? '(' . $entry->invoice_date->format('d/m/Y') . ')' : '' }}</dd>
                    <dt class="col-sm-4">Bon de livraison</dt><dd class="col-sm-8">{{ $entry->delivery_note_ref ?? '-' }}</dd>
                    <dt class="col-sm-4">Financement</dt><dd class="col-sm-8">{{ $entry->funding_source ?? '-' }}</dd>
                    @if($entry->notes)<dt class="col-sm-4">Notes</dt><dd class="col-sm-8">{{ $entry->notes }}</dd>@endif
                    @if($entry->rejection_reason)<dt class="col-sm-4">Motif du rejet</dt><dd class="col-sm-8 text-danger">{{ $entry->rejection_reason }}</dd>@endif
                    <dt class="col-sm-4">Créé par</dt><dd class="col-sm-8">{{ $entry->creator?->name }} — {{ $entry->created_at?->format('d/m/Y H:i') }}</dd>
                    @if($entry->validator)<dt class="col-sm-4">Traité par</dt><dd class="col-sm-8">{{ $entry->validator->name }} — {{ $entry->validated_at?->format('d/m/Y H:i') }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><strong>Pièces justificatives</strong></div>
            <div class="card-body">
                @if($entry->isEditable())
                <form method="POST" action="{{ route('comptable.carburant.attachments.store', ['type' => 'entry', 'id' => $entry->id]) }}" enctype="multipart/form-data" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-5"><input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required></div>
                    <div class="col-md-4">
                        <select name="kind" class="form-select form-select-sm">
                            <option value="justificatif">Justificatif</option>
                            <option value="facture">Facture</option>
                            <option value="bon_livraison">Bon de livraison</option>
                            <option value="recu">Reçu</option>
                        </select>
                    </div>
                    <div class="col-md-3"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-upload me-1"></i>Ajouter</button></div>
                </form>
                @endif
                <ul class="list-group list-group-flush">
                    @forelse($entry->attachments as $a)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas {{ $a->isImage() ? 'fa-image' : 'fa-file-pdf' }} me-1"></i>{{ $a->original_name }}
                            <small class="text-muted">({{ $a->sizeHuman() }}, {{ $a->uploader?->name }})</small></span>
                        <span class="d-flex gap-1">
                            <a href="{{ route('comptable.carburant.attachments.download', $a->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i></a>
                            @if($entry->isEditable())
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
                    @forelse($entry->statusLogs as $log)
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
                <a href="{{ route('comptable.carburant.pdf', ['type' => 'entry', 'id' => $entry->id]) }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf me-1"></i>PDF du bon</a>
                @if($entry->status === 'draft')
                <form method="POST" action="{{ route('comptable.carburant.entries.validate', $entry->id) }}" onsubmit="return confirm('Valider cette entrée ? Le stock sera crédité de {{ (int) $entry->quantity }} L.')">
                    @csrf
                    <button class="btn btn-success w-100"><i class="fas fa-check me-1"></i>Valider — créditer le stock</button>
                </form>
                <form method="POST" action="{{ route('comptable.carburant.entries.reject', $entry->id) }}">
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
