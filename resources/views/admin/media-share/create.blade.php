@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@php $rp = request()->routeIs('ctc.*') ? 'ctc' : 'admin'; @endphp

@section('title', 'Nouvel événement - QR Media Share')
@section('page-title', 'Nouvel événement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 {{ $rp === 'ctc' ? 'text-white' : '' }}">
            <i class="fas fa-plus-circle me-2"></i>Nouvel événement
        </h1>
        <a href="{{ route($rp . '.media-share.index') }}" class="btn btn-outline-secondary">
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

                    <form action="{{ route($rp . '.media-share.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Titre de l'événement <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Ex : Cérémonie de remise de diplômes 2026" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Album officiel...">{{ old('description') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Date de l'événement</label>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Image de couverture</label>
                                <input type="file" name="cover_image" accept="image/*" class="form-control">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Créer l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <p class="text-muted small mt-3 {{ $rp === 'ctc' ? 'text-white-50' : '' }}">
                <i class="fas fa-info-circle me-1"></i>Après la création, vous pourrez ajouter les photos/vidéos et générer le QR Code.
            </p>
        </div>
    </div>
</div>
@endsection
