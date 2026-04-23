@extends('layouts.public')

@section('title', 'Bulletins SIM - CSAR')

@section('content')
<!-- CSS externe pour les styles SIM -->
<link rel="stylesheet" href="{{ asset('css/sim-styles.css') }}">

<style>
/* Animations modernes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
    }
    50% {
        box-shadow: 0 0 30px rgba(34, 197, 94, 0.6);
    }
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Hero Section Ultra-Moderne avec Gradient Animé */
.hero-section {
    background: linear-gradient(-45deg, #1a472a, #2d5a3d, #4a7c59, #22c55e);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    position: relative;
    overflow: hidden;
    min-height: 70vh;
    display: flex;
    align-items: center;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(255,255,255,0.05) 0%, transparent 50%);
    animation: float 6s ease-in-out infinite;
}

.hero-section::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
    animation: fadeInUp 1s ease-out;
}

.hero-title {
    font-size: 4rem;
    font-weight: 900;
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #e0f2fe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 4px 8px rgba(0,0,0,0.3);
    margin-bottom: 1.5rem;
    line-height: 1.1;
    position: relative;
    display: inline-block;
}

.hero-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, transparent, #22c55e, transparent);
    animation: shimmer 3s linear infinite;
}

.hero-subtitle {
    font-size: 1.5rem;
    color: rgba(255,255,255,0.95);
    font-weight: 600;
    margin-bottom: 1rem;
    animation: slideInLeft 1s ease-out 0.3s both;
}

.hero-description {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.85);
    line-height: 1.6;
    animation: slideInLeft 1s ease-out 0.6s both;
}

.hero-icon {
    font-size: 12rem;
    color: rgba(255,255,255,0.15);
    animation: float 4s ease-in-out infinite;
    position: relative;
}

.hero-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: pulse 3s ease-in-out infinite;
}

/* Filtres Section */
.filters-section {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 4rem 0;
    position: relative;
}

.filters-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

.filter-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 0 0 1px rgba(34, 197, 94, 0.1);
    border: 2px solid transparent;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    backdrop-filter: blur(10px);
}

.filter-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 30px 60px rgba(34, 197, 94, 0.15), 0 0 0 2px rgba(34, 197, 94, 0.3);
    border-color: rgba(34, 197, 94, 0.2);
}

.filter-card::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #22c55e, #16a34a, #22c55e);
    border-radius: 24px;
    opacity: 0;
    z-index: -1;
    transition: opacity 0.3s ease;
}

.filter-card:hover::before {
    opacity: 0.15;
}

.filter-header {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.filter-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

.filter-title {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    position: relative;
    z-index: 2;
}

.filter-form {
    padding: 2.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #ffffff;
}

.form-control:focus, .form-select:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    outline: none;
}

.btn-filter {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border: none;
    border-radius: 14px;
    padding: 1rem 2.5rem;
    font-weight: 700;
    font-size: 1.05rem;
    color: white;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
}

.btn-filter:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}

.btn-filter::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-filter:hover::before {
    left: 100%;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
}

/* Results Section */
.results-section {
    padding: 4rem 0;
    background: #ffffff;
}

.no-results {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 20px;
    border: 2px dashed #d1d5db;
    position: relative;
    overflow: hidden;
}

.no-results::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(34,197,94,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
    opacity: 0.5;
}

.no-results-icon {
    font-size: 6rem;
    color: #22c55e;
    margin-bottom: 2rem;
    animation: pulse 2s ease-in-out infinite;
}

.no-results-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 1rem;
}

.no-results-description {
    font-size: 1.1rem;
    color: #6b7280;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-action {
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
}

.btn-primary-action {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    border: none;
}

.btn-secondary-action {
    background: #ffffff;
    color: #22c55e;
    border: 2px solid #22c55e;
}

.btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    text-decoration: none;
    color: inherit;
}

.btn-primary-action:hover {
    color: white;
}

.btn-secondary-action:hover {
    color: #16a34a;
    background: #f0fdf4;
}

/* Styles pour les cartes bulletin améliorées */
.bulletin-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    border: 1px solid rgba(34, 197, 94, 0.1);
}

