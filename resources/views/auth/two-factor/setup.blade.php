@extends($layout ?? 'layouts.auth')

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

                    <p>2. Après avoir scanné le QR code (ou saisi la clé), cliquez ci-dessous pour finaliser :</p>

                    <form method="POST" action="{{ route($enableRoute ?? 'admin.2fa.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-arrow-right me-2"></i> Continuer vers les codes de récupération
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
