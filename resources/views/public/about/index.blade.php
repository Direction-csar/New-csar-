@extends('layouts.public')

@section('title', __('pages.about'))

@section('content')
<style>
/* Performance: respect reduced motion (avoid heavy animations on slower devices) */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.001ms !important;
        scroll-behavior: auto !important;
    }
    .hero-section {
        animation: none !important;
        transform: none !important;
    }
}

/* Ancres pour navigation (Gouvernance, Histoire, Stratégie) - évite masquage par navbar sticky */
#gouvernance, #histoire, #strategie, #mot-introductif {
    scroll-margin-top: 120px;
}

/* Variables CSS pour la cohérence des couleurs */
:root {
    --primary-green: #22c55e;
    --dark-green: #16a34a;
    --blue: #3b82f6;
    --purple: #8b5cf6;
    --orange: #f59e0b;
    --cyan: #06b6d4;
    --red: #ef4444;
    --dark-blue: #1e3a8a;
    --light-bg: #f8fafc;
    --white: #ffffff;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-600: #4b5563;
    --gray-800: #1f2937;
}

/* Animations modernes et professionnelles */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-60px) rotateY(-15deg);
    }
    to {
        opacity: 1;
        transform: translateX(0) rotateY(0deg);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(60px) rotateY(15deg);
    }
    to {
        opacity: 1;
        transform: translateX(0) rotateY(0deg);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        filter: brightness(1);
    }
    50% {
        transform: scale(1.05);
        filter: brightness(1.1);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(2deg);
    }
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.3), 0 0 40px rgba(34, 197, 94, 0.1);
    }
    50% {
        box-shadow: 0 0 30px rgba(34, 197, 94, 0.6), 0 0 60px rgba(34, 197, 94, 0.2);
    }
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
        opacity: 0.8;
    }
    50% {
        opacity: 1;
    }
    100% {
        background-position: 200% 0;
        opacity: 0.8;
    }
}

/* Nouvelles animations modernes et dynamiques */
@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(60px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3) rotate(-10deg);
    }
    50% {
        opacity: 1;
        transform: scale(1.05) rotate(2deg);
    }
    70% {
        transform: scale(0.95) rotate(-1deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

@keyframes morphing {
    0%, 100% {
        border-radius: 20px;
        transform: rotate(0deg);
    }
    25% {
        border-radius: 30px;
        transform: rotate(1deg);
    }
    50% {
        border-radius: 15px;
        transform: rotate(-1deg);
    }
    75% {
        border-radius: 25px;
        transform: rotate(0.5deg);
    }
}

@keyframes gradientShift {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

@keyframes textReveal {
    0% {
        opacity: 0;
        transform: translateY(30px) rotateX(90deg);
    }
    100% {
        opacity: 1;
        transform: translateY(0) rotateX(0deg);
    }
}

@keyframes cardFlip {
    0% {
        transform: perspective(1000px) rotateY(0deg);
    }
    50% {
        transform: perspective(1000px) rotateY(5deg);
    }
    100% {
        transform: perspective(1000px) rotateY(0deg);
    }
}

@keyframes wave {
    0%, 100% {
        transform: translateX(0) translateY(0);
    }
    25% {
        transform: translateX(5px) translateY(-3px);
    }
    50% {
        transform: translateX(0) translateY(-5px);
    }
    75% {
        transform: translateX(-3px) translateY(-2px);
    }
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.5) rotate(180deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

@keyframes slideInDiagonal {
    from {
        opacity: 0;
        transform: translate(-50px, 50px) rotate(45deg);
    }
    to {
        opacity: 1;
        transform: translate(0, 0) rotate(0deg);
    }
}

/* Animations pour les chiffres clés */
@keyframes counterUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes numberPulse {
    0%, 100% {
        transform: scale(1);
        color: var(--primary-green);
    }
    50% {
        transform: scale(1.1);
        color: var(--dark-green);
    }
}

@keyframes iconBounce {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    25% {
        transform: translateY(-5px) rotate(5deg);
    }
    50% {
        transform: translateY(-10px) rotate(0deg);
    }
    75% {
        transform: translateY(-5px) rotate(-5deg);
    }
}

@keyframes cardGlow {
    0%, 100% {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    50% {
        box-shadow: 0 15px 40px rgba(34, 197, 94, 0.2);
    }
}

@keyframes textShimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Animations pour l'historique */
@keyframes timelineSlide {
    from {
        opacity: 0;
        transform: translateX(-100px) rotateY(-20deg);
    }
    to {
        opacity: 1;
        transform: translateX(0) rotateY(0deg);
    }
}

@keyframes yearPulse {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
    }
    50% {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 0 30px rgba(34, 197, 94, 0.6);
    }
}

@keyframes timelineGlow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        border-left-color: var(--primary-green);
    }
    50% {
        box-shadow: 0 0 30px rgba(34, 197, 94, 0.2);
        border-left-color: var(--dark-green);
    }
}

@keyframes contentReveal {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes timelineFloat {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes yearRotate {
    0% {
        transform: rotate(0deg) scale(1);
    }
    25% {
        transform: rotate(5deg) scale(1.05);
    }
    50% {
        transform: rotate(0deg) scale(1.1);
    }
    75% {
        transform: rotate(-5deg) scale(1.05);
    }
    100% {
        transform: rotate(0deg) scale(1);
    }
}

/* ============================================
   ANIMATIONS ULTRA-MODERNES 2025 POUR L'HISTORIQUE
   ============================================ */

/* Animation de particules flottantes */
@keyframes particleFloat {
    0%, 100% {
        transform: translateY(0px) translateX(0px) rotate(0deg);
        opacity: 0.3;
    }
    25% {
        transform: translateY(-20px) translateX(10px) rotate(90deg);
        opacity: 0.6;
    }
    50% {
        transform: translateY(-40px) translateX(-5px) rotate(180deg);
        opacity: 0.8;
    }
    75% {
        transform: translateY(-20px) translateX(15px) rotate(270deg);
        opacity: 0.6;
    }
}

/* Animation de connexion entre les éléments */
@keyframes connectionFlow {
    0% {
        stroke-dashoffset: 100;
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        stroke-dashoffset: 0;
        opacity: 0.8;
    }
}

/* Animation de morphing des années - Version subtile */
@keyframes yearMorph {
    0% {
        border-radius: 50%;
        transform: scale(1) rotate(0deg);
        background: linear-gradient(45deg, var(--primary-green), var(--dark-green));
    }
    50% {
        border-radius: 45%;
        transform: scale(1.02) rotate(5deg);
        background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
    }
    100% {
        border-radius: 50%;
        transform: scale(1) rotate(0deg);
        background: linear-gradient(45deg, var(--primary-green), var(--dark-green));
    }
}

/* Animation de texte holographique - Version subtile */
@keyframes holographicText {
    0% {
        background: linear-gradient(45deg, var(--primary-green), var(--dark-green));
        background-size: 200% 200%;
        background-position: 0% 50%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 0 10px rgba(34, 197, 94, 0.3);
    }
    50% {
        background-position: 100% 50%;
        text-shadow: 0 0 15px rgba(22, 163, 74, 0.4);
    }
    100% {
        background-position: 0% 50%;
        text-shadow: 0 0 10px rgba(34, 197, 94, 0.3);
    }
}

/* Animation de timeline avec effet de vague */
@keyframes timelineWave {
    0% {
        transform: translateX(-100%) scaleY(0);
        opacity: 0;
    }
    50% {
        transform: translateX(0%) scaleY(1.2);
        opacity: 0.8;
    }
    100% {
        transform: translateX(100%) scaleY(0);
        opacity: 0;
    }
}

/* Animation de glitch pour les années - Version subtile */
@keyframes yearGlitch {
    0%, 100% {
        transform: translateX(0) skew(0deg);
        filter: hue-rotate(0deg);
    }
    50% {
        transform: translateX(1px) skew(0.5deg);
        filter: hue-rotate(10deg);
    }
}

/* Animation de zoom avec effet de profondeur - Version subtile */
@keyframes depthZoom {
    0% {
        transform: perspective(1000px) translateZ(0px) scale(1);
        filter: blur(0px);
    }
    50% {
        transform: perspective(1000px) translateZ(10px) scale(1.01);
        filter: blur(0px);
    }
    100% {
        transform: perspective(1000px) translateZ(0px) scale(1);
        filter: blur(0px);
    }
}

/* Animation de pulsation avec effet de sonar - Version subtile */
@keyframes sonarPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.3);
        transform: scale(1);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
        transform: scale(1.01);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        transform: scale(1);
    }
}

/* Animation de rotation 3D - Version subtile */
@keyframes rotate3D {
    0% {
        transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg);
    }
    50% {
        transform: rotateX(5deg) rotateY(5deg) rotateZ(0deg);
    }
    100% {
        transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg);
    }
}

