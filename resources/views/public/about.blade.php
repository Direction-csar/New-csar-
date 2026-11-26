@extends('layouts.public')

@section('title', 'À propos du CSAR')

@section('content')
<div class="container-fluid py-5">
    <div class="container">
        <!-- En-tête -->
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-primary mb-3">
                    <i class="fas fa-info-circle me-3"></i>À propos du CSAR
                </h1>
                <p class="lead text-muted">Découvrez notre mission, notre vision et notre engagement envers l'humanitaire</p>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $stats['founded_year'] ?? 2010 }}</h3>
                            <p class="stat-label">Année de création</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $stats['total_staff'] ?? 0 }}</h3>
                            <p class="stat-label">Personnel</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $stats['total_warehouses'] ?? 0 }}</h3>
                            <p class="stat-label">Entrepôts</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-number">{{ $stats['beneficiaries'] ?? 0 }}</h3>
                            <p class="stat-label">Bénéficiaires</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations générales -->
        @if($about)
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto">
                <div class="info-card">
                    <h2 class="card-title">
                        <i class="fas fa-building me-2"></i>Informations générales
                    </h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Nom officiel :</strong>
                            <span>{{ $about->official_name ?? 'Comité de Secours et d\'Assistance aux Réfugiés (CSAR)' }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Sigle :</strong>
                            <span>{{ $about->acronym ?? 'CSAR' }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Siège :</strong>
                            <span>{{ $about->headquarters ?? 'Dakar, Sénégal' }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Téléphone :</strong>
                            <span>{{ $about->phone ?? '+221 77 645 92 42' }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Email :</strong>
                            <span>{{ $about->email ?? 'contact@csar.sn' }}</span>
                        </div>
                        <div class="info-item">
                            <strong>Site web :</strong>
                            <a href="{{ $about->website ?? 'https://www.csar.sn' }}" target="_blank">
                                {{ $about->website ?? 'https://www.csar.sn' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission et Vision -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="mission-card">
                    <h3 class="card-title">
                        <i class="fas fa-bullseye me-2"></i>Notre Mission
                    </h3>
                    <p class="card-text">{{ $about->mission ?? 'Le CSAR s\'engage à fournir une assistance humanitaire d\'urgence et des services de secours aux réfugiés et aux populations vulnérables au Sénégal et dans la région.' }}</p>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="vision-card">
                    <h3 class="card-title">
                        <i class="fas fa-eye me-2"></i>Notre Vision
                    </h3>
                    <p class="card-text">{{ $about->vision ?? 'Nous aspirons à un monde où chaque personne déplacée ou réfugiée a accès à une assistance humanitaire de qualité, à la protection et à des solutions durables.' }}</p>
                </div>
            </div>
        </div>

        <!-- Valeurs -->
        @if($about->values)
        <div class="row mb-5">
            <div class="col-12">
                <div class="values-card">
                    <h3 class="card-title">
                        <i class="fas fa-gem me-2"></i>Nos Valeurs
                    </h3>
                    <p class="values-text">{{ $about->values }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Équipe dirigeante -->
        @if($about->leadership && count($about->leadership) > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-users-cog me-2"></i>Équipe dirigeante
                </h2>
                <div class="leadership-grid">
                    @foreach($about->leadership as $member)
                    <div class="leadership-card">
                        <div class="leadership-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="leadership-info">
                            <h4 class="leadership-name">{{ $member['name'] ?? 'Nom non disponible' }}</h4>
                            <p class="leadership-position">{{ $member['position'] ?? 'Poste non défini' }}</p>
                            @if(isset($member['email']))
                            <p class="leadership-email">{{ $member['email'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Partenaires -->
        @if($about->partners && count($about->partners) > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-handshake me-2"></i>Nos Partenaires
                </h2>
                <div class="partners-grid">
                    @foreach($about->partners as $partner)
                    <div class="partner-card">
                        <div class="partner-logo">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="partner-info">
                            <h4 class="partner-name">{{ $partner['name'] ?? 'Partenaire' }}</h4>
                            <p class="partner-description">{{ $partner['description'] ?? 'Description non disponible' }}</p>
                            @if(isset($partner['website']))
                            <a href="{{ $partner['website'] }}" target="_blank" class="partner-website">
                                <i class="fas fa-external-link-alt me-1"></i>Site web
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Certifications -->
        @if($about->certifications && count($about->certifications) > 0)
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title">
                    <i class="fas fa-award me-2"></i>Certifications
                </h2>
                <div class="certifications-grid">
                    @foreach($about->certifications as $certification)
                    <div class="certification-card">
                        <div class="certification-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="certification-info">
                            <h4 class="certification-name">{{ $certification['name'] ?? 'Certification' }}</h4>
                            <p class="certification-description">{{ $certification['description'] ?? 'Description non disponible' }}</p>
                            @if(isset($certification['issuer']))
                            <p class="certification-issuer"><strong>Émetteur :</strong> {{ $certification['issuer'] }}</p>
                            @endif
                            @if(isset($certification['issue_date']))
                            <p class="certification-date"><strong>Émis le :</strong> {{ \Carbon\Carbon::parse($certification['issue_date'])->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @else
        <div class="row">
            <div class="col-12 text-center">
                <div class="empty-state">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">Informations non disponibles</h3>
                    <p class="text-muted">Les informations sur le CSAR ne sont pas encore disponibles.</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.info-card, .mission-card, .vision-card, .values-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.info-grid {
    display: grid;
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.info-item:last-child {
    border-bottom: none;
}

.leadership-grid, .partners-grid, .certifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.leadership-card, .partner-card, .certification-card {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.leadership-card:hover, .partner-card:hover, .certification-card:hover {
    transform: translateY(-5px);
}

.leadership-avatar, .partner-logo, .certification-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.5rem;
}

.section-title {
    color: #333;
    margin-bottom: 2rem;
    font-weight: 600;
}

.empty-state {
    padding: 4rem 2rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .leadership-grid, .partners-grid, .certifications-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
@endsection