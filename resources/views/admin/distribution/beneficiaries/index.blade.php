@extends('layouts.admin')

@section('title', 'Bénéficiaires')
@section('page-title', 'Bénéficiaires de distribution')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">👥 Bénéficiaires</h1>
                    <a href="{{ route('admin.distribution.beneficiaries.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Nouveau bénéficiaire</a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success small py-1">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger small py-1">{{ session('error') }}</div>@endif

    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Recherche</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone, CNI">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Planning</label>
                        <select class="form-select form-select-sm" name="planning_id">
                            <option value="">Tous</option>
                            @foreach($plannings as $id => $name)
                            <option value="{{ $id }}" {{ request('planning_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Statut</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">Tous</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="validated" {{ request('status') === 'validated' ? 'selected' : '' }}>Validé</option>
                            <option value="ticket_issued" {{ request('status') === 'ticket_issued' ? 'selected' : '' }}>Ticket émis</option>
                            <option value="kit_collected" {{ request('status') === 'kit_collected' ? 'selected' : '' }}>Kit récupéré</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filtrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Nom</th><th>Téléphone</th><th>CNI</th><th>Planning</th><th>Qté (kg)</th><th>Statut</th><th>Validé par</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($beneficiaries as $b)
                        <tr>
                            <td><a href="{{ route('admin.distribution.beneficiaries.show', $b->id) }}">{{ $b->full_name }}</a></td>
                            <td>{{ $b->phone ?? '—' }}</td>
                            <td>{{ $b->cni ?? '—' }}</td>
                            <td>{{ $b->planning?->name ?? '—' }}</td>
                            <td>{{ number_format($b->quantity_kg, 1, ',', ' ') }}</td>
                            <td>
                                @if($b->status === 'pending') <span class="badge bg-secondary">En attente</span>
                                @elseif($b->status === 'validated') <span class="badge bg-info">Validé</span>
                                @elseif($b->status === 'ticket_issued') <span class="badge bg-warning">Ticket émis</span>
                                @elseif($b->status === 'kit_collected') <span class="badge bg-success">Kit récupéré</span>
                                @endif
                            </td>
                            <td>{{ $b->validator?->name ?? '—' }}</td>
                            <td>{{ $b->validated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.distribution.beneficiaries.show', $b->id) }}" class="btn btn-outline-info btn-sm py-0 px-2"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.distribution.beneficiaries.edit', $b->id) }}" class="btn btn-outline-warning btn-sm py-0 px-2"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.distribution.beneficiaries.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bénéficiaire ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $beneficiaries->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
