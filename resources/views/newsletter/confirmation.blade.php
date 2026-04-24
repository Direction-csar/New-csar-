@extends('layouts.public')

@section('title', 'Abonnement Newsletter - CSAR')

@section('content')
<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 60px 20px;">
    <div style="max-width: 640px; width: 100%; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); overflow: hidden;">

        {{-- Barre supérieure verte --}}
        <div style="height: 6px; background: linear-gradient(90deg, #2d7d46, #4caf70, #8bc34a);"></div>

        <div style="padding: 56px 48px; text-align: center;">

            {{-- Icône animée --}}
            <div style="width: 90px; height: 90px; background: linear-gradient(135deg, #2d7d46, #4caf70); border-radius: 50%; margin: 0 auto 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(45,125,70,0.3);">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>

            {{-- Logo CSAR --}}
            <div style="margin-bottom: 24px;">
                <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" style="height: 52px; object-fit: contain;">
            </div>

            <h1 style="font-size: 26px; font-weight: 700; color: #1a3c2a; margin: 0 0 12px;">
                Vérifiez votre messagerie
            </h1>
            <p style="font-size: 16px; color: #4a7c59; font-weight: 500; margin: 0 0 32px;">
                Un email de confirmation est en route.
            </p>

            {{-- Encadré informatif --}}
            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 12px; padding: 24px 28px; text-align: left; margin-bottom: 32px;">
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
                    <span style="font-size: 22px; flex-shrink: 0;">📩</span>
                    <div>
                        <strong style="color: #1a3c2a; font-size: 15px;">Étape 1 — Vérifiez votre boîte mail</strong>
                        <p style="color: #4a7c59; font-size: 14px; margin: 4px 0 0; line-height: 1.5;">
                            Nous vous avons envoyé un email contenant un lien de confirmation depuis <strong>contact@csar.sn</strong>.
                        </p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
                    <span style="font-size: 22px; flex-shrink: 0;">🔗</span>
                    <div>
                        <strong style="color: #1a3c2a; font-size: 15px;">Étape 2 — Cliquez sur le lien</strong>
                        <p style="color: #4a7c59; font-size: 14px; margin: 4px 0 0; line-height: 1.5;">
                            Cliquez sur le bouton <strong>"Confirmer mon abonnement"</strong> dans l'email pour activer définitivement votre abonnement.
                        </p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <span style="font-size: 22px; flex-shrink: 0;">🚫</span>
                    <div>
                        <strong style="color: #1a3c2a; font-size: 15px;">Email non reçu ?</strong>
                        <p style="color: #4a7c59; font-size: 14px; margin: 4px 0 0; line-height: 1.5;">
                            Vérifiez votre dossier <strong>Spam / Indésirables</strong> et marquez notre email comme "non-spam".
                        </p>
                    </div>
                </div>
            </div>

            {{-- Boutons --}}
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 13px 28px; background: #2d7d46; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; transition: background 0.2s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Retour à l'accueil
                </a>
                <a href="{{ route('news.index', ['locale' => app()->getLocale()]) }}"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 13px 28px; background: white; color: #2d7d46; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; border: 2px solid #2d7d46; transition: all 0.2s;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Voir nos actualités
                </a>
            </div>

            <p style="color: #9ca3af; font-size: 12px; margin-top: 32px;">
                © {{ date('Y') }} CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience
            </p>
        </div>
    </div>
</div>
@endsection
