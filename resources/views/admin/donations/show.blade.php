@extends('layouts.admin')

@section('title', 'Détail du Don')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-hand-holding-heart me-2"></i>Détail du Don #{{ $donation->id }}</h1>
        </div>
        <a href="{{ route('admin.donations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Informations du donateur</h6></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-40">Nom complet</th>
                            <td>{{ $donation->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $donation->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td>{{ $donation->phone ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Anonyme</th>
                            <td>
                                @if($donation->is_anonymous)
                                    <span class="badge badge-secondary ms-1">Anonyme</span>
                                @else
                                    <span class="badge badge-info">Non</span>
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
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Informations du paiement</h6></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-40">Montant</th>
                            <td><strong class="text-success fs-5">{{ number_format($donation->amount, 0, ',', ' ') }} FCFA</strong></td>
                        </tr>
                        <tr>
                            <th>Méthode</th>
                            <td><span class="badge badge-info">{{ $donation->payment_method ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                @if($donation->payment_status === 'success')
                                    <span class="badge badge-success">Complété</span>
                                @elseif($donation->payment_status === 'pending')
                                    <span class="badge badge-warning">En attente</span>
                                @else
                                    <span class="badge badge-danger">Échoué</span>
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
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Actions</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.donations.destroy', $donation->id) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Supprimer définitivement ce don ?')">
                    <i class="fas fa-trash me-2"></i>Supprimer ce don
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