.bulletin-card:hover {
    transform: translateY(-8px) rotate(1deg);
    box-shadow: 0 12px 40px rgba(34, 197, 94, 0.2);
    border-color: rgba(34, 197, 94, 0.3);
}

.bulletin-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.bulletin-card:hover::before {
    transform: scaleX(1);
}

.bulletin-cover {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
}

.bulletin-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.bulletin-card:hover .bulletin-image {
    transform: scale(1.1);
}

.bulletin-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.9), rgba(22, 163, 74, 0.9));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.bulletin-overlay i {
    font-size: 3rem;
    color: white;
    transform: scale(0);
    transition: transform 0.3s ease 0.1s;
}

.bulletin-card:hover .bulletin-overlay {
    opacity: 1;
}

.bulletin-card:hover .bulletin-overlay i {
    transform: scale(1);
}

.bulletin-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.bulletin-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    line-height: 1.4;
    color: #1f2937;
    transition: color 0.3s ease;
}

.bulletin-title a {
    color: inherit;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.bulletin-title a:hover {
    color: #22c55e;
}

.bulletin-meta {
    margin-bottom: 1rem;
    flex: 1;
}

.bulletin-date {
    font-size: 0.875rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bulletin-download {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #22c55e;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
    border: 2px solid #22c55e;
    border-radius: 8px;
    transition: all 0.3s ease;
    align-self: flex-start;
}

.bulletin-download:hover {
    background: #22c55e;
    color: white;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

/* Styles modernes pour les nouvelles cartes */
.modern-card {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
}

.bulletin-cover-wrapper {
    position: relative;
    height: 300px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
}

/* Placeholder en cas d'absence d'image */
.bulletin-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: #9ca3af;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
}
.bulletin-image-placeholder i {
    font-size: 4rem;
    margin-bottom: .5rem;
}
.bulletin-image-placeholder span {
    font-weight: 700;
    letter-spacing: .5px;
}

.bulletin-cover-wrapper .bulletin-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.modern-card:hover .bulletin-image {
    transform: scale(1.05) rotate(1deg);
}

.bulletin-overlay-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0) 0%,
        rgba(0,0,0,0.1) 50%,
        rgba(0,0,0,0.4) 100%
    );
    opacity: 0;
    transition: opacity 0.4s ease;
}

.modern-card:hover .bulletin-overlay-gradient {
    opacity: 1;
}

.bulletin-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(34, 197, 94, 0.95);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transform: translateY(-100px);
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.modern-card:hover .bulletin-badge {
    transform: translateY(0);
}

.bulletin-type-badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}

.badge-daily {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
}

