@extends('layouts.admin')

@section('title', 'Scan ticket')
@section('page-title', 'Validation d\'un ticket de retrait')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-qrcode fa-3x text-success mb-3"></i>
                    <h4>Valider un retrait</h4>
                    <p class="text-muted">Saisissez le code du ticket ou scannez le QR pour valider la remise.</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.distribution.tickets.scan.process') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="code" class="form-control form-control-lg text-center" placeholder="Code ticket" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check me-2"></i>Valider le retrait
                        </button>
                    </form>

                    <div class="mt-3">
                        <a href="{{ route('admin.distribution.tickets.index') }}" class="btn btn-link">Voir tous les tickets</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
