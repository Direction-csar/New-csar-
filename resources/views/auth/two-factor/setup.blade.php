@extends('layouts.admin')

@section('title', 'Activer l\'authentification 2FA')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i> Activer la double authentification</h4>
                </div>
                <div class="card-body">
                    <p>1. Scannez ce QR code avec <strong>Google Authenticator</strong>, <strong>Authy</strong> ou tout équivalent TOTP :</p>

                    <div class="text-center my-3">
                        {!! $qrSvg !!}
                    </div>

                    <p class="small text-muted">Si vous ne pouvez pas scanner, saisissez la clé manuellement :</p>
                    <div class="alert alert-secondary text-center">
                        <code class="user-select-all">{{ $secret }}</code>
                    </div>

                    <hr>

                    <p>2. Saisissez le code à 6 chiffres généré par votre application :</p>

                    <form method="POST" action="{{ route(($enableRoute ?? 'admin.2fa.enable')) }}">
                        @csrf
                        <div class="mb-3">
                            <input type="text"
                                   name="code"
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                   inputmode="numeric"
                                   pattern="\d{6}"
                                   maxlength="6"
                                   placeholder="000000"
                                   required
                                   autofocus
                                   autocomplete="one-time-code">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Activer la 2FA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
