@extends('layouts.admin')

@section('title', 'Doublons')
@section('page-title', 'File de contrôle des doublons')

@section('content')
<div class="container-fluid py-4">
    <h2 class="h4 mb-4">Doublons détectés</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Entité 1</th>
                        <th>Planning 1</th>
                        <th>Entité 2</th>
                        <th>Planning 2</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doublons as $doublon)
                    <tr>
                        <td>{{ $doublon->type }}</td>
                        <td>{{ $doublon->entity1?->name }}</td>
                        <td>{{ $doublon->planning1?->name ?? '—' }}</td>
                        <td>{{ $doublon->entity2?->name }}</td>
                        <td>{{ $doublon->planning2?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $doublon->status === 'confirme' ? 'danger' : ($doublon->status === 'faux_positif' ? 'secondary' : 'warning') }}">
                                {{ $doublon->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.distribution.doublons.update', $doublon) }}" class="d-inline">
                                @csrf
                                @method('PUT')
                                <div class="input-group input-group-sm">
                                    <select name="status" class="form-select" required>
                                        <option value="a_verifier" {{ $doublon->status === 'a_verifier' ? 'selected' : '' }}>À vérifier</option>
                                        <option value="confirme" {{ $doublon->status === 'confirme' ? 'selected' : '' }}>Confirmé</option>
                                        <option value="faux_positif" {{ $doublon->status === 'faux_positif' ? 'selected' : '' }}>Faux positif</option>
                                    </select>
                                    <input type="text" name="justification" class="form-control" placeholder="Justification" value="{{ $doublon->justification }}">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun doublon détecté.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($doublons->hasPages())
        <div class="card-footer">
            {{ $doublons->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
