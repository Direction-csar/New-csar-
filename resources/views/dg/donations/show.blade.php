@extends('layouts.dg-modern')

@section('title', 'Détail du Don')
@section('page-title', 'Détail Donation - Vue DG')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-hand-holding-heart me-2"></i>Détail du Don #{{ $donation->id }}</h1>
        </div>
        <a href="{{ route('dg.donations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user me-2"></i>Informations du donateur</h6></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-40">Nom complet</th>
                            <td>{{ $donation->is_anonymous ? 'Anonyme' : $donation->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $donation->is_anonymous ? '—' : ($donation->email ?? '—') }}</td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td>{{ $donation->is_anonymous ? '—' : ($donation->phone ?? '—') }}</td>
                        </tr>
                        <tr>
                            <th>Anonyme</th>
                            <td>
                                @if($donation->is_anonymous)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-info">Non</span>
                                @endif
                            </td>
                        </tr>
                        @if($donation->message)
                        <tr>
                            <th>Message</th>
                            <td>{{ $donation->message }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-credit-card me-2"></i>Informations du paiement</h6></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-40">Montant</th>
                            <td><strong class="text-success fs-5">{{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency ?? 'FCFA' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Fournisseur</th>
                            <td>
                                @if($donation->payment_provider === 'paypal')
                                    <span class="badge bg-primary">PayPal</span>
                                @else
                                    <span class="badge bg-info">PayDunya</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Méthode</th>
                            <td><span class="badge bg-secondary">{{ $donation->payment_method ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>{{ $donation->donation_type === 'recurring' ? 'Récurrent' : 'Ponctuel' }}</td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($donation->payment_status === 'success')
                                    <span class="badge bg-success">Complété</span>
                                @elseif($donation->payment_status === 'pending')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                    <span class="badge bg-danger">Échoué</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Référence</th>
                            <td><code>{{ $donation->transaction_id ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $donation->created_at->format('d/m/Y à H:i') }}</td>
                        </tr>
                        @if($donation->processed_at)
                        <tr>
                            <th>Traité le</th>
                            <td>{{ $donation->processed_at->format('d/m/Y à H:i') }}</td>
                        </tr>
                        @endif
                        @if($donation->failure_reason)
                        <tr>
                            <th>Raison échec</th>
                            <td class="text-danger">{{ $donation->failure_reason }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
