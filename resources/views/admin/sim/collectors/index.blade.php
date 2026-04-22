@extends('layouts.admin')

@section('title', 'SIM — Collecteurs')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-users me-2 text-primary"></i>Collecteurs SIM</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="fas fa-plus me-1"></i> Nouveau collecteur
        </button>
    </div>

    {{-- Filtres --}}
    <div class="card shadow mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                        <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactif</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                    <a href="{{ route('admin.sim.collectors') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Collectes</th>
                        <th>Dernière sync</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collectors as $collector)
                    <tr>
                        <td class="fw-semibold">{{ $collector->name }}</td>
                        <td>{{ $collector->email }}</td>
                        <td>{{ $collector->phone }}</td>
                        <td>
                            @if($collector->status === 'active')
                                <span class="badge bg-success">Actif</span>
                            @elseif($collector->status === 'inactive')
                                <span class="badge bg-secondary">Inactif</span>
                            @else
                                <span class="badge bg-danger">Suspendu</span>
                            @endif
                        </td>
                        <td>{{ $collector->total_collections }}</td>
                        <td class="small text-muted">
                            {{ $collector->last_sync ? $collector->last_sync->diffForHumans() : 'Jamais' }}
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning"
                                onclick="openEdit({{ $collector->id }}, '{{ addslashes($collector->name) }}', '{{ $collector->email }}', '{{ $collector->phone }}', '{{ $collector->status }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.sim.collectors.destroy', $collector->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer {{ addslashes($collector->name) }} ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun collecteur trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($collectors->hasPages())
        <div class="card-footer">{{ $collectors->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Création --}}
<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.sim.collectors.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nouveau collecteur SIM</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" required value="{{ old('phone') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                        <div class="form-text">Minimum 6 caractères</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Statut</label>
                        <select name="status" class="form-select">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Créer</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Édition --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEdit" method="POST">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Modifier le collecteur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="editPhone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nouveau mot de passe <small class="text-muted">(laisser vide = inchangé)</small></label>
                        <input type="password" name="password" class="form-control" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Statut</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEdit(id, name, email, phone, status) {
    document.getElementById('formEdit').action = '/admin/sim/collectors/' + id;
    document.getElementById('editName').value   = name;
    document.getElementById('editEmail').value  = email;
    document.getElementById('editPhone').value  = phone;
    document.getElementById('editStatus').value = status;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
@endpush

@endsection
