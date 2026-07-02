@extends('layouts.public')

@section('title', 'Partager un témoignage - CSAR')
@section('meta_description', 'Partagez votre expérience avec le CSAR. Votre témoignage nous aide à progresser.')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold" style="color: #166534;">Partager mon témoignage</h1>
                    <p class="lead text-muted">Votre retour est précieux pour améliorer nos missions</p>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <div class="card border-0 shadow" style="border-radius: 24px;">
                    <div class="card-body p-5">
                        <form action="{{ route('testimonials.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required
                                           style="border-radius: 12px; border-color: #bbf7d0;">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}"
                                           style="border-radius: 12px; border-color: #bbf7d0;">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="organization" class="form-label fw-semibold">Organisation / Structure</label>
                                    <input type="text" class="form-control form-control-lg @error('organization') is-invalid @enderror"
                                           id="organization" name="organization" value="{{ old('organization') }}"
                                           style="border-radius: 12px; border-color: #bbf7d0;">
                                </div>

                                <div class="col-md-6">
                                    <label for="type" class="form-label fw-semibold">Type de témoignage</label>
                                    <select class="form-select form-select-lg" id="type" name="type"
                                            style="border-radius: 12px; border-color: #bbf7d0;">
                                        <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>Général</option>
                                        <option value="mission" {{ old('type') == 'mission' ? 'selected' : '' }}>Mission sur le terrain</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mission-fields" style="display: none;">
                                    <label for="mission_location" class="form-label fw-semibold">Lieu de la mission</label>
                                    <input type="text" class="form-control form-control-lg"
                                           id="mission_location" name="mission_location" value="{{ old('mission_location') }}"
                                           style="border-radius: 12px; border-color: #bbf7d0;">
                                </div>

                                <div class="col-md-6 mission-fields" style="display: none;">
                                    <label for="mission_date" class="form-label fw-semibold">Date de la mission</label>
                                    <input type="date" class="form-control form-control-lg"
                                           id="mission_date" name="mission_date" value="{{ old('mission_date') }}"
                                           style="border-radius: 12px; border-color: #bbf7d0;">
                                </div>

                                <div class="col-12">
                                    <label for="rating" class="form-label fw-semibold">Note</label>
                                    <div class="star-rating mb-2">
                                        @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating', 5) == $i ? 'checked' : '' }} />
                                        <label for="star{{ $i }}" title="{{ $i }} étoiles"><i class="fas fa-star"></i></label>
                                        @endfor
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label fw-semibold">Votre témoignage <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('message') is-invalid @enderror"
                                              id="message" name="message" rows="6" required minlength="10"
                                              style="border-radius: 12px; border-color: #bbf7d0; resize: vertical;">{{ old('message') }}</textarea>
                                    @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Minimum 10 caractères. Votre témoignage sera publié après validation.</div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg" style="border-radius: 50px; padding: 14px 48px;">
                                        <i class="fas fa-paper-plane me-2"></i>Envoyer mon témoignage
                                    </button>
                                    <a href="{{ route('testimonials.index') }}" class="btn btn-outline-secondary btn-lg ms-2" style="border-radius: 50px;">
                                        Voir les témoignages
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 8px;
}
.star-rating input {
    display: none;
}
.star-rating label {
    font-size: 2rem;
    color: #d1d5db;
    cursor: pointer;
    transition: color 0.2s;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #fbbf24;
}
</style>

<script>
document.getElementById('type').addEventListener('change', function() {
    const fields = document.querySelectorAll('.mission-fields');
    fields.forEach(f => f.style.display = this.value === 'mission' ? 'block' : 'none');
});
// Trigger on load
if(document.getElementById('type').value === 'mission') {
    document.querySelectorAll('.mission-fields').forEach(f => f.style.display = 'block');
}
</script>
@endsection