/* Animation de texte qui s'écrit */
@keyframes typewriter {
    0% {
        width: 0;
        border-right: 2px solid var(--primary-green);
    }
    50% {
        border-right: 2px solid transparent;
    }
    100% {
        width: 100%;
        border-right: 2px solid var(--primary-green);
    }
}

/* Animation de particules qui gravitent */
@keyframes orbit {
    0% {
        transform: rotate(0deg) translateX(30px) rotate(0deg);
    }
    100% {
        transform: rotate(360deg) translateX(30px) rotate(-360deg);
    }
}

@keyframes gradientShift {
    0% { 
        background-position: 0% 50%; 
        filter: hue-rotate(0deg);
    }
    50% { 
        background-position: 100% 50%; 
        filter: hue-rotate(10deg);
    }
    100% { 
        background-position: 0% 50%; 
        filter: hue-rotate(0deg);
    }
}

/* Nouvelles animations professionnelles */
@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.8) rotate(-5deg);
    }
    to {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3) translateY(-100px);
    }
    50% {
        opacity: 1;
        transform: scale(1.05) translateY(0);
    }
    70% {
        transform: scale(0.95);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes typewriter {
    from {
        width: 0;
    }
    to {
        width: 100%;
    }
}

@keyframes blink {
    0%, 50% {
        border-color: transparent;
    }
    51%, 100% {
        border-color: var(--primary-green);
    }
}

@keyframes morphing {
    0%, 100% {
        border-radius: 20px;
        transform: rotate(0deg);
    }
    25% {
        border-radius: 30px;
        transform: rotate(1deg);
    }
    50% {
        border-radius: 15px;
        transform: rotate(-1deg);
    }
    75% {
        border-radius: 25px;
        transform: rotate(0.5deg);
    }
}

/* Hero Section Ultra-Moderne */
.hero-section {
    background: linear-gradient(-45deg, var(--primary-green), var(--dark-green), #4a7c59, #22c55e);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    position: relative;
    overflow: hidden;
    min-height: 60vh;
    display: flex;
    align-items: center;
    color: white;
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

.hero-content {
    position: relative;
    z-index: 2;
    animation: fadeInUp 1s ease-out;
}

.hero-title {
    font-size: 3.5rem;
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

.hero-subtitle {
    font-size: 1.3rem;
    color: rgba(255,255,255,0.95);
    font-weight: 600;
    margin-bottom: 1rem;
    animation: slideInLeft 1s ease-out 0.3s both;
}

/* Section CSAR */
.csar-section {
    padding: 5rem 0;
    background: var(--white);
}

.csar-card {
    background: var(--white);
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 0 0 1px rgba(34, 197, 94, 0.1);
    border: 2px solid transparent;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    backdrop-filter: blur(10px);
    padding: 3rem;
}

.csar-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 30px 60px rgba(34, 197, 94, 0.15), 0 0 0 2px rgba(34, 197, 94, 0.3);
}

.csar-logo {
    width: 150px;
    height: 150px;
    background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);
    animation: pulse 3s ease-in-out infinite;
    padding: 15px;
    overflow: hidden;
}

.csar-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 50%;
}

.csar-logo i {
    font-size: 3rem;
    color: white;
}

/* Section Fondation */
.foundation-section {
    padding: 5rem 0;
    background: var(--light-bg);
}

.foundation-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2.5rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid var(--gray-200);
}