.badge-weekly {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.badge-monthly {
    background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
    color: #5b21b6;
}

.badge-quarterly {
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    color: #9a3412;
}

.badge-annual {
    background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
    color: #9f1239;
}

.badge-special {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.bulletin-views {
    margin-left: 1rem;
    font-size: 0.875rem;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.bulletin-excerpt {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.bulletin-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: auto;
}

.btn-view,
.btn-download {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn-view {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #16a34a;
    border: 2px solid #bbf7d0;
}

.btn-view:hover {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    border-color: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.3);
}

.btn-download {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    border: 2px solid #22c55e;
}

.btn-download:hover {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
}

.btn-view::before,
.btn-download::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-view:hover::before,
.btn-download:hover::before {
    width: 300px;
    height: 300px;
}

/* Sidebar cards améliorées */
.sidebar-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.sidebar-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.sidebar-card .card-header {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-bottom: 2px solid #22c55e;
    padding: 1rem 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.sidebar-card .card-body {
    padding: 1.25rem;
}

/* Styles additionnels pour la sidebar */
.logo-container {
    position: relative;
    display: inline-block;
}

.logo-shine {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.5) 50%, transparent 70%);
    transform: rotate(45deg) translate(-100%, -100%);
    transition: transform 0.6s;
}

.sidebar-card:hover .logo-shine {
    transform: rotate(45deg) translate(100%, 100%);
}

.btn-glow {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-glow::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-glow:hover::before {
    left: 100%;
}

.news-item {
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.news-item:last-child {
    border-bottom: none;
}

.news-item:hover {
    padding-left: 0.5rem;
    background: linear-gradient(90deg, rgba(34, 197, 94, 0.05) 0%, transparent 100%);
}

.news-date {
    font-size: 0.8rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.news-title {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.4;
}

.news-title a {
    color: #1f2937;
    text-decoration: none;
    transition: color 0.3s ease;
}

.news-title a:hover {
    color: #22c55e;
}

.see-all-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #22c55e;
    font-weight: 600;
    text-decoration: none;
    margin-top: 1rem;
    transition: all 0.3s ease;
}

.see-all-link:hover {
    gap: 0.75rem;
    color: #16a34a;
}

.empty-state {
    text-align: center;
    padding: 2rem 0;
}

.btn-pulse {
    animation: pulse 2s ease-in-out infinite;
}

.presentation-card {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
}

.news-card {
    position: relative;
    overflow: hidden;
}

.news-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
    transform: translate(30px, -30px);
}

.events-card {
    background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);
}

.help-card {
    background: linear-gradient(135deg, #ffffff 0%, #dbeafe 100%);
    border: 2px solid rgba(59, 130, 246, 0.1);
}

.btn-outline-success {
    color: #22c55e;
    border-color: #22c55e;
    background: transparent;
    transition: all 0.3s ease;
}

.btn-outline-success:hover {
    background: #22c55e;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .hero-icon {
        font-size: 8rem;
    }
    
    .filter-form {
        padding: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-action {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .bulletin-card {
        margin-bottom: 1rem;
    }
    
    .bulletin-cover-wrapper { height: 180px; }
    
    .sidebar-card {
        margin-top: 2rem;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .no-results-title {
        font-size: 1.5rem;
    }
    
    .no-results-description {
        font-size: 1rem;
    }
}

/* Animations d'entrée */
.fade-in {
    animation: fadeInUp 0.8s ease-out;
}

.slide-in-left {
    animation: slideInLeft 0.8s ease-out;
}

.slide-in-right {
    animation: slideInRight 0.8s ease-out;
}
</style>

<!-- Hero Section Ultra-Moderne -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <i class="fas fa-chart-bar me-3" style="color: #ffd700; animation: glow 2s ease-in-out infinite;"></i>
                        Bulletins SIM
                </h1>
                    <p class="hero-subtitle">
                    Surveillance des Indicateurs de Marché
                </p>
                    <p class="hero-description">
                        Analysez les tendances du marché et les indicateurs économiques pour une meilleure prise de décision stratégique
                </p>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="hero-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filtres Section -->
<section class="filters-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="filter-card fade-in">
                    <div class="filter-header">
                        <h2 class="filter-title">
                            <i class="fas fa-filter me-2"></i>
                            Filtres de recherche avancés
                        </h2>
                </div>
                    <div class="filter-form">
                        <form action="{{ route('sim.index', ['locale' => app()->getLocale()]) }}" method="GET">
                            <div class="row g-4">
                                <div class="col-lg-4 col-md-6">
                                    <div class="form-group">
                                        <label for="search" class="form-label">
                                            <i class="fas fa-search"></i>
                                Recherche
                            </label>
                            <input type="text" id="search" name="search" 
                                               class="form-control" placeholder="Titre, description, contenu..."
                                               value="{{ request('search') }}">
                                    </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-6">
                                    <div class="form-group">
                                        <label for="report_type" class="form-label">
                                            <i class="fas fa-calendar-alt"></i>
                                Type
                            </label>
                                        <select id="report_type" name="report_type" class="form-select">
                                            <option value="">Tous les types</option>
                                <option value="daily" {{ request('report_type') == 'daily' ? 'selected' : '' }}>Quotidien</option>
                                <option value="weekly" {{ request('report_type') == 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                                <option value="monthly" {{ request('report_type') == 'monthly' ? 'selected' : '' }}>Mensuel</option>
                                <option value="quarterly" {{ request('report_type') == 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                                <option value="annual" {{ request('report_type') == 'annual' ? 'selected' : '' }}>Annuel</option>
                            </select>
                                    </div>
                        </div>

                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label for="region" class="form-label">
                                            <i class="fas fa-map-marker-alt"></i>
                                Région
                            </label>
                                        <select id="region" name="region" class="form-select">
                                            <option value="">Toutes les régions</option>
                                <option value="Dakar" {{ request('region') == 'Dakar' ? 'selected' : '' }}>Dakar</option>
                                <option value="Thiès" {{ request('region') == 'Thiès' ? 'selected' : '' }}>Thiès</option>
                                <option value="Diourbel" {{ request('region') == 'Diourbel' ? 'selected' : '' }}>Diourbel</option>
                                <option value="Fatick" {{ request('region') == 'Fatick' ? 'selected' : '' }}>Fatick</option>
                                <option value="Kaolack" {{ request('region') == 'Kaolack' ? 'selected' : '' }}>Kaolack</option>
                                <option value="Kolda" {{ request('region') == 'Kolda' ? 'selected' : '' }}>Kolda</option>
                                <option value="Louga" {{ request('region') == 'Louga' ? 'selected' : '' }}>Louga</option>
                                <option value="Matam" {{ request('region') == 'Matam' ? 'selected' : '' }}>Matam</option>
                                <option value="Saint-Louis" {{ request('region') == 'Saint-Louis' ? 'selected' : '' }}>Saint-Louis</option>
                                <option value="Tambacounda" {{ request('region') == 'Tambacounda' ? 'selected' : '' }}>Tambacounda</option>
                                <option value="Ziguinchor" {{ request('region') == 'Ziguinchor' ? 'selected' : '' }}>Ziguinchor</option>
                                <option value="Kaffrine" {{ request('region') == 'Kaffrine' ? 'selected' : '' }}>Kaffrine</option>
                                <option value="Kédougou" {{ request('region') == 'Kédougou' ? 'selected' : '' }}>Kédougou</option>
                                <option value="Sédhiou" {{ request('region') == 'Sédhiou' ? 'selected' : '' }}>Sédhiou</option>
                            </select>
                                    </div>
                        </div>

                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label for="market_sector" class="form-label">
                                            <i class="fas fa-industry"></i>
                                Secteur
                            </label>
                                        <select id="market_sector" name="market_sector" class="form-select">
                                            <option value="">Tous les secteurs</option>
                                <option value="agriculture" {{ request('market_sector') == 'agriculture' ? 'selected' : '' }}>Agriculture</option>
                                <option value="livestock" {{ request('market_sector') == 'livestock' ? 'selected' : '' }}>Élevage</option>
                                <option value="fisheries" {{ request('market_sector') == 'fisheries' ? 'selected' : '' }}>Pêche</option>
                                <option value="manufacturing" {{ request('market_sector') == 'manufacturing' ? 'selected' : '' }}>Manufacture</option>
                                <option value="services" {{ request('market_sector') == 'services' ? 'selected' : '' }}>Services</option>
                                <option value="trade" {{ request('market_sector') == 'trade' ? 'selected' : '' }}>Commerce</option>
                            </select>
                                    </div>
                                </div>
                        </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn-filter">
                                <i class="fas fa-search me-2"></i>
                                    Filtrer les bulletins
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
                        </div>
</section>

<!-- Results Section (Grille bulletins + Sidebar) -->
<section class="results-section">
    <div class="container">
        <div class="row">
            <!-- Grille des bulletins -->
            <div class="col-lg-8">
                @if($reports->count() === 0)
                    <div class="no-results fade-in">
                        <div class="no-results-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="no-results-title">Aucun bulletin SIM disponible</h3>
                        <p class="no-results-description">
                            Les bulletins de surveillance des indicateurs de marché seront publiés ici dès qu'ils seront disponibles.
                        </p>
                        <div class="action-buttons">
                            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn-action btn-primary-action">
                                <i class="fas fa-home"></i>
                                Retour à l'accueil
                            </a>
                            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="btn-action btn-secondary-action">
                                <i class="fas fa-envelope"></i>
                                Nous contacter
                            </a>
                        </div>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($reports as $report)
                            <div class="col-md-6 col-lg-4">
                                <div class="bulletin-card modern-card">
                                    <div class="bulletin-cover-wrapper">
                                        <div class="bulletin-image-placeholder">
                                            <i class="fas fa-chart-bar"></i>
                                            <span>SIM Report</span>
                                        </div>
                                        <div class="bulletin-overlay-gradient">
                                            <div class="bulletin-badge">
                                                <i class="fas fa-file-pdf"></i>
                                                <span>PDF</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bulletin-content">
                                        <div class="bulletin-type-badge badge-{{ $report->report_type }}">
                                            {{ $report->report_type_label }}
                                        </div>
                                        <h5 class="bulletin-title">
                                            <a href="{{ route('sim.show', ['locale' => app()->getLocale(), 'simReport' => $report->id]) }}" title="{{ $report->title }}">
                                                {{ Str::limit($report->title, 60) }}
                                            </a>
                                        </h5>
                                        <div class="bulletin-meta">
                                            <span class="bulletin-date">
                                                <i class="far fa-calendar-alt"></i>
                                                {{ $report->published_at ? $report->published_at->format('d M Y') : $report->created_at->format('d M Y') }}
                                            </span>
                                            @if($report->view_count > 0)
                                                <span class="bulletin-views">
                                                    <i class="far fa-eye"></i>
                                                    {{ $report->view_count }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($report->description)
                                            <p class="bulletin-excerpt">
                                                {{ Str::limit($report->description, 100) }}
                                            </p>
                                        @endif
                                        <div class="bulletin-actions">
                                            @php
                                                $downloadLabel = 'PDF';
                                                if ($report->document_file) {
                                                    $path = storage_path('app/public/' . $report->document_file);
                                                    $size = file_exists($path) ? number_format(filesize($path) / 1048576, 2) . ' Mo' : null;
                                                    if ($size) { $downloadLabel = 'PDF ' . $size; }
                                                }
                                            @endphp
                                            <a href="{{ route('sim.download', ['locale' => app()->getLocale(), 'simReport' => $report->id]) }}" class="btn-download w-100">
                                                <i class="fas fa-download"></i>
                                                {{ $downloadLabel }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $reports->withQueryString()->links() }}
                    </div>
                @endif
            </div>

            <!-- Sidebar droite -->
            <div class="col-lg-4">
                <div class="sidebar-card presentation-card">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Présentation du CSAR</strong>
                    </div>
                    <div class="card-body text-center">
                        <div class="logo-container">
                            <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" alt="CSAR" class="img-fluid mb-3" style="max-height:120px;">
                            <div class="logo-shine"></div>
                        </div>
                        <p class="text-muted mb-3">Commissariat à la Sécurité Alimentaire et à la Résilience</p>
                        <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="btn btn-success btn-glow">
                            <i class="fas fa-arrow-right me-2"></i>
                            En savoir plus
                        </a>
                    </div>
                </div>

                <div class="sidebar-card news-card">
                    <div class="card-header">
                        <i class="fas fa-newspaper me-2"></i>
                        <strong>Actualités</strong>
                    </div>
                    <div class="card-body">
                        @forelse(($latestNews ?? collect()) as $news)
                            <div class="news-item">
                                <div class="news-date">
                                    <i class="far fa-calendar-alt"></i>
                                    {{ optional($news->published_at)->format('d F Y') }}
                                </div>
                                <h6 class="news-title">
                                    <a href="{{ route('news.show', ['locale' => app()->getLocale(), 'id' => $news->id]) }}">
                                        {{ Str::limit($news->title, 100) }}
                                    </a>
                                </h6>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-inbox text-muted mb-2" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0">Aucune actualité pour le moment.</p>
                            </div>
                        @endforelse
                        @if(($latestNews ?? collect())->count() > 0)
                            <a href="{{ route('news', ['locale' => app()->getLocale()]) }}" class="see-all-link">
                                <span>Toutes les actualités</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="sidebar-card events-card">
                    <div class="card-header">
                        <i class="fas fa-calendar-check me-2"></i>
                        <strong>Événements</strong>
                    </div>
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fas fa-calendar-times text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted">Aucun événement à venir.</p>
                            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-envelope me-1"></i>
                                Nous contacter
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-card help-card">
                    <div class="card-header">
                        <i class="fas fa-question-circle me-2"></i>
                        <strong>Comment pouvons-nous vous aider ?</strong>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted mb-3">Notre équipe est à votre disposition pour répondre à vos questions</p>
                        <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="btn btn-success btn-pulse w-100">
                            <i class="fas fa-headset me-2"></i>
                            Contactez-nous
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// Animation d'entrée des éléments avec effet cascade
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) translateX(0)';
                    entry.target.classList.add('animate-in');
                }, index * 100);
            }
        });
    }, {
        threshold: 0.1
    });
    
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});

