@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@php $rp = request()->routeIs('ctc.*') ? 'ctc' : 'admin'; @endphp

@section('title', 'Modifier événement - QR Media Share')
@section('page-title', 'Modifier événement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 {{ $rp === 'ctc' ? 'text-white' : '' }}">
            <i class="fas fa-edit me-2"></i>Modifier l'événement
        </h1>
        <a href="{{ route($rp . '.media-share.show', $event->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route($rp . '.media-share.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $event->title) }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $event->description) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Date</label>
                                <input type="date" name="event_date" value="{{ old('event_date', optional($event->event_date)->format('Y-m-d')) }}" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $event->status === 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ $event->status === 'inactive' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Couverture</label>
                                <input type="file" name="cover_image" accept="image/*" class="form-control">
                            </div>
                        </div>
                        @if($event->cover_image)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $event->cover_image) }}" alt="" style="height:90px; border-radius:8px;">
                            </div>
                        @endif
                        <div class="text-end">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
