@extends('layouts.direction-portal')

@section('title', 'Personnel stock - CSAR')
@section('page-title', 'Inspecteurs et magasiniers')

@section('content')
@if(session('generated_password'))
<div class="alert alert-warning">
    <strong>Mot de passe initial (affiché une seule fois) :</strong>
    <code class="user-select-all">{{ session('generated_password') }}</code>
    <div class="small">À transmettre à l'agent de façon sécurisée ; il doit le changer à sa première connexion.</div>
</div>
@endif

<div class="alert alert-info py-2 small">
    <i class="fas fa-info-circle me-1"></i>
    Comptes nominatifs réels uniquement. Un compte désactivé perd immédiatement l'accès (Web et APK). Aucun compte n'est supprimé ; chaque action est historisée.
    Les magasins des inspecteurs se gèrent dans
    <a href="{{ route($portal . '.inspector-assignments.index') }}">Affectations inspecteurs</a>.
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="fas fa-user-plus me-2 text-success"></i>Créer un compte</div>
    <div class="card-body">
        @if($errors->any())<div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route($portal . '.stock-staff.store') }}" class="row g-2">
            @csrf
            <div class="col-md-2">
                <select name="role" id="newRole" class="form-select form-select-sm" required>
                    <option value="inspecteur">Inspecteur</option>
                    <option value="magasinier">Magasinier</option>
                </select>
            </div>
            <div class="col-md-3"><input name="name" class="form-control form-control-sm" placeholder="Nom complet" required value="{{ old('name') }}"></div>
            <div class="col-md-3"><input name="email" type="email" class="form-control form-control-sm" placeholder="E-mail" required value="{{ old('email') }}"></div>
            <div class="col-md-2"><input name="phone" class="form-control form-control-sm" placeholder="Téléphone" required value="{{ old('phone') }}"></div>
            <div class="col-md-2" id="newWh" style="display:none">
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value="">Magasin…</option>
                    @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }} ({{ $w->region }})</option>@endforeach
                </select>
            </div>
            <div class="col-12"><button class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i>Créer</button></div>
        </form>
    </div>
</div>

@foreach([['Inspecteurs', $inspectors, false], ['Magasiniers', $keepers, true]] as [$title, $list, $isKeeper])
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">{{ $title }} ({{ $list->count() }})</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Nom</th><th>E-mail</th><th>Téléphone</th>@if($isKeeper)<th>Magasin</th>@endif<th>État</th><th></th></tr></thead>
            <tbody>
            @forelse($list as $u)
                <tr class="{{ $u->is_active ? '' : 'table-secondary' }}">
                    <form method="POST" action="{{ route($portal . '.stock-staff.update', $u->id) }}" id="f{{ $u->id }}">@csrf @method('PUT')</form>
                    <td><input form="f{{ $u->id }}" name="name" value="{{ $u->name }}" class="form-control form-control-sm"></td>
                    <td><input form="f{{ $u->id }}" name="email" value="{{ $u->email }}" class="form-control form-control-sm"></td>
                    <td><input form="f{{ $u->id }}" name="phone" value="{{ $u->phone }}" class="form-control form-control-sm"></td>
                    @if($isKeeper)
                    <td>
                        <select form="f{{ $u->id }}" name="warehouse_id" class="form-select form-select-sm">
                            @foreach($warehouses as $w)<option value="{{ $w->id }}" {{ $u->warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->region }})</option>@endforeach
                        </select>
                    </td>
                    @endif
                    <td><span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }}">{{ $u->is_active ? 'Actif' : 'Désactivé' }}</span></td>
                    <td class="text-end text-nowrap">
                        <button form="f{{ $u->id }}" class="btn btn-sm btn-outline-primary">Enregistrer</button>
                        <form method="POST" action="{{ route($portal . '.stock-staff.toggle', $u->id) }}" class="d-inline"
                              onsubmit="return confirm('Confirmer ?')">@csrf
                            <button class="btn btn-sm btn-outline-{{ $u->is_active ? 'danger' : 'success' }}">{{ $u->is_active ? 'Désactiver' : 'Réactiver' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted text-center py-3">Aucun compte.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="fas fa-clock-rotate-left me-2 text-secondary"></i>Historique (50 dernières actions)</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Action</th><th>Détail</th></tr></thead>
            <tbody>
            @forelse($history as $h)
                <tr><td class="text-nowrap">{{ $h->created_at?->format('d/m/Y H:i') }}</td><td>{{ $h->action }}</td><td>{{ $h->description }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-muted text-center py-3">Aucune action.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('newRole').addEventListener('change', function () {
    document.getElementById('newWh').style.display = this.value === 'magasinier' ? '' : 'none';
});
</script>
@endsection
