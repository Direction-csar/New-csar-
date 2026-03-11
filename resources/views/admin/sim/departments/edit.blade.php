@extends('layouts.admin')

@section('title', 'SIM — Modifier le département')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier : {{ $simDepartment->name }}</h1>
        <a href="{{ route('admin.sim.departments') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('admin.sim.departments.update', $simDepartment) }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-2">
                    <label class="form-label">Région *</label>
                    <select name="sim_region_id" class="form-select" required>
                        @foreach($regions as $r)
                            <option value="{{ $r->id }}" {{ $simDepartment->sim_region_id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $simDepartment->name) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $simDepartment->code) }}" maxlength="20">
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_dept_active" {{ $simDepartment->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="edit_dept_active">Actif</label>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('admin.sim.departments') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection
