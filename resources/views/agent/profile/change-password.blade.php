@extends('layouts.agent')

@section('title', 'Changer mot de passe - Agent CSAR')

@section('content')
<div class="container-fluid">
    <div class="profile-header">
        <h1><i class="fas fa-key"></i> Changer le mot de passe</h1>
        <p>Choisissez un mot de passe sécurisé.</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('agent.profile.update-password') }}">
            @csrf

            <div class="grid">
                <div class="field">
                    <label>Mot de passe actuel *</label>
                    <input type="password" name="current_password" required>
                    @error('current_password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label>Nouveau mot de passe *</label>
                    <input type="password" name="password" required minlength="8">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label>Confirmer le nouveau mot de passe *</label>
                    <input type="password" name="password_confirmation" required minlength="8">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="{{ route('agent.profile') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.container-fluid { padding: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
.profile-header { text-align: center; margin-bottom: 2rem; color: #fff; }
.profile-header h1 { font-size: 2.2rem; margin: 0 0 .5rem; font-weight: 700; }
.profile-header p { opacity: .9; margin: 0; }
.card { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,.12); }
.grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap: 1.25rem; }
.field label { display:block; font-weight: 700; color:#374151; margin-bottom:.35rem; }
.field input { width:100%; padding:.8rem .9rem; border:1px solid #e5e7eb; border-radius:10px; background:#f9fafb; }
.error { margin-top:.35rem; color:#b91c1c; font-size:.9rem; }
.actions { display:flex; gap: .75rem; justify-content:center; margin-top: 1.5rem; flex-wrap: wrap; }
.btn { display:inline-flex; align-items:center; gap:.5rem; padding:.75rem 1.25rem; border-radius:10px; text-decoration:none; border:none; cursor:pointer; font-weight:800; }
.btn-primary { background:#667eea; color:#fff; }
.btn-primary:hover { background:#5a67d8; }
.btn-secondary { background:#6b7280; color:#fff; }
.btn-secondary:hover { background:#4b5563; }
</style>
@endsection


