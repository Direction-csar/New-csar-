@extends('layouts.public')

@section('title', 'Mes données personnelles - RGPD')

@section('content')

<section style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%); min-height: 30vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px;">
    <div style="text-align: center; max-width: 800px; margin: 0 auto;">
        <div style="display: inline-block; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 12px 24px; margin-bottom: 24px;">
            <span style="color: #22c55e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                🔒 Protection des données
            </span>
        </div>
        <h1 style="font-size: 2.8rem; font-weight: 800; color: #fff; margin-bottom: 16px; letter-spacing: -1px;">
            Mes données personnelles
        </h1>
        <p style="font-size: 1.15rem; color: rgba(255,255,255,0.85); max-width: 600px; margin: 0 auto; line-height: 1.7;">
            Conformément au RGPD, vous disposez d'un contrôle total sur vos données personnelles
        </p>
    </div>
</section>

<section style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 60px 20px;">
    <div style="max-width: 900px; margin: 0 auto;">

        @if(session('success'))
        <div style="background: #dcfce7; border-left: 4px solid #22c55e; color: #166534; padding: 16px 20px; border-radius: 8px; margin-bottom: 30px;">
            ✅ {{ session('success') }}
        </div>
        @endif

        {{-- Profil utilisateur --}}
        <div style="background: #fff; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                👤 Vos informations
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                <div>
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Nom</div>
                    <div style="color: #0f172a; font-weight: 600;">{{ $user->name }}</div>
                </div>
                <div>
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Email</div>
                    <div style="color: #0f172a; font-weight: 600;">{{ $user->email }}</div>
                </div>
                <div>
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Rôle</div>
                    <div style="color: #0f172a; font-weight: 600;">{{ ucfirst($user->role) }}</div>
                </div>
                <div>
                    <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Compte créé le</div>
                    <div style="color: #0f172a; font-weight: 600;">{{ $user->created_at?->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Article 20 : Export --}}
        <div style="background: #fff; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-left: 4px solid #3b82f6;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                📥 Télécharger mes données
            </h2>
            <p style="color: #475569; line-height: 1.7; margin-bottom: 20px;">
                <strong>Article 20 du RGPD</strong> — Droit à la portabilité.
                Téléchargez l'ensemble de vos données personnelles au format JSON.
                Cette archive contient votre profil, vos notifications, messages, demandes et préférences.
            </p>
            <form method="POST" action="{{ route('gdpr.export') }}">
                @csrf
                <button type="submit" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(59,130,246,0.3); display: inline-flex; align-items: center; gap: 8px;">
                    📥 Télécharger mes données (JSON)
                </button>
            </form>
        </div>

        {{-- Article 15 : Accès --}}
        <div style="background: #fff; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-left: 4px solid #22c55e;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                ✏️ Modifier mes données
            </h2>
            <p style="color: #475569; line-height: 1.7; margin-bottom: 20px;">
                <strong>Article 16 du RGPD</strong> — Droit de rectification.
                Vous pouvez mettre à jour vos informations personnelles depuis votre profil.
            </p>
            <a href="{{ url('/profile') }}" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(34,197,94,0.3);">
                ✏️ Accéder à mon profil
            </a>
        </div>

        {{-- Article 17 : Suppression --}}
        <div style="background: #fff; border-radius: 16px; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-left: 4px solid #ef4444;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                🗑️ Supprimer mon compte
            </h2>
            <p style="color: #475569; line-height: 1.7; margin-bottom: 20px;">
                <strong>Article 17 du RGPD</strong> — Droit à l'effacement (« droit à l'oubli »).
                Cette action est <strong style="color: #ef4444;">irréversible</strong>. Toutes vos données seront définitivement effacées.
            </p>

            @if($errors->any())
            <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                @foreach($errors->all() as $error)
                <div>⚠️ {{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('gdpr.delete') }}" onsubmit="return confirm('Êtes-vous absolument certain de vouloir supprimer votre compte ? Cette action est définitive et irréversible.');">
                @csrf
                @method('DELETE')

                <div style="margin-bottom: 16px;">
                    <label style="display: block; color: #0f172a; font-weight: 600; margin-bottom: 6px;">Mot de passe actuel</label>
                    <input type="password" name="password" required style="width: 100%; max-width: 400px; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; color: #0f172a; font-weight: 600; margin-bottom: 6px;">
                        Tapez exactement <code style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px;">SUPPRIMER MON COMPTE</code> pour confirmer
                    </label>
                    <input type="text" name="confirmation" required placeholder="SUPPRIMER MON COMPTE" style="width: 100%; max-width: 400px; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem;">
                </div>

                <button type="submit" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(239,68,68,0.3); display: inline-flex; align-items: center; gap: 8px;">
                    🗑️ Supprimer définitivement mon compte
                </button>
            </form>
        </div>

        {{-- Contact DPO --}}
        <div style="background: #f1f5f9; border-radius: 16px; padding: 24px; text-align: center;">
            <p style="color: #475569; margin: 0;">
                💬 Question sur vos données ? Contactez notre <strong>Délégué à la Protection des Données</strong> :
                <a href="mailto:dpo@csar.sn" style="color: #0f766e; font-weight: 600; text-decoration: none;">dpo@csar.sn</a>
            </p>
        </div>

    </div>
</section>

@endsection
