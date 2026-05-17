@extends('layouts.guest')

@section('title', 'Vérification 2FA')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-key text-success me-2"></i>
                        Vérification 2FA
                    </h4>

                    <p class="text-muted small text-center">
                        Saisissez le code à 6 chiffres généré par votre application d'authentification,
                        ou un code de récupération.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger small">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.2fa.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="text"
                                   name="code"
                                   class="form-control form-control-lg text-center"
                                   placeholder="000000"
                                   inputmode="numeric"
                                   maxlength="8"
                                   required
                                   autofocus
                                   autocomplete="one-time-code">
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt me-1"></i> Valider
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