// Effets interactifs avancés
document.querySelectorAll('.btn-filter, .btn-action').forEach(btn => {
    // Effet de shimmer
    btn.addEventListener('mouseenter', function() {
        this.style.backgroundPosition = '200% 0';
        this.style.transform = 'translateY(-4px) scale(1.02)';
    });
    
    btn.addEventListener('mouseleave', function() {
        this.style.backgroundPosition = '-200% 0';
        this.style.transform = 'translateY(0) scale(1)';
    });
    
    // Effet de clic
    btn.addEventListener('mousedown', function() {
        this.style.transform = 'translateY(-2px) scale(0.98)';
    });
    
    btn.addEventListener('mouseup', function() {
        this.style.transform = 'translateY(-4px) scale(1.02)';
    });
});

// Animation des icônes au survol
document.querySelectorAll('.hero-icon, .no-results-icon').forEach(icon => {
    icon.addEventListener('mouseenter', function() {
        this.style.animation = 'pulse 1s ease-in-out infinite, float 2s ease-in-out infinite';
    });
    
    icon.addEventListener('mouseleave', function() {
        this.style.animation = 'float 6s ease-in-out infinite';
    });
});

// Effet de parallaxe sur le hero
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero-section');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

// Animation des formulaires
document.querySelectorAll('.form-control, .form-select').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateY(-2px)';
        this.parentElement.style.boxShadow = '0 10px 25px rgba(34, 197, 94, 0.15)';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateY(0)';
        this.parentElement.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.05)';
    });
});