.foundation-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.foundation-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.foundation-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 1rem;
}

/* Section Objectifs */
.objectives-section {
    padding: 5rem 0;
    background: var(--white);
}

.objective-card {
    background: var(--white);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    border: 1px solid var(--gray-200);
}

.objective-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.objective-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.objective-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.75rem;
}

/* Section Chiffres Clés */
.stats-section {
    padding: 5rem 0;
    background: var(--primary-green);
    position: relative;
    overflow: hidden;
}

.stats-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.stats-title {
    color: white;
    font-size: 2.5rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 1rem;
    position: relative;
    z-index: 2;
}

.stats-subtitle {
    color: rgba(255,255,255,0.9);
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
    z-index: 2;
}

.stats-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    z-index: 2;
}

.stats-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 50px rgba(0,0,0,0.2);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 900;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
    animation: pulse 2s ease-in-out infinite;
}

.stats-label {
    font-size: 1rem;
    color: var(--gray-600);
    font-weight: 600;
}

/* Section Historique */
.history-section {
    padding: 5rem 0;
    background: var(--white);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, var(--primary-green), var(--blue), var(--purple), var(--orange), var(--cyan));
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    background: var(--white);
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid var(--gray-200);
    transition: all 0.3s ease;
}

.timeline-item:hover {
    transform: translateX(10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -37px;
    top: 2rem;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 4px solid var(--white);
    box-shadow: 0 0 0 3px var(--primary-green);
}

.timeline-year {
    display: inline-block;
    background: var(--primary-green);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 700;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.timeline-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.75rem;
}

/* Section Messages */
.message-section {
    padding: 5rem 0;
    background: var(--dark-blue);
    color: white;
}

.message-card {
    background: var(--white);
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    color: var(--gray-800);
    height: 100%;
}

.message-image {
    width: 200px;
    height: 200px;
    border-radius: 16px;
    object-fit: cover;
    border: 4px solid var(--primary-green);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.message-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.message-title {
    font-size: 1rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 1.5rem;
}

.message-quote {
    background: linear-gradient(135deg, var(--primary-green), var(--dark-green));
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    font-style: italic;
    font-size: 1.1rem;
    margin: 1.5rem 0;
    position: relative;
}

.message-quote::before {
    content: '"';
    font-size: 4rem;
    position: absolute;
    top: -10px;
    left: 15px;
    opacity: 0.3;
}

.message-signature {
    text-align: right;
    margin-top: 2rem;
    font-weight: 700;
    color: var(--gray-800);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .csar-card {
        padding: 2rem;
    }
    
    .foundation-card,
    .objective-card,
    .stats-card {
        margin-bottom: 1.5rem;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-item::before {
        left: -27px;
    }
    
    .message-image {
        width: 150px;
        height: 150px;
    }
    
    .message-card {
        padding: 2rem;
        margin-bottom: 2rem;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .stats-number {
        font-size: 2rem;
    }
    
    .foundation-icon,
    .objective-icon,
    .stats-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}

/* Animations d'entrée */
.fade-in {
    animation: fadeInUp 0.8s ease-out;
}

/* Classes d'animation professionnelles */
.fade-in {
    animation: fadeInUp 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.slide-in-left {
    animation: slideInLeft 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.slide-in-right {
    animation: slideInRight 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.scale-in {
    animation: scaleIn 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.slide-up-fade {
    animation: slideUpFade 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.bounce-in {
    animation: bounceIn 1.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

.float-animation {
    animation: float 3s ease-in-out infinite;
}

.pulse-animation {
    animation: pulse 2s ease-in-out infinite;
}

.glow-animation {
    animation: glow 2s ease-in-out infinite;
}

.shimmer-effect {
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

.morphing-shape {
    animation: morphing 4s ease-in-out infinite;
}

/* Effets de transition au survol */
.hover-lift {
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.hover-lift:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.hover-glow {
    transition: all 0.3s ease;
}

.hover-glow:hover {
    box-shadow: 0 0 30px rgba(34, 197, 94, 0.4);
    transform: scale(1.05);
}

.hover-rotate {
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.hover-rotate:hover {
    transform: rotate(2deg) scale(1.02);
}

/* Délais d'animation en cascade */
.animate-delay-1 { animation-delay: 0.1s; }
.animate-delay-2 { animation-delay: 0.2s; }
.animate-delay-3 { animation-delay: 0.3s; }
.animate-delay-4 { animation-delay: 0.4s; }
.animate-delay-5 { animation-delay: 0.5s; }
.animate-delay-6 { animation-delay: 0.6s; }

/* Nouvelles classes d'animation modernes - OPTIMISÉES pour affichage immédiat */
.slide-up-fade {
    /* Contenu visible immédiatement, animation optionnelle */
    opacity: 1 !important;
    animation: slideUpFade 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) ease-out;
    will-change: transform;
}

.bounce-in {
    /* Contenu visible immédiatement, animation optionnelle */
    opacity: 1 !important;
    animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) ease-out;
    will-change: transform;
}

.morphing-shape {
    animation: morphing 4s ease-in-out infinite;
}

.gradient-shift {
    background: linear-gradient(-45deg, var(--primary-green), var(--dark-green), var(--blue), var(--purple));
    background-size: 400% 400%;
    animation: gradientShift 6s ease infinite;
}

.text-reveal {
    /* Contenu visible immédiatement, animation optionnelle */
    opacity: 1 !important;
    animation: textReveal 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) ease-out;
    will-change: transform;
}

.card-flip {
    /* Contenu visible immédiatement, animation optionnelle */
    opacity: 1 !important;
    animation: cardFlip 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) ease-out;
    will-change: transform;
}

.wave-effect {
    animation: wave 3s ease-in-out infinite;
}

.zoom-in {
    /* Contenu visible immédiatement, animation optionnelle */
    opacity: 1 !important;
    animation: zoomIn 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) ease-out;
    will-change: transform;
}

.slide-diagonal {
    /* Contenu visible immédiatement, animation optionnelle */
    opacity: 1 !important;
    animation: slideInDiagonal 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) ease-out;
    will-change: transform;
}

/* Effets de survol améliorés */
.hover-lift {
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.hover-lift:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.hover-glow {
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.hover-glow:hover {
    box-shadow: 0 0 30px rgba(34, 197, 94, 0.4);
    transform: scale(1.05);
}

.hover-rotate {
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.hover-rotate:hover {
    transform: rotate(5deg) scale(1.1);
}

/* Effets de texte dynamiques */
.text-gradient {
    background: linear-gradient(45deg, var(--primary-green), var(--blue), var(--purple));
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradientShift 3s ease infinite;
}

.text-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Classes pour les chiffres clés - OPTIMISÉES */
.counter-up {
    /* Contenu visible immédiatement */
    opacity: 1 !important;
    animation: counterUp 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) ease-out;
    will-change: transform;
}

/* Tous les éléments sont maintenant visibles immédiatement */
.counter-up,
.slide-up-fade,
.bounce-in,
.text-reveal,
.card-flip,
.zoom-in,
.slide-diagonal,
.timeline-slide,
.content-reveal {
    /* Rendu visible immédiatement, animations comme effet visuel */
    animation-fill-mode: both;
    opacity: 1 !important;
}

/* Si JavaScript est désactivé, rendre tous les éléments visibles */
.no-js .counter-up,
.no-js .slide-up-fade,
.no-js .bounce-in,
.no-js .text-reveal,
.no-js .card-flip,
.no-js .zoom-in,
.no-js .slide-diagonal,
.no-js .timeline-slide,
.no-js .content-reveal {
    opacity: 1 !important;
    transform: none !important;
}

.number-pulse {
    animation: numberPulse 2s ease-in-out infinite;
    font-weight: 800;
    font-size: 2.5rem;
}

.icon-bounce {
    animation: iconBounce 3s ease-in-out infinite;
}

.card-glow {
    animation: cardGlow 4s ease-in-out infinite;
}

.text-shimmer-effect {
    background: linear-gradient(90deg, var(--primary-green), var(--dark-green), var(--primary-green));
    background-size: 200% 100%;
    animation: textShimmer 3s ease-in-out infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Effet chrono pour les chiffres */
.chrono-counter {
    font-weight: 800;
    font-size: 2.5rem;
    color: var(--primary-green);
    transition: all 0.3s ease;
}

.chrono-counter:hover {
    transform: scale(1.1);
    color: var(--dark-green);
    text-shadow: 0 0 20px rgba(34, 197, 94, 0.5);
}

/* Classes pour l'historique */
.timeline-slide {
    animation: timelineSlide 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    opacity: 0;
}

.year-pulse {
    animation: yearPulse 3s ease-in-out infinite;
}

.timeline-glow {
    animation: timelineGlow 4s ease-in-out infinite;
}

.content-reveal {
    animation: contentReveal 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    opacity: 0;
}

.timeline-float {
    animation: timelineFloat 3s ease-in-out infinite;
}

.year-rotate {
    animation: yearRotate 4s ease-in-out infinite;
}

/* Effets de survol pour l'historique */
.timeline-item:hover {
    transform: translateX(10px) scale(1.02);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.timeline-year:hover {
    transform: scale(1.2) rotate(10deg);
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* ============================================
   CLASSES ULTRA-MODERNES 2025 POUR L'HISTORIQUE
   ============================================ */

/* Classe pour les particules flottantes */
.particle-float {
    animation: particleFloat 6s ease-in-out infinite;
}

/* Classe pour l'effet de connexion */
.connection-flow {
    animation: connectionFlow 3s ease-in-out infinite;
}

/* Classe pour le morphing des années - Version subtile */
.year-morph {
    animation: yearMorph 12s ease-in-out infinite;
}

/* Classe pour le texte holographique - Version subtile */
.holographic-text {
    animation: holographicText 8s ease-in-out infinite;
    background: linear-gradient(45deg, var(--primary-green), var(--dark-green));
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Classe pour l'effet de vague sur la timeline */
.timeline-wave {
    position: relative;
    overflow: hidden;
}

.timeline-wave::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.3), transparent);
    animation: timelineWave 3s ease-in-out infinite;
}

/* Classe pour l'effet de glitch - Version subtile */
.year-glitch {
    animation: yearGlitch 6s ease-in-out infinite;
    position: relative;
}

.year-glitch::before,
.year-glitch::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: inherit;
    color: inherit;
    opacity: 0.8;
}

.year-glitch::before {
    animation: yearGlitch 2s ease-in-out infinite;
    z-index: -1;
    color: #ff006e;
    transform: translateX(-2px);
}

.year-glitch::after {
    animation: yearGlitch 2s ease-in-out infinite reverse;
    z-index: -2;
    color: #3a86ff;
    transform: translateX(2px);
}

/* Classe pour l'effet de profondeur - Version subtile */
.depth-zoom {
    animation: depthZoom 8s ease-in-out infinite;
    transform-style: preserve-3d;
}

/* Classe pour l'effet de sonar - Version subtile */
.sonar-pulse {
    animation: sonarPulse 4s ease-in-out infinite;
}

/* Classe pour la rotation 3D - Version subtile */
.rotate-3d {
    animation: rotate3D 10s ease-in-out infinite;
    transform-style: preserve-3d;
}

/* Classe pour l'effet de machine à écrire */
.typewriter-effect {
    overflow: hidden;
    white-space: nowrap;
    animation: typewriter 3s steps(40, end) infinite;
    border-right: 2px solid var(--primary-green);
}

/* Classe pour les particules en orbite */
.orbit-particles {
    position: relative;
}

.orbit-particles::before,
.orbit-particles::after {
    content: '';
    position: absolute;
    width: 4px;
    height: 4px;
    background: var(--primary-green);
    border-radius: 50%;
    animation: orbit 4s linear infinite;
}

.orbit-particles::before {
    animation-delay: 0s;
}

.orbit-particles::after {
    animation-delay: 2s;
    background: var(--blue);
}

/* Classe pour l'effet de néon */
.neon-glow {
    text-shadow: 
        0 0 5px currentColor,
        0 0 10px currentColor,
        0 0 15px currentColor,
        0 0 20px var(--primary-green);
    animation: glow 2s ease-in-out infinite alternate;
}

/* Classe pour l'effet de liquide */
.liquid-effect {
    background: linear-gradient(45deg, var(--primary-green), var(--blue), var(--purple));
    background-size: 300% 300%;
    animation: gradientShift 3s ease infinite;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Classe pour l'effet de métamorphose */
.metamorphosis {
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.metamorphosis:hover {
    transform: scale(1.1) rotateY(180deg);
    background: linear-gradient(45deg, var(--primary-green), var(--blue));
    border-radius: 20px;
}

/* Classe pour l'effet de téléportation */
.teleport-effect {
    position: relative;
    overflow: hidden;
}

.teleport-effect::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    animation: timelineWave 2s ease-in-out infinite;
}

/* Classe pour l'effet de distorsion */
.distortion-effect {
    animation: yearGlitch 3s ease-in-out infinite;
    filter: contrast(1.2) brightness(1.1);
}

/* Classe pour l'effet de cristal */
.crystal-effect {
    background: linear-gradient(45deg, 
        rgba(34, 197, 94, 0.1), 
        rgba(59, 130, 246, 0.1), 
        rgba(139, 92, 246, 0.1)
    );
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}
</style>

<!-- Hero Section -->
<section class="hero-section" data-aos="fade-down" data-aos-duration="600">
    <div class="container">
        <div class="hero-content text-center">
            <h1 class="hero-title text-reveal text-gradient">{{ __('about.hero_title') }}</h1>
            <p class="hero-subtitle slide-up-fade animate-delay-1">{{ __('about.hero_subtitle') }}</p>
        </div>
    </div>
</section>

<!-- Section CSAR -->
<section class="csar-section" data-aos="fade-up" data-aos-duration="600">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="csar-card slide-diagonal hover-lift morphing-shape">
                    <h2 class="mb-4 text-reveal animate-delay-1" style="font-size: 2rem; font-weight: 800; color: var(--gray-800);">
                        {{ __('about.csar_title') }}
                    </h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3 slide-up-fade animate-delay-2">
                                <div class="me-3 pulse-animation bounce-in" style="width: 12px; height: 12px; background: var(--primary-green); border-radius: 50%; margin-top: 8px;"></div>
                                <p class="mb-0">{{ __('about.csar_intro') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3 slide-up-fade animate-delay-3">
                                <div class="me-3 pulse-animation bounce-in" style="width: 12px; height: 12px; background: var(--blue); border-radius: 50%; margin-top: 8px;"></div>
                                <p class="mb-0">{{ __('about.csar_mission') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="csar-logo bounce-in hover-glow wave-effect">
                    <img src="{{ asset('images/csar-logo.png') }}" alt="Logo CSAR" style="height: 120px; width: auto; max-width: 120px;" onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}';">
                </div>
                <h4 class="text-reveal animate-delay-4" style="color: var(--gray-800); font-weight: 700;">CSAR</h4>
                <p class="slide-up-fade animate-delay-5" style="color: var(--gray-600);">{{ __('about.csar_full_name') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Fondation (Gouvernance : mission, vision, valeurs) -->
<section id="gouvernance" class="foundation-section" data-aos="fade-up" data-aos-duration="600">
    <div class="container">
        <h2 class="text-center mb-2 text-reveal text-gradient" style="font-size: 2.5rem; font-weight: 800; color: var(--gray-800);">
            {{ __('about.foundation_title') }}
        </h2>
        <p class="text-center mb-5 slide-up-fade animate-delay-1" style="color: var(--gray-600); font-size: 1.1rem;">
            {{ __('about.foundation_subtitle') }}
        </p>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="foundation-card slide-diagonal hover-lift card-flip animate-delay-2">
                    <div class="foundation-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--primary-green), var(--dark-green));">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 class="foundation-title text-reveal">{{ __('about.foundation_mission') }}</h3>
                    <p class="slide-up-fade">{{ __('about.foundation_mission_text') }}</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="foundation-card zoom-in hover-lift card-flip animate-delay-3">
                    <div class="foundation-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--blue), #1e40af);">
                        <i class="fas fa-globe-africa"></i>
                    </div>
                    <h3 class="foundation-title text-reveal">{{ __('about.foundation_vision') }}</h3>
                    <p class="slide-up-fade">{{ __('about.foundation_vision_text') }}</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="foundation-card slide-diagonal hover-lift card-flip animate-delay-4">
                    <div class="foundation-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--purple), #7c3aed);">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="foundation-title text-reveal">{{ __('about.foundation_values') }}</h3>
                    <ul class="list-unstyled text-start">
                        <li class="mb-2 slide-up-fade animate-delay-5"><i class="fas fa-check text-success me-2 bounce-in"></i>{{ __('about.foundation_value_equity') }}</li>
                        <li class="mb-2 slide-up-fade animate-delay-6"><i class="fas fa-check text-success me-2 bounce-in"></i>{{ __('about.foundation_value_integrity') }}</li>
                        <li class="mb-2 slide-up-fade animate-delay-1"><i class="fas fa-check text-success me-2 bounce-in"></i>{{ __('about.foundation_value_transparency') }}</li>
                        <li class="mb-2 slide-up-fade animate-delay-2"><i class="fas fa-check text-success me-2 bounce-in"></i>{{ __('about.foundation_value_commitment') }}</li>
                        <li class="mb-2 slide-up-fade animate-delay-3"><i class="fas fa-check text-success me-2 bounce-in"></i>{{ __('about.foundation_value_innovation') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Objectifs Stratégiques (Stratégie) -->
<section id="strategie" class="objectives-section" data-aos="fade-up" data-aos-duration="600">
    <div class="container">
        <h2 class="text-center mb-2 text-reveal text-gradient" style="font-size: 2.5rem; font-weight: 800; color: var(--gray-800);">
            {{ __('about.objectives_title') }}
        </h2>
        <p class="text-center mb-5 slide-up-fade animate-delay-1" style="color: var(--gray-600); font-size: 1.1rem;">
            {{ __('about.objectives_subtitle') }}
        </p>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="objective-card bounce-in hover-lift card-flip animate-delay-2">
                    <div class="objective-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--primary-green), var(--dark-green));">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 class="objective-title text-reveal">{{ __('about.obj_storage') }}</h4>
                    <p class="slide-up-fade">{{ __('about.obj_storage_text') }}</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="objective-card zoom-in hover-lift card-flip animate-delay-3">
                    <div class="objective-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--blue), #1e40af);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h4 class="objective-title text-reveal">{{ __('about.obj_innovation') }}</h4>
                    <p class="slide-up-fade">{{ __('about.obj_innovation_text') }}</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="objective-card slide-diagonal hover-lift card-flip animate-delay-4">
                    <div class="objective-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--orange), #ea580c);">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4 class="objective-title text-reveal">{{ __('about.obj_alert') }}</h4>
                    <p class="slide-up-fade">{{ __('about.obj_alert_text') }}</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="objective-card zoom-in hover-lift card-flip animate-delay-5">
                    <div class="objective-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--purple), #7c3aed);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="objective-title text-reveal">{{ __('about.obj_community') }}</h4>
                    <p class="slide-up-fade">{{ __('about.obj_community_text') }}</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="objective-card slide-diagonal hover-lift card-flip animate-delay-6">
                    <div class="objective-icon glow-animation morphing-shape" style="background: linear-gradient(135deg, var(--cyan), #0891b2);">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h4 class="objective-title text-reveal">{{ __('about.obj_governance') }}</h4>
                    <p class="slide-up-fade">{{ __('about.obj_governance_text') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Historique -->
<section id="histoire" class="history-section" data-aos="fade-up" data-aos-duration="600">
    <div class="container">
        <h2 class="text-center mb-2 text-reveal text-gradient" style="font-size: 2.5rem; font-weight: 800; color: var(--gray-800);">
            {{ __('about.history_title') }}
        </h2>
        <p class="text-center mb-5 slide-up-fade animate-delay-1" style="color: var(--gray-600); font-size: 1.1rem;">
            {{ __('about.history_subtitle') }}
        </p>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline">
                    <!-- 2024 - CSAR -->
                    <div class="timeline-item timeline-slide timeline-glow timeline-float animate-delay-3">
                        <div class="timeline-year year-pulse year-morph depth-zoom" 
                             style="background: var(--blue);">2024</div>
                        <h4 class="timeline-title content-reveal animate-delay-4">{{ __('about.history_2024') }}</h4>
                        <p class="content-reveal animate-delay-5">{{ __('about.history_2024_text') }}</p>
                    </div>
                    
                    <!-- 1984 - Commissariat à la Sécurité Alimentaire (CSA) -->
                    <div class="timeline-item timeline-slide timeline-glow animate-delay-4">
                        <div class="timeline-year year-pulse year-morph sonar-pulse" 
                             style="background: var(--purple);">1984</div>
                        <h4 class="timeline-title content-reveal animate-delay-5">{{ __('about.history_1984') }}</h4>
                        <p class="content-reveal animate-delay-6">{{ __('about.history_1984_text') }}</p>
                    </div>
                    
                    <!-- 1976 - Commissariat à l'Aide Alimentaire -->
                    <div class="timeline-item timeline-slide timeline-glow timeline-float animate-delay-5">
                        <div class="timeline-year year-pulse year-morph rotate-3d" 
                             style="background: var(--orange);">1976</div>
                        <h4 class="timeline-title content-reveal animate-delay-6">{{ __('about.history_1976') }}</h4>
                        <p class="content-reveal animate-delay-1">{{ __('about.history_1976_text') }}</p>
                    </div>
                    
                    <!-- 1973-1974 - Commissariat aux Sinistrés de la Sécheresse -->
                    <div class="timeline-item timeline-slide timeline-glow animate-delay-6">
                        <div class="timeline-year year-pulse year-morph" 
                             style="background: var(--cyan);">1973-1974</div>
                        <h4 class="timeline-title content-reveal animate-delay-1">{{ __('about.history_1973') }}</h4>
                        <p class="content-reveal animate-delay-2">{{ __('about.history_1973_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Message de la Direction Générale -->
<section id="mot-introductif" class="message-section" data-aos="fade-up" data-aos-duration="600">
    <div class="container">
        <h2 class="text-center mb-2" style="font-size: 2.5rem; font-weight: 800; color: white;">
            {{ __('about.message_dg_title') }}
        </h2>
        <p class="text-center mb-5" style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">
            {{ __('about.message_dg_subtitle') }}
        </p>
        
        <div class="row align-items-center">
            <div class="col-lg-4 text-center">
                <img src="{{ asset('images/dg.jpg') }}" alt="{{ __('about.dg_name') }}" class="message-image">
                <h4 class="message-name">{{ __('about.dg_name') }}</h4>
                <p class="message-title">{{ __('about.dg_title') }}</p>
            </div>
            <div class="col-lg-8">
                <div class="message-card slide-in-right">
                    <h5 style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1.5rem;">{{ __('about.dg_intro') }}</h5>
                    
                    <p>{{ __('about.dg_para1') }}</p>
                    
                    <p>{{ __('about.dg_para2') }}</p>
                    
                    <p>{{ __('about.dg_para3') }}</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3" style="width: 30px; height: 30px; background: var(--primary-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">1</div>
                                <p class="mb-0">{{ __('about.dg_priority1') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3" style="width: 30px; height: 30px; background: var(--primary-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">2</div>
                                <p class="mb-0">{{ __('about.dg_priority2') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3" style="width: 30px; height: 30px; background: var(--primary-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">3</div>
                                <p class="mb-0">{{ __('about.dg_priority3') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3" style="width: 30px; height: 30px; background: var(--primary-green); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">4</div>
                                <p class="mb-0">{{ __('about.dg_priority4') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-quote">
                        "{{ __('about.dg_quote') }}"
                    </div>
                    
                    <div class="message-signature">
                        <div>{{ __('about.dg_signature') }}</div>
                        <div style="font-size: 0.9rem; color: var(--gray-600);">{{ __('about.dg_title') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Message du Ministère -->
<section class="message-section" data-aos="fade-up" data-aos-duration="600" style="background: var(--dark-blue);">
    <div class="container">
        <h2 class="text-center mb-2" style="font-size: 2.5rem; font-weight: 800; color: white;">
            {{ __('about.message_minister_title') }}
        </h2>
        <p class="text-center mb-5" style="color: rgba(255,255,255,0.9); font-size: 1.1rem;">
            {{ __('about.message_minister_subtitle') }}
        </p>
        
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="message-card slide-in-left">
                    <h5 style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1.5rem;">{{ __('about.dg_intro') }}</h5>
                    
                    <p>{{ __('about.minister_para1') }}</p>
                    
                    <p>{{ __('about.minister_para2') }}</p>
                    
                    <p>{{ __('about.minister_para3') }}</p>
                    
                    <div class="message-quote" style="background: linear-gradient(135deg, var(--gray-800), var(--gray-600));">
                        "{{ __('about.minister_quote') }}"
                    </div>
                    
                    <div class="message-signature">
                        <div>{{ __('about.minister_signature') }}</div>
                        <div style="font-size: 0.9rem; color: var(--gray-600);">{{ __('about.minister_name_short') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <img src="{{ asset('images/ministre.JPG') }}" alt="{{ __('about.minister_name') }}" class="message-image">
                <h4 class="message-name">{{ __('about.minister_name') }}</h4>
                <p class="message-title">{{ __('about.minister_title') }}</p>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// PERF: ensure content is visible immediately (avoid waiting for animations/intersection observers)
function csarMakeContentVisible() {
    const selectors = [
        '.counter-up',
        '.slide-up-fade',
        '.bounce-in',
        '.text-reveal',
        '.card-flip',
        '.zoom-in',
        '.slide-diagonal',
        '.timeline-slide',
        '.content-reveal',
        '.fade-in',
        '.slide-in-left',
        '.slide-in-right'
    ];
    document.querySelectorAll(selectors.join(', ')).forEach((el) => {
        el.style.opacity = '1';
        el.style.transform = 'none';
        el.classList.add('animate-in');
    });
}

// PERF: run heavy effects only when browser is idle (and skip for reduced motion / low-end)
function csarRunWhenIdle(fn, timeout = 1500) {
    if ('requestIdleCallback' in window) {
        requestIdleCallback(fn, { timeout });
    } else {
        setTimeout(fn, Math.min(timeout, 500));
    }
}

function csarShouldReduceMotion() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// Animation d'entrée des éléments avec effet cascade
document.addEventListener('DOMContentLoaded', function() {
    // Always show content immediately; animations are nice-to-have, not blocking
    csarMakeContentVisible();

    // If user prefers reduced motion, skip the rest of heavy animation wiring
    if (csarShouldReduceMotion()) return;

    const elements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) translateX(0)';
                    entry.target.classList.add('animate-in');
                }, index * 30); // Réduit de 100ms à 30ms pour des animations plus rapides
            }
        });
    }, {
        threshold: 0.05 // Réduit de 0.1 à 0.05 pour déclencher plus tôt
    });
    
    elements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
});

// Animation des compteurs
function animateCounters() {
    const counters = document.querySelectorAll('.stats-number');
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/,/g, ''));
        const increment = target / 100;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current).toLocaleString();
            }
        }, 20);
    });
}

// Observer pour déclencher l'animation des compteurs
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            statsObserver.unobserve(entry.target);
        }
    });
});

const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    statsObserver.observe(statsSection);
}

// Effet de parallaxe sur le hero
window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero-section');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.3}px)`;
    }
});

// Effet de glow sur les cartes
document.querySelectorAll('.stats-card, .foundation-card, .objective-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.boxShadow = '0 25px 50px rgba(34, 197, 94, 0.2)';
        this.style.borderColor = 'var(--primary-green)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
        this.style.borderColor = 'var(--gray-200)';
    });
});
</script>
@endpush
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // PERF: if reduced motion, avoid intervals and expensive effects
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        csarMakeContentVisible();
        return;
    }

    // Effet chrono pour les chiffres clés
    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16); // 60 FPS
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            // Formatage du nombre
            if (target >= 1000) {
                element.textContent = Math.floor(current).toLocaleString();
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    }
    
    // Observer pour déclencher l'animation quand la section est visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counters = entry.target.querySelectorAll('.chrono-counter[data-target]');
                counters.forEach((counter, index) => {
                    const target = parseInt(counter.getAttribute('data-target'));
                    const delay = parseInt(counter.closest('.stats-card').getAttribute('data-delay')) || 0;
                    
                    setTimeout(() => {
                        animateCounter(counter, target, 2000);
                    }, delay);
                });
                
                // Arrêter d'observer après la première animation
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    // Observer la section des statistiques
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
    
    // Effet de pulsation continue pour les chiffres
    // PERF: avoid perpetual intervals; apply pulse once after counters animate
    setTimeout(() => {
        const counters = document.querySelectorAll('.chrono-counter');
        counters.forEach(counter => {
            counter.style.animation = 'numberPulse 2s ease-in-out infinite';
        });
    }, 2200);
    
    // Fallback rapide : s'assurer que tous les éléments sont visibles après 100ms (au cas où)
    setTimeout(() => {
        const hiddenElements = document.querySelectorAll('.counter-up, .slide-up-fade, .bounce-in, .text-reveal, .card-flip, .zoom-in, .slide-diagonal, .timeline-slide, .content-reveal');
        hiddenElements.forEach(element => {
            if (getComputedStyle(element).opacity === '0') {
                element.style.opacity = '1';
                element.style.transform = 'none';
            }
        });
    }, 100); // Réduit de 5000ms à 100ms car le CSS rend déjà tout visible
});

// ============================================
// EFFETS INTERACTIFS ULTRA-MODERNES 2025
// ============================================

// Effet de particules dynamiques
function createParticles() {
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, index) => {
        for (let i = 0; i < 3; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.cssText = `
                position: absolute;
                width: 4px;
                height: 4px;
                background: linear-gradient(45deg, #22c55e, #3b82f6);
                border-radius: 50%;
                pointer-events: none;
                animation: particleFloat ${6 + i}s ease-in-out infinite;
                animation-delay: ${i * 2}s;
                left: ${20 + i * 30}%;
                top: ${30 + i * 20}%;
                z-index: 1;
            `;
            item.appendChild(particle);
        }
    });
}

// Effet de connexion entre les éléments
function createConnections() {
    const timelineYears = document.querySelectorAll('.timeline-year');
    timelineYears.forEach((year, index) => {
        if (index < timelineYears.length - 1) {
            const connection = document.createElement('div');
            connection.className = 'connection-line';
            connection.style.cssText = `
                position: absolute;
                width: 2px;
                height: 60px;
                background: linear-gradient(180deg, #22c55e, #3b82f6);
                left: 50%;
                top: 100%;
                transform: translateX(-50%);
                z-index: 0;
                animation: connectionFlow 3s ease-in-out infinite;
                animation-delay: ${index * 0.5}s;
            `;
            year.parentElement.appendChild(connection);
        }
    });
}

// Effet de glitch interactif
function addGlitchEffect() {
    const glitchElements = document.querySelectorAll('.year-glitch');
    glitchElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            element.style.animation = 'yearGlitch 0.5s ease-in-out infinite';
        });
        
        element.addEventListener('mouseleave', () => {
            element.style.animation = 'yearGlitch 2s ease-in-out infinite';
        });
    });
}

// Effet de morphing au survol
function addMorphingEffect() {
    const morphElements = document.querySelectorAll('.metamorphosis');
    morphElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            element.style.transform = 'scale(1.1) rotateY(180deg)';
            element.style.background = 'linear-gradient(45deg, #22c55e, #3b82f6)';
            element.style.borderRadius = '20px';
        });
        
        element.addEventListener('mouseleave', () => {
            element.style.transform = 'scale(1) rotateY(0deg)';
            element.style.background = '';
            element.style.borderRadius = '';
        });
    });
}

// Effet de téléportation
function addTeleportEffect() {
    const teleportElements = document.querySelectorAll('.teleport-effect');
    teleportElements.forEach(element => {
        element.addEventListener('click', () => {
            element.style.transform = 'scale(0.8) rotateY(360deg)';
            element.style.opacity = '0.5';
            
            setTimeout(() => {
                element.style.transform = 'scale(1) rotateY(0deg)';
                element.style.opacity = '1';
            }, 300);
        });
    });
}

// Effet de distorsion au clic
function addDistortionEffect() {
    const distortionElements = document.querySelectorAll('.distortion-effect');
    distortionElements.forEach(element => {
        element.addEventListener('click', () => {
            element.style.filter = 'contrast(2) brightness(1.5) hue-rotate(180deg)';
            
            setTimeout(() => {
                element.style.filter = 'contrast(1.2) brightness(1.1)';
            }, 1000);
        });
    });
}

