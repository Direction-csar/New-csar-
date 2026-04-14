@extends('layouts.public')

@section('title', 'Suivi des dons')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-4">
                    <h2 class="h3 mb-0 text-center">
                        <i class="fas fa-search me-2"></i>Suivi des dons
                    </h2>
                </div>
                <div class="card-body p-4 p-md-5">
                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('donations.track') }}" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="{{ request('email') }}" required
                                       placeholder="votre@email.com">
                            </div>
                            <div class="col-md-4">
                                <label for="transaction_id" class="form-label">N° de transaction (optionnel)</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                                       value="{{ request('transaction_id') }}"
                                       placeholder="ID transaction">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Results --}}
                    @if(isset($donations) && $donations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Méthode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($donations as $donation)
                                        <tr>
                                            <td>{{ $donation->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency }}</td>
                                            <td>
                                                @if($donation->payment_status === 'success')
                                                    <span class="badge bg-success">Réussi</span>
                                                @elseif($donation->payment_status === 'pending')
                                                    <span class="badge bg-warning">En cours</span>
                                                @else
                                                    <span class="badge bg-danger">Échoué</span>
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($donation->payment_method) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $donations->links() }}
                    @elseif(request()->has('email'))
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun don trouvé avec cet email.
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Entrez votre email pour consulter l'historique de vos dons.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