// Effet de typing pour le titre
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.innerHTML = '';
    
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    
    type();
}

// Animation de typing au chargement
window.addEventListener('load', function() {
    const title = document.querySelector('.hero-title');
    if (title) {
        const originalText = title.textContent;
        typeWriter(title, originalText, 80);
    }
});

// Effet de particules flottantes
function createFloatingParticles() {
    const hero = document.querySelector('.hero-section');
    if (!hero) return;
    
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.style.position = 'absolute';
        particle.style.width = Math.random() * 4 + 2 + 'px';
        particle.style.height = particle.style.width;
        particle.style.background = 'rgba(255, 255, 255, 0.1)';
        particle.style.borderRadius = '50%';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.top = Math.random() * 100 + '%';
        particle.style.animation = `float ${Math.random() * 10 + 5}s ease-in-out infinite`;
        particle.style.animationDelay = Math.random() * 5 + 's';
        
        hero.appendChild(particle);
    }
}

// Créer les particules au chargement
createFloatingParticles();

// Effet de glow sur les cartes
document.querySelectorAll('.filter-card, .no-results').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.boxShadow = '0 40px 80px rgba(34, 197, 94, 0.2)';
        this.style.borderColor = '#22c55e';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.15)';
        this.style.borderColor = '#e2e8f0';
    });
});

// Animation de chargement des filtres
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('.btn-filter');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Recherche en cours...';
    btn.disabled = true;
    
    // Simuler un délai de chargement
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-search me-2"></i>Filtrer les bulletins';
        btn.disabled = false;
    }, 2000);
});

// Effet de révélation progressive
const revealElements = document.querySelectorAll('.form-group, .action-buttons');
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

revealElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s ease';
    revealObserver.observe(el);
});
</script>
@endpush
@endsection