@extends('layouts.admin')

@section('title', 'Détail bon-matière')
@section('page-title', 'Bon-matière ' . $bonMatiere->numero_bon)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="h5 mb-0"><i class="fas fa-ticket-alt me-2"></i>{{ $bonMatiere->numero_bon }}</span>
                    <span class="badge bg-{{ $bonMatiere->statut === 'livre' ? 'success' : ($bonMatiere->statut === 'annule' ? 'danger' : 'warning') }}">
                        {{ $bonMatiere->statut }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Bénéficiaire :</strong> {{ $bonMatiere->beneficiaire?->name }}</div>
                        <div class="col-md-6"><strong>Téléphone :</strong> {{ $bonMatiere->beneficiaire?->phone ?? '—' }}</div>
                        <div class="col-md-6"><strong>Planning :</strong> {{ $bonMatiere->planning?->name }}</div>
                        <div class="col-md-6"><strong>Campagne :</strong> {{ $bonMatiere->planning?->campaign?->name }}</div>
                        <div class="col-md-6"><strong>Quantité :</strong> {{ number_format($bonMatiere->quantite_kg, 2, ',', ' ') }} kg</div>
                        <div class="col-md-6"><strong>Catégorie :</strong> {{ $bonMatiere->categorie }}</div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Ticket de retrait</h6>
                    @if($bonMatiere->ticket)
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h3 class="font-monospace">{{ $bonMatiere->ticket->code }}</h3>
                            <p class="text-muted mb-0">QR : <code>{{ $bonMatiere->ticket->qr_data }}</code></p>
                            <p class="mt-2 mb-0">
                                Statut :
                                <span class="badge bg-{{ $bonMatiere->ticket->used ? 'success' : 'info' }}">
                                    {{ $bonMatiere->ticket->used ? 'Utilisé le ' . $bonMatiere->ticket->used_at?->format('d/m/Y H:i') : 'Non utilisé' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if(!$bonMatiere->ticket->used)
                    <form action="{{ route('admin.distribution.tickets.reissue', $bonMatiere->ticket) }}" method="POST" class="mt-3">
                        @csrf
                        @method('POST')
                        <div class="input-group">
                            <input type="text" name="reason" class="form-control" placeholder="Motif de réédition (ticket perdu)" required>
                            <button type="submit" class="btn btn-warning">Rééditer le ticket</button>
                        </div>
                    </form>
                    @endif
                    @else
                    <p class="text-muted">Aucun ticket associé.</p>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.distribution.bon-matieres.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                        @if($bonMatiere->statut !== 'annule')
                        <form action="{{ route('admin.distribution.bon-matieres.cancel', $bonMatiere) }}" method="POST" onsubmit="return confirm('Annuler ce bon et rétablir le stock ?')">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban me-1"></i>Annuler le bon
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
