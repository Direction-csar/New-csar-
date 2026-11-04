@extends('layouts.public')
@section('title', 'Effectuer une demande — Portail CSAR')
@section('content')
<div class="selection-container">
    <div class="sel-header">
        <h1><i class="fas fa-landmark"></i> Portail des demandes</h1>
        <p>Commissariat à la Sécurité alimentaire et à la Résilience — déposez votre demande en ligne ou par lettre. Vous pourrez la suivre en temps réel grâce à un code de suivi unique.</p>
    </div>

    <div class="sel-grid">
        <a href="{{ route('demande.create', ['mode' => 'formulaire']) }}" class="sel-card">
            <div class="sel-icon"><i class="fas fa-keyboard"></i></div>
            <h2>Remplir une demande en ligne</h2>
            <p>Formulaire guidé en 5 étapes : identité, coordonnées, objet, localisation et pièces justificatives.</p>
            <span class="sel-go">Commencer <i class="fas fa-arrow-right"></i></span>
        </a>
        <a href="{{ route('demande.create', ['mode' => 'lettre']) }}" class="sel-card">
            <div class="sel-icon"><i class="fas fa-file-arrow-up"></i></div>
            <h2>Déposer une lettre de demande</h2>
            <p>Téléversez votre courrier déjà rédigé (PDF, DOCX, JPG ou PNG) et renseignez les informations indispensables.</p>
            <span class="sel-go">Déposer <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>

    <div class="sel-note">
        <i class="fas fa-lock"></i>
        <p>Vos données sont transmises de façon sécurisée et réservées aux agents habilités du CSAR. En cas d'urgence : <strong>+221 77 645 92 42</strong> — <strong>contact@csar.sn</strong></p>
    </div>
</div>

<style>
.selection-container{max-width:820px;margin:40px auto 60px;padding:0 18px}
.sel-header{text-align:center;margin-bottom:32px}
.sel-header h1{font-size:1.9rem;font-weight:800;color:#0d9488;margin-bottom:8px}
.sel-header p{color:#475569;font-size:1.02rem;max-width:640px;margin:0 auto}
.sel-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
.sel-card{display:flex;flex-direction:column;align-items:center;text-align:center;background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px 24px;text-decoration:none;color:inherit;transition:.2s;box-shadow:0 4px 12px rgba(2,132,199,.06)}
.sel-card:hover{border-color:#0d9488;box-shadow:0 10px 26px rgba(13,148,136,.14);transform:translateY(-2px)}
.sel-icon{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#0284c7);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.6rem;margin-bottom:16px}
.sel-card h2{font-size:1.15rem;font-weight:700;color:#0f172a;margin-bottom:8px}
.sel-card p{color:#64748b;font-size:.92rem;margin-bottom:18px;flex:1}
.sel-go{color:#0284c7;font-weight:600;font-size:.95rem}
.sel-note{display:flex;gap:12px;align-items:flex-start;background:#f0fdfa;border:1px solid #99f6e4;border-radius:12px;padding:14px 18px;color:#0f766e;font-size:.9rem}
.sel-note i{margin-top:3px}
@media(max-width:640px){.sel-grid{grid-template-columns:1fr}}
</style>
@endsection