// Effet de cristal au survol
function addCrystalEffect() {
    const crystalElements = document.querySelectorAll('.crystal-effect');
    crystalElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            element.style.backdropFilter = 'blur(20px)';
            element.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.4)';
        });
        
        element.addEventListener('mouseleave', () => {
            element.style.backdropFilter = 'blur(10px)';
            element.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
        });
    });
}

// Effet de sonar au clic
function addSonarEffect() {
    const sonarElements = document.querySelectorAll('.sonar-pulse');
    sonarElements.forEach(element => {
        element.addEventListener('click', () => {
            element.style.animation = 'sonarPulse 0.5s ease-in-out';
            
            setTimeout(() => {
                element.style.animation = 'sonarPulse 2s ease-in-out infinite';
            }, 500);
        });
    });
}

// Effet de rotation 3D au survol
function add3DRotationEffect() {
    const rotate3DElements = document.querySelectorAll('.rotate-3d');
    rotate3DElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            element.style.animation = 'rotate3D 1s ease-in-out';
        });
        
        element.addEventListener('mouseleave', () => {
            element.style.animation = 'rotate3D 6s ease-in-out infinite';
        });
    });
}

// Effet de profondeur au survol
function addDepthEffect() {
    const depthElements = document.querySelectorAll('.depth-zoom');
    depthElements.forEach(element => {
        element.addEventListener('mouseenter', () => {
            element.style.transform = 'perspective(1000px) translateZ(100px) scale(1.1)';
            element.style.filter = 'blur(2px)';
        });
        
        element.addEventListener('mouseleave', () => {
            element.style.transform = 'perspective(1000px) translateZ(0px) scale(1)';
            element.style.filter = 'blur(0px)';
        });
    });
}

// Initialisation des effets
document.addEventListener('DOMContentLoaded', function() {
    // PERF: defer ultra effects to idle time so first render is fast
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    csarRunWhenIdle(() => {
        try {
            createParticles();
            createConnections();
            addGlitchEffect();
            addMorphingEffect();
            addTeleportEffect();
            addDistortionEffect();
            addCrystalEffect();
            addSonarEffect();
            add3DRotationEffect();
            addDepthEffect();
        } catch (e) {
            // Never block the page if an effect fails
            console.warn('CSAR effects init failed', e);
        }
    }, 2000);
    
    console.log('🎉 Effets ultra-modernes 2025 initialisés !');
});
</script>