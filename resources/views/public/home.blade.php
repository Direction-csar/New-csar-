@extends('layouts.public')

@section('title', 'Accueil - CSAR')

@section('content')
<style>
/* Actualités Grid Responsive */
.news-grid-2x2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 3rem;
}

@media (max-width: 992px) {
    .news-grid-2x2 {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

/* Hover effects pour les cards d'actualités */
.news-card-ultra:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
}

.news-card-ultra:hover .news-image-hover {
    transform: scale(1.05);
}

.news-card-ultra:hover a[href] {
    background: #d4a574;
    color: white;
}

/* Hover effects pour les cards de rapports SIM */
.sim-report-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 70px rgba(5, 150, 105, 0.25) !important;
}

.sim-report-card:hover .news-image-hover {
    transform: scale(1.08);
}

.sim-report-card:hover h3 {
    color: #059669 !important;
}

.sim-report-card:hover a[href] {
    background: #059669;
    color: white;
}

/* Hover effects pour les cartes de publications */
.publication-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(245, 158, 11, 0.2) !important;
}

.publication-card:hover img {
    transform: scale(1.05);
}

.publication-card:hover h3 {
    color: #f59e0b !important;
}

.publication-card:hover a[href] {
    background: #d97706 !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(217, 119, 6, 0.4);
}

/* ============================================
   ANIMATIONS PROFESSIONNELLES AVEC TRANSITIONS DYNAMIQUES
   ============================================ */

/* ============================================
   SECTION LE MINISTRE - ANIMATIONS PROFESSIONNELLES
   ============================================ */

/* Section principale */
.minister-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
    padding: 6rem 0;
    position: relative;
    overflow: hidden;
}

/* Particules flottantes en arrière-plan */
.minister-section::before {
    content: '';
    position: absolute;
    top: 10%;
    left: 5%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(34, 197, 94, 0.1), transparent);
    border-radius: 50%;
    filter: blur(60px);
    animation: ministerFloat 20s ease-in-out infinite;
}

.minister-section::after {
    content: '';
    position: absolute;
    bottom: 10%;
    right: 5%;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.1), transparent);
    border-radius: 50%;
    filter: blur(50px);
    animation: ministerFloat 25s ease-in-out infinite reverse;
}

/* Animation de flottement */
@keyframes ministerFloat {
    0%, 100% {
        transform: translateY(0) translateX(0) rotate(0deg);
        opacity: 0.3;
    }
    25% {
        transform: translateY(-20px) translateX(10px) rotate(90deg);
        opacity: 0.5;
    }
    50% {
        transform: translateY(-10px) translateX(-15px) rotate(180deg);
        opacity: 0.4;
    }
    75% {
        transform: translateY(-30px) translateX(5px) rotate(270deg);
        opacity: 0.6;
    }
}

/* Titre de la section */
.minister-title {
    font-size: 3rem;
    font-weight: 900;
    color: #1e293b;
    margin-bottom: 3rem;
    position: relative;
    animation: ministerTitleSlide 1.2s ease-out;
}

.minister-title-text {
    background: linear-gradient(45deg, #1e293b, #475569, #64748b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    display: inline-block;
}

.minister-title-text::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    border-radius: 2px;
    animation: ministerUnderline 1.5s ease-out 0.5s both;
}

/* Animation du titre */
@keyframes ministerTitleSlide {
    0% {
        opacity: 0;
        transform: translateY(-50px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes ministerUnderline {
    0% {
        width: 0;
        opacity: 0;
    }
    100% {
        width: 80px;
        opacity: 1;
    }
}

/* Carte principale */
.minister-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 30px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
    border: 2px solid rgba(34, 197, 94, 0.1);
    overflow: hidden;
    position: relative;
    animation: ministerCardSlide 1.5s ease-out 0.3s both;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.minister-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 35px 70px rgba(0, 0, 0, 0.15);
    border-color: rgba(34, 197, 94, 0.2);
}

/* Animation de la carte */
@keyframes ministerCardSlide {
    0% {
        opacity: 0;
        transform: translateY(50px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Container de l'image */
.minister-image-container {
    padding: 3rem;
    position: relative;
    animation: ministerImageContainer 1.8s ease-out 0.6s both;
}

@keyframes ministerImageContainer {
    0% {
        opacity: 0;
        transform: translateX(-50px);
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Wrapper de l'image */
.minister-image-wrapper {
    position: relative;
    display: inline-block;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.minister-image-wrapper:hover {
    transform: scale(1.05) rotate(2deg);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
}

/* Image du ministre */
.minister-image {
    width: 100%;
    max-width: 400px;
    height: auto;
    display: block;
    border-radius: 20px;
    transition: all 0.4s ease;
}

/* Drapeaux en arrière-plan */
.minister-flags {
    position: absolute;
    top: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
    z-index: 2;
    animation: ministerFlagsFloat 2s ease-out 1s both;
}

.flag {
    width: 40px;
    height: 30px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.flag:hover {
    transform: scale(1.1);
}

.flag-senegal {
    background: linear-gradient(90deg, #00853f 0%, #fcd116 50%, #ce1126 100%);
    position: relative;
}

.flag-senegal::after {
    content: '★';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #00853f;
    font-size: 12px;
}

.flag-csar {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    position: relative;
}

.flag-csar::after {
    content: 'CSAR';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 7px;
    font-weight: bold;
}

@keyframes ministerFlagsFloat {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Effet de lueur */
.minister-glow {
    position: absolute;
    top: -20px;
    left: -20px;
    right: -20px;
    bottom: -20px;
    background: linear-gradient(45deg, rgba(34, 197, 94, 0.1), rgba(59, 130, 246, 0.1));
    border-radius: 30px;
    filter: blur(20px);
    opacity: 0;
    transition: all 0.4s ease;
    z-index: -1;
}

.minister-image-wrapper:hover .minister-glow {
    opacity: 1;
    animation: ministerGlowPulse 2s ease-in-out infinite;
}

@keyframes ministerGlowPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.6;
    }
}

/* Contenu du ministre */
.minister-content {
    padding: 3rem;
    animation: ministerContentSlide 1.8s ease-out 0.9s both;
}

@keyframes ministerContentSlide {
    0% {
        opacity: 0;
        transform: translateX(50px);
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Sous-titre */
.minister-subtitle {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    animation: ministerSubtitleSlide 1.5s ease-out 1.2s both;
}

.subtitle-line {
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #22c55e, #16a34a);
    border-radius: 2px;
    margin-right: 15px;
    animation: ministerLineExtend 1s ease-out 1.5s both;
}

.subtitle-text {
    font-size: 1.2rem;
    font-weight: 700;
    color: #22c55e;
    text-transform: uppercase;
    letter-spacing: 2px;
}

@keyframes ministerSubtitleSlide {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes ministerLineExtend {
    0% {
        width: 0;
    }
    100% {
        width: 50px;
    }
}

/* Message du ministre */
.minister-message {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #475569;
    animation: ministerMessageReveal 2s ease-out 1.5s both;
}

.minister-message p {
    margin-bottom: 1.5rem;
    opacity: 0;
    animation: ministerParagraphSlide 0.8s ease-out both;
}

.minister-message p:nth-child(1) { animation-delay: 1.8s; }
.minister-message p:nth-child(2) { animation-delay: 2.0s; }
.minister-message p:nth-child(3) { animation-delay: 2.2s; }
.minister-message p:nth-child(4) { animation-delay: 2.4s; }
.minister-message p:nth-child(5) { animation-delay: 2.6s; }

.greeting {
    font-weight: 600;
    color: #1e293b;
    font-size: 1.2rem;
}

.closing {
    font-weight: 600;
    color: #22c55e;
    font-style: italic;
    text-align: right;
}

@keyframes ministerMessageReveal {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes ministerParagraphSlide {
    0% {
        opacity: 0;
        transform: translateX(-20px);
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .minister-title {
        font-size: 2.5rem;
    }
    
    .minister-image-container,
    .minister-content {
        padding: 2rem;
    }
    
    .minister-flags {
        top: 15px;
        right: 15px;
    }
    
    .flag {
        width: 35px;
        height: 25px;
    }
}

/* Animation de flottement du motif de grille */

    25% {
        transform: translateX(10px) translateY(-5px);
        opacity: 0.4;
    }
    50% {
        transform: translateX(-5px) translateY(10px);
        opacity: 0.2;
    }
    75% {
        transform: translateX(-10px) translateY(-5px);
        opacity: 0.4;
    }
}

/* Animation d'entrée de la carte */

    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Animation de pulsation au survol */

    50% {
        box-shadow: 0 50px 100px rgba(34, 197, 94, 0.35);
    }
}

/* Animation d'entrée du header */

    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Animation de rotation du logo */

    100% {
        transform: rotate(360deg);
    }
}

/* Animation de brillance du titre */

    50% {
        background-position: 100% 50%;
    }
}

/* Animation d'entrée du contenu */

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Styles pour les paragraphes */

/* Animation d'entrée des paragraphes */

    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Animation de brillance des paragraphes */

    100% {
        left: 100%;
    }
}


/* Animation d'entrée de la signature */

    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Animation de flottement de l'image */

    50% {
        transform: translateY(-10px);
    }
}

/* Animation d'entrée de l'image */

    100% {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@keyframes logoPulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 15px 40px rgba(34, 197, 94, 0.5);
    }
}

/* Styles pour les logos en haut des discours */

/* Animation de rebond du logo principal */

    50% {
        transform: translateY(-8px);
    }
}

/* Animation d'entrée du logo principal */

    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Style du titre de section */

    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

    50% {
        text-shadow: 0 0 30px rgba(34, 197, 94, 0.6);
    }
}

@keyframes speechSlideIn {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}


/* Effets de transition entre images */
@keyframes slideInFromLeft {
    0% {
        transform: translateX(-100%) scale(0.8);
        opacity: 0;
        filter: blur(10px);
    }
    50% {
        transform: translateX(-20%) scale(0.9);
        opacity: 0.7;
        filter: blur(5px);
    }
    100% {
        transform: translateX(0) scale(1);
        opacity: 1;
        filter: blur(0);
    }
}

@keyframes slideInFromRight {
    0% {
        transform: translateX(100%) scale(0.8);
        opacity: 0;
        filter: blur(10px);
    }
    50% {
        transform: translateX(20%) scale(0.9);
        opacity: 0.7;
        filter: blur(5px);
    }
    100% {
        transform: translateX(0) scale(1);
        opacity: 1;
        filter: blur(0);
    }
}

@keyframes zoomInWithRotation {
    0% {
        transform: scale(0.5) rotate(-180deg);
        opacity: 0;
        filter: brightness(0.3);
    }
    50% {
        transform: scale(0.8) rotate(-90deg);
        opacity: 0.6;
        filter: brightness(0.7);
    }
    100% {
        transform: scale(1) rotate(0deg);
        opacity: 1;
        filter: brightness(1);
    }
}

@keyframes fadeInWithGlow {
    0% {
        opacity: 0;
        transform: scale(0.9);
        filter: brightness(0.5) drop-shadow(0 0 0px rgba(34, 197, 94, 0));
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
        filter: brightness(1.2) drop-shadow(0 0 20px rgba(34, 197, 94, 0.5));
    }
    100% {
        opacity: 1;
        transform: scale(1);
        filter: brightness(1) drop-shadow(0 0 10px rgba(34, 197, 94, 0.3));
    }
}

@keyframes morphingTransition {
    0% {
        transform: scale(1) rotate(0deg) skew(0deg, 0deg);
        border-radius: 0%;
        filter: hue-rotate(0deg);
    }
    25% {
        transform: scale(1.1) rotate(5deg) skew(2deg, 1deg);
        border-radius: 20%;
        filter: hue-rotate(90deg);
    }
    50% {
        transform: scale(0.9) rotate(-5deg) skew(-1deg, 2deg);
        border-radius: 50%;
        filter: hue-rotate(180deg);
    }
    75% {
        transform: scale(1.05) rotate(3deg) skew(1deg, -1deg);
        border-radius: 30%;
        filter: hue-rotate(270deg);
    }
    100% {
        transform: scale(1) rotate(0deg) skew(0deg, 0deg);
        border-radius: 0%;
        filter: hue-rotate(360deg);
    }
}

/* ============================================
   ANIMATIONS MOBILES ADOUCIES - EFFETS PROFESSIONNELS
   ============================================ */

/* Image 1 - Effet Matrix unique pour mobile (8s) */
@keyframes ultraMatrixMobile {
    0% {
        transform: scale(1) rotate(0deg) translateX(0%);
        filter: brightness(1) contrast(1);
    }
    20% {
        transform: scale(1.04) rotate(0.3deg) translateX(-1%);
        filter: brightness(1.05) contrast(1.02);
    }
    40% {
        transform: scale(1.02) rotate(-0.2deg) translateX(1%);
        filter: brightness(1.08) contrast(1.05);
    }
    60% {
        transform: scale(1.06) rotate(0.1deg) translateX(-0.5%);
        filter: brightness(1.03) contrast(1.01);
    }
    80% {
        transform: scale(1.01) rotate(-0.1deg) translateX(0.5%);
        filter: brightness(1.06) contrast(1.03);
    }
    100% {
        transform: scale(1) rotate(0deg) translateX(0%);
        filter: brightness(1) contrast(1);
    }
}

/* N1 - Effet doré unique pour mobile (8s) */
@keyframes dynamicN1Mobile {
    0% {
        transform: scale(1.02) rotate(0deg) translateY(0%);
        filter: brightness(1) hue-rotate(0deg);
    }
    15% {
        transform: scale(1.05) rotate(0.4deg) translateY(-0.8%);
        filter: brightness(1.08) hue-rotate(5deg);
    }
    30% {
        transform: scale(1.03) rotate(-0.2deg) translateY(0.4%);
        filter: brightness(1.12) hue-rotate(-3deg);
    }
    45% {
        transform: scale(1.07) rotate(0.3deg) translateY(-0.3%);
        filter: brightness(1.06) hue-rotate(8deg);
    }
    60% {
        transform: scale(1.01) rotate(-0.1deg) translateY(0.6%);
        filter: brightness(1.09) hue-rotate(-2deg);
    }
    75% {
        transform: scale(1.04) rotate(0.2deg) translateY(-0.2%);
        filter: brightness(1.04) hue-rotate(3deg);
    }
    90% {
        transform: scale(1.06) rotate(-0.1deg) translateY(0.1%);
        filter: brightness(1.07) hue-rotate(-1deg);
    }
    100% {
        transform: scale(1.02) rotate(0deg) translateY(0%);
        filter: brightness(1) hue-rotate(0deg);
    }
}

/* N2 - Effet diamant unique pour mobile (8s) */
@keyframes diamondSpinMobile {
    0% {
        transform: scale(1) rotate(0deg) perspective(1000px) rotateY(0deg) rotateX(0deg);
        filter: brightness(1) saturate(1);
    }
    12% {
        transform: scale(1.04) rotate(0.6deg) perspective(1000px) rotateY(1.5deg) rotateX(0.3deg);
        filter: brightness(1.06) saturate(1.1);
    }
    25% {
        transform: scale(1.02) rotate(-0.4deg) perspective(1000px) rotateY(-1deg) rotateX(-0.2deg);
        filter: brightness(1.09) saturate(1.2);
    }
    37% {
        transform: scale(1.06) rotate(0.3deg) perspective(1000px) rotateY(0.8deg) rotateX(0.4deg);
        filter: brightness(1.04) saturate(1.05);
    }
    50% {
        transform: scale(1.01) rotate(-0.2deg) perspective(1000px) rotateY(-0.6deg) rotateX(-0.3deg);
        filter: brightness(1.11) saturate(1.15);
    }
    62% {
        transform: scale(1.05) rotate(0.5deg) perspective(1000px) rotateY(1.2deg) rotateX(0.2deg);
        filter: brightness(1.03) saturate(1.08);
    }
    75% {
        transform: scale(1.03) rotate(-0.1deg) perspective(1000px) rotateY(-0.4deg) rotateX(-0.1deg);
        filter: brightness(1.07) saturate(1.12);
    }
    87% {
        transform: scale(1.02) rotate(0.2deg) perspective(1000px) rotateY(0.5deg) rotateX(0.1deg);
        filter: brightness(1.05) saturate(1.03);
    }
    100% {
        transform: scale(1) rotate(0deg) perspective(1000px) rotateY(0deg) rotateX(0deg);
        filter: brightness(1) saturate(1);
    }
}

/* N3 - Effet onde de choc unique pour mobile (8s) */
@keyframes shockwaveMobile {
    0% {
        transform: scale(1) rotate(0deg) translateZ(0px);
        filter: brightness(1) contrast(1) blur(0px);
    }
    10% {
        transform: scale(1.03) rotate(0.1deg) translateZ(2px);
        filter: brightness(1.04) contrast(1.01) blur(0.5px);
    }
    20% {
        transform: scale(1.01) rotate(-0.1deg) translateZ(-1px);
        filter: brightness(1.07) contrast(1.03) blur(0px);
    }
    30% {
        transform: scale(1.05) rotate(0.2deg) translateZ(3px);
        filter: brightness(1.02) contrast(1.05) blur(0.3px);
    }
    40% {
        transform: scale(1.02) rotate(-0.15deg) translateZ(-2px);
        filter: brightness(1.09) contrast(1.08) blur(0px);
    }
    50% {
        transform: scale(1.04) rotate(0.1deg) translateZ(1px);
        filter: brightness(1.06) contrast(1.02) blur(0.2px);
    }
    60% {
        transform: scale(1.01) rotate(-0.05deg) translateZ(-1px);
        filter: brightness(1.08) contrast(1.06) blur(0px);
    }
    70% {
        transform: scale(1.03) rotate(0.15deg) translateZ(2px);
        filter: brightness(1.03) contrast(1.04) blur(0.4px);
    }
    80% {
        transform: scale(1.02) rotate(-0.1deg) translateZ(-1px);
        filter: brightness(1.05) contrast(1.01) blur(0px);
    }
    90% {
        transform: scale(1.01) rotate(0.05deg) translateZ(1px);
        filter: brightness(1.04) contrast(1.03) blur(0.1px);
    }
    100% {
        transform: scale(1) rotate(0deg) translateZ(0px);
        filter: brightness(1) contrast(1) blur(0px);
    }
}

/* N4 - Effet tourbillon galactique unique pour mobile (8s) */
@keyframes galacticMobile {
    0% {
        transform: scale(1) rotate(0deg) skew(0deg, 0deg);
        filter: hue-rotate(0deg) brightness(1) saturate(1);
    }
    14% {
        transform: scale(1.04) rotate(0.8deg) skew(0.2deg, 0.1deg);
        filter: hue-rotate(15deg) brightness(1.05) saturate(1.1);
    }
    28% {
        transform: scale(1.02) rotate(-0.5deg) skew(-0.1deg, 0.2deg);
        filter: hue-rotate(-10deg) brightness(1.08) saturate(1.2);
    }
    42% {
        transform: scale(1.06) rotate(1.2deg) skew(0.3deg, -0.1deg);
        filter: hue-rotate(25deg) brightness(1.03) saturate(1.05);
    }
    56% {
        transform: scale(1.01) rotate(-0.3deg) skew(-0.2deg, 0.1deg);
        filter: hue-rotate(-5deg) brightness(1.09) saturate(1.15);
    }
    70% {
        transform: scale(1.05) rotate(0.6deg) skew(0.1deg, -0.2deg);
        filter: hue-rotate(20deg) brightness(1.04) saturate(1.08);
    }
    84% {
        transform: scale(1.03) rotate(-0.2deg) skew(-0.1deg, 0.1deg);
        filter: hue-rotate(-8deg) brightness(1.06) saturate(1.12);
    }
    100% {
        transform: scale(1) rotate(0deg) skew(0deg, 0deg);
        filter: hue-rotate(0deg) brightness(1) saturate(1);
    }
}

/* N5 - Effet pulsation unique pour mobile (8s) */
@keyframes pulseMobile {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        filter: brightness(1) opacity(1);
    }
    8% {
        transform: scale(1.02) rotate(0.1deg);
        filter: brightness(1.03) opacity(0.98);
    }
    16% {
        transform: scale(1.05) rotate(-0.1deg);
        filter: brightness(1.06) opacity(0.95);
    }
    24% {
        transform: scale(1.01) rotate(0.05deg);
        filter: brightness(1.08) opacity(0.97);
    }
    32% {
        transform: scale(1.04) rotate(-0.05deg);
        filter: brightness(1.04) opacity(0.96);
    }
    40% {
        transform: scale(1.07) rotate(0.1deg);
        filter: brightness(1.09) opacity(0.94);
    }
    48% {
        transform: scale(1.02) rotate(-0.1deg);
        filter: brightness(1.05) opacity(0.98);
    }
    56% {
        transform: scale(1.03) rotate(0.05deg);
        filter: brightness(1.07) opacity(0.96);
    }
    64% {
        transform: scale(1.01) rotate(-0.05deg);
        filter: brightness(1.08) opacity(0.97);
    }
    72% {
        transform: scale(1.06) rotate(0.1deg);
        filter: brightness(1.03) opacity(0.95);
    }
    80% {
        transform: scale(1.02) rotate(-0.1deg);
        filter: brightness(1.06) opacity(0.98);
    }
    88% {
        transform: scale(1.04) rotate(0.05deg);
        filter: brightness(1.04) opacity(0.96);
    }
    96% {
        transform: scale(1.01) rotate(-0.05deg);
        filter: brightness(1.05) opacity(0.99);
    }
}

/* N6 - Effet rotation unique pour mobile (8s) */
@keyframes rotationMobile {
    0% {
        transform: scale(1) rotate(0deg) translateX(0%) translateY(0%);
        filter: brightness(1) contrast(1);
    }
    11% {
        transform: scale(1.03) rotate(0.4deg) translateX(0.5%) translateY(-0.3%);
        filter: brightness(1.04) contrast(1.02);
    }
    22% {
        transform: scale(1.01) rotate(-0.3deg) translateX(-0.4%) translateY(0.2%);
        filter: brightness(1.07) contrast(1.05);
    }
    33% {
        transform: scale(1.05) rotate(0.6deg) translateX(0.3%) translateY(-0.5%);
        filter: brightness(1.02) contrast(1.01);
    }
    44% {
        transform: scale(1.02) rotate(-0.2deg) translateX(-0.2%) translateY(0.4%);
        filter: brightness(1.08) contrast(1.06);
    }
    55% {
        transform: scale(1.04) rotate(0.3deg) translateX(0.6%) translateY(-0.2%);
        filter: brightness(1.05) contrast(1.03);
    }
    66% {
        transform: scale(1.01) rotate(-0.4deg) translateX(-0.3%) translateY(0.3%);
        filter: brightness(1.06) contrast(1.04);
    }
    77% {
        transform: scale(1.03) rotate(0.2deg) translateX(0.2%) translateY(-0.4%);
        filter: brightness(1.03) contrast(1.02);
    }
    88% {
        transform: scale(1.02) rotate(-0.1deg) translateX(-0.1%) translateY(0.2%);
        filter: brightness(1.07) contrast(1.05);
    }
    100% {
        transform: scale(1) rotate(0deg) translateX(0%) translateY(0%);
        filter: brightness(1) contrast(1);
    }
}

/* N7 - Effet zoom unique pour mobile (8s) */
@keyframes zoomMobile {
    0% {
        transform: scale(1) rotate(0deg);
        filter: brightness(1) saturate(1);
    }
    13% {
        transform: scale(1.02) rotate(0.1deg);
        filter: brightness(1.03) saturate(1.05);
    }
    25% {
        transform: scale(1.05) rotate(-0.1deg);
        filter: brightness(1.06) saturate(1.1);
    }
    38% {
        transform: scale(1.01) rotate(0.05deg);
        filter: brightness(1.08) saturate(1.15);
    }
    50% {
        transform: scale(1.04) rotate(-0.05deg);
        filter: brightness(1.04) saturate(1.08);
    }
    63% {
        transform: scale(1.07) rotate(0.1deg);
        filter: brightness(1.09) saturate(1.2);
    }
    75% {
        transform: scale(1.02) rotate(-0.1deg);
        filter: brightness(1.05) saturate(1.12);
    }
    88% {
        transform: scale(1.03) rotate(0.05deg);
        filter: brightness(1.07) saturate(1.18);
    }
    100% {
        transform: scale(1) rotate(0deg);
        filter: brightness(1) saturate(1);
    }
}

/* N8 - Effet fade unique pour mobile (8s) */
@keyframes fadeMobile {
    0%, 100% {
        transform: scale(1) rotate(0deg);
        filter: opacity(1) brightness(1) contrast(1);
    }
    9% {
        transform: scale(1.01) rotate(0.05deg);
        filter: opacity(0.98) brightness(1.02) contrast(1.01);
    }
    18% {
        transform: scale(1.03) rotate(-0.05deg);
        filter: opacity(0.95) brightness(1.05) contrast(1.03);
    }
    27% {
        transform: scale(1.01) rotate(0.03deg);
        filter: opacity(0.97) brightness(1.08) contrast(1.05);
    }
    36% {
        transform: scale(1.04) rotate(-0.03deg);
        filter: opacity(0.93) brightness(1.03) contrast(1.02);
    }
    45% {
        transform: scale(1.02) rotate(0.04deg);
        filter: opacity(0.96) brightness(1.06) contrast(1.04);
    }
    54% {
        transform: scale(1.05) rotate(-0.04deg);
        filter: opacity(0.92) brightness(1.09) contrast(1.06);
    }
    63% {
        transform: scale(1.01) rotate(0.02deg);
        filter: opacity(0.98) brightness(1.04) contrast(1.03);
    }
    72% {
        transform: scale(1.03) rotate(-0.02deg);
        filter: opacity(0.94) brightness(1.07) contrast(1.05);
    }
    81% {
        transform: scale(1.02) rotate(0.03deg);
        filter: opacity(0.96) brightness(1.05) contrast(1.04);
    }
    90% {
        transform: scale(1.01) rotate(-0.01deg);
        filter: opacity(0.99) brightness(1.02) contrast(1.01);
    }
}

/* ============================================
   ANIMATIONS TABLETTE ADOUCIES - EFFETS PROFESSIONNELS
   ============================================ */

/* Image 1 - Effet Matrix adouci pour tablette (6s au lieu de 5s) */
@keyframes ultraMatrixTablet {
    0% {
        transform: scale(1) rotate(0deg);
    }
    20% {
        transform: scale(1.04) rotate(0.3deg);
    }
    40% {
        transform: scale(1.07) rotate(-0.2deg) translateX(-0.7%);
    }
    60% {
        transform: scale(1.03) rotate(0.15deg) translateX(0.7%);
    }
    80% {
        transform: scale(1.05) rotate(-0.1deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
    }
}

/* N1 - Effet doré adouci pour tablette */
@keyframes dynamicN1Tablet {
    0% {
        transform: scale(1.03) rotate(-0.5deg);
    }
    25% {
        transform: scale(1.08) rotate(0.5deg) translateX(-2%);
    }
    50% {
        transform: scale(1.05) rotate(0deg) translateX(1%) translateY(-0.5%);
    }
    75% {
        transform: scale(1.1) rotate(-0.3deg) translateX(-0.7%);
    }
    100% {
        transform: scale(1.07) rotate(0deg);
    }
}

/* N2 - Effet diamant adouci pour tablette */
@keyframes diamondSpinTablet {
    0% {
        transform: scale(1) rotate(0deg) perspective(1000px) rotateY(0deg);
    }
    25% {
        transform: scale(1.05) rotate(1deg) perspective(1000px) rotateY(2deg);
    }
    50% {
        transform: scale(1.08) rotate(-0.5deg) perspective(1000px) rotateY(-1deg);
    }
    75% {
        transform: scale(1.03) rotate(0.3deg) perspective(1000px) rotateY(1deg);
    }
    100% {
        transform: scale(1) rotate(0deg) perspective(1000px) rotateY(0deg);
    }
}

/* N3 - Effet onde de choc adouci pour tablette */
@keyframes shockwaveTablet {
    0% {
        transform: scale(1) rotate(0deg);
        filter: brightness(1) contrast(1);
    }
    25% {
        transform: scale(1.03) rotate(0.3deg);
        filter: brightness(1.08) contrast(1.03);
    }
    50% {
        transform: scale(1.06) rotate(-0.2deg);
        filter: brightness(1.12) contrast(1.08);
    }
    75% {
        transform: scale(1.02) rotate(0.15deg);
        filter: brightness(1.05) contrast(1.02);
    }
    100% {
        transform: scale(1) rotate(0deg);
        filter: brightness(1) contrast(1);
    }
}

/* N4 - Effet tourbillon galactique adouci pour tablette */
@keyframes galacticTablet {
    0% {
        transform: scale(1) rotate(0deg);
        filter: hue-rotate(0deg) brightness(1);
    }
    25% {
        transform: scale(1.04) rotate(0.7deg);
        filter: hue-rotate(8deg) brightness(1.03);
    }
    50% {
        transform: scale(1.07) rotate(-0.4deg);
        filter: hue-rotate(-5deg) brightness(1.08);
    }
    75% {
        transform: scale(1.03) rotate(0.3deg);
        filter: hue-rotate(3deg) brightness(1.02);
    }
    100% {
        transform: scale(1) rotate(0deg);
        filter: hue-rotate(0deg) brightness(1);
    }
}

/* N5 - Effet pulsation adouci pour tablette */
@keyframes pulseTablet {
    0%, 100% {
        transform: scale(1);
        filter: brightness(1);
    }
    25% {
        transform: scale(1.03);
        filter: brightness(1.05);
    }
    50% {
        transform: scale(1.06);
        filter: brightness(1.08);
    }
    75% {
        transform: scale(1.02);
        filter: brightness(1.03);
    }
}

/* N6 - Effet rotation douce pour tablette */
@keyframes rotationTablet {
    0% {
        transform: scale(1) rotate(0deg);
    }
    25% {
        transform: scale(1.03) rotate(0.5deg);
    }
    50% {
        transform: scale(1.05) rotate(-0.3deg);
    }
    75% {
        transform: scale(1.02) rotate(0.2deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
    }
}

/* N7 - Effet zoom doux pour tablette */
@keyframes zoomTablet {
    0% {
        transform: scale(1);
    }
    25% {
        transform: scale(1.03);
    }
    50% {
        transform: scale(1.06);
    }
    75% {
        transform: scale(1.02);
    }
    100% {
        transform: scale(1);
    }
}

/* N8 - Effet fade doux pour tablette */
@keyframes fadeTablet {
    0%, 100% {
        transform: scale(1);
        filter: opacity(1) brightness(1);
    }
    25% {
        transform: scale(1.02);
        filter: opacity(0.97) brightness(1.03);
    }
    50% {
        transform: scale(1.05);
        filter: opacity(0.93) brightness(1.08);
    }
    75% {
        transform: scale(1.02);
        filter: opacity(0.96) brightness(1.02);
    }
}

/* ============================================
   DIAPORAMA D'ARRIÈRE-PLAN ULTRA-LUXE
   ============================================ */

/* Hero Section avec diaporama */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    /* Transparent to let the image fill the background */
    background-color: transparent;
}

/* Container du diaporama d'arrière-plan */
.hero-background-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    /* Avoid white flash between slides */
    background-color: transparent;
}

/* Chaque slide du diaporama avec transition visible et effets */
.hero-bg-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    visibility: hidden;
    will-change: opacity, transform, visibility, filter;
    transition: opacity 2s ease-in-out, 
                visibility 2s ease-in-out,
                transform 2s ease-in-out,
                filter 2s ease-in-out;
    transform: scale(0.95);
    filter: blur(10px) brightness(0.7);
    background-color: transparent;
}

.hero-bg-slide.active {
    opacity: 1;
    visibility: visible;
    z-index: 2;
    transform: scale(1);
    filter: blur(0px) brightness(1);
}

.hero-bg-slide.next-up {
    z-index: 1;
    opacity: 0;
}

/* Image sans zoom - photo entière visible */
.hero-bg-slide img {
    width: 100%;
    height: 100%;
    /* Full-bleed background image */
    object-fit: cover;
    object-position: center;
    transform: none;
    transition: none;
    background-color: transparent;
}

.hero-bg-slide.active img {
    transform: scale(1);
    /* default subtle motion; can be overridden per-slide below */
    animation: none;
}

/* ---------- Global responsive images ---------- */
img, .img-fluid { max-width: 100%; height: auto; }

/* ---------- Slow down desktop animations ---------- */
@media (min-width: 992px) {
  .hero-bg-slide[data-index].active img { animation-duration: 12s !important; }
  .hero-bg-slide { transition-duration: 1.2s !important; }
}

/* ---------- Disable heavy motion on tablets & mobiles ---------- */
@media (max-width: 991.98px) {
  .hero-section { min-height: 80vh; }
  .hero-bg-slide img { animation: none !important; transform: none !important; }
  .hero-bg-slide { transition: opacity .6s ease-in-out, visibility .6s ease-in-out !important; }
}

@media (max-width: 575.98px) {
  .hero-section { min-height: 70vh; }
}

/* Respect user preference: reduce motion */
@media (prefers-reduced-motion: reduce) {
  .hero-bg-slide, .hero-bg-slide * { transition: none !important; animation: none !important; }
}

/* Ken Burns and dynamic effects */
@keyframes kenZoomIn { 0% { transform: scale(1.05); } 100% { transform: scale(1.18); } }
@keyframes kenZoomOut { 0% { transform: scale(1.15); } 100% { transform: scale(1.0); } }
@keyframes panLeft { 0% { transform: scale(1.1) translateX(2%); } 100% { transform: scale(1.1) translateX(-2%); } }
@keyframes panRight { 0% { transform: scale(1.1) translateX(-2%); } 100% { transform: scale(1.1) translateX(2%); } }
@keyframes panUp { 0% { transform: scale(1.1) translateY(2%); } 100% { transform: scale(1.1) translateY(-2%); } }
@keyframes panDown { 0% { transform: scale(1.1) translateY(-2%); } 100% { transform: scale(1.1) translateY(2%); } }
@keyframes focusIn { 0% { filter: blur(8px) brightness(0.8); transform: scale(1.08); } 100% { filter: blur(0) brightness(1); transform: scale(1.1); } }

/* 🔥 EFFETS ULTRA-DYNAMIQUES AVANCÉS 🔥 */

/* Image 1 - Effet zoom et rotation dynamique (couleurs naturelles) */
@keyframes ultraMatrix {
    0% {
        transform: scale(1) rotate(0deg);
    }
    20% {
        transform: scale(1.08) rotate(0.5deg);
    }
    40% {
        transform: scale(1.12) rotate(-0.3deg) translateX(-1%);
    }
    60% {
        transform: scale(1.05) rotate(0.2deg) translateX(1%);
    }
    80% {
        transform: scale(1.1) rotate(-0.1deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
    }
}

/* EFFETS SPECTACULAIRES POUR N1 - Plus dynamique (couleurs naturelles) */
@keyframes dynamicN1 {
    0% {
        transform: scale(1.05) rotate(-1deg);
    }
    25% {
        transform: scale(1.12) rotate(1deg) translateX(-3%);
    }
    50% {
        transform: scale(1.08) rotate(0deg) translateX(2%) translateY(-1%);
    }
    75% {
        transform: scale(1.15) rotate(-0.5deg) translateX(-1%);
    }
    100% {
        transform: scale(1.1) rotate(0deg);
    }
}

/* N2 - Effet diamant tournant 3D (couleurs naturelles) */
@keyframes diamondSpin {
    0% {
        transform: scale(1) rotate(0deg) perspective(1000px) rotateY(0deg);
    }
    25% {
        transform: scale(1.08) rotate(2deg) perspective(1000px) rotateY(5deg);
    }
    50% {
        transform: scale(1.12) rotate(-1deg) perspective(1000px) rotateY(-3deg);
    }
    75% {
        transform: scale(1.05) rotate(1deg) perspective(1000px) rotateY(2deg);
    }
    100% {
        transform: scale(1) rotate(0deg) perspective(1000px) rotateY(0deg);
    }
}

/* N3 - Effet onde de choc dynamique (couleurs naturelles) */
@keyframes electricShock {
    0%, 100% {
        transform: scale(1.02) translateX(0) skewX(0deg);
    }
    10% {
        transform: scale(1.06) translateX(2%) skewX(1deg);
    }
    20% {
        transform: scale(1.1) translateX(-1%) skewX(-0.5deg);
    }
    35% {
        transform: scale(1.08) translateX(1.5%) skewX(0.8deg);
    }
    50% {
        transform: scale(1.12) translateX(-2%) skewX(-1deg);
    }
    65% {
        transform: scale(1.07) translateX(1%) skewX(0.5deg);
    }
    80% {
        transform: scale(1.05) translateX(-0.5%) skewX(-0.3deg);
    }
}

/* N4 - Effet galaxie tourbillonnante (couleurs naturelles) */
@keyframes galaxySwirl {
    0% {
        transform: scale(1.05) rotate(0deg);
    }
    25% {
        transform: scale(1.12) rotate(3deg) translateY(-2%);
    }
    50% {
        transform: scale(1.08) rotate(-2deg) translateY(1%);
    }
    75% {
        transform: scale(1.15) rotate(1deg) translateY(-1%);
    }
    100% {
        transform: scale(1.05) rotate(0deg);
    }
}

/* N5 - Effet pulsation dynamique (couleurs naturelles) */
@keyframes firePulse {
    0%, 100% {
        transform: scale(1.03) translateY(0);
    }
    15% {
        transform: scale(1.08) translateY(-1%);
    }
    30% {
        transform: scale(1.12) translateY(0.5%);
    }
    45% {
        transform: scale(1.06) translateY(-0.5%);
    }
    60% {
        transform: scale(1.1) translateY(1%);
    }
    75% {
        transform: scale(1.07) translateY(-0.8%);
    }
}

@keyframes rippleEffect {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.02);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Effet de particules flottantes */
@keyframes particleFloat {
    0%, 100% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0.6;
    }
    25% {
        transform: translateY(-20px) translateX(10px) scale(1.2);
        opacity: 0.8;
    }
    50% {
        transform: translateY(-10px) translateX(-15px) scale(0.9);
        opacity: 1;
    }
    75% {
        transform: translateY(-25px) translateX(5px) scale(1.1);
        opacity: 0.7;
    }
}

/* 🔥 MAPPING DES EFFETS ULTRA-DYNAMIQUES PAR IMAGE 🔥 */

/* Image 1 - Effet Matrix avec lueur verte pulsante */
.hero-bg-slide[data-index="0"].active img { 
    inset: 0;
    background: radial-gradient(circle at center, rgba(255, 215, 0, 0.15) 0%, transparent 70%);
    animation: rippleEffect 3s ease-in-out infinite;
    pointer-events: none;
    z-index: 1;
}

/* N2 - Effet diamant tournant avec cristaux bleus */
.hero-bg-slide[data-index="2"].active img { 
    animation: diamondSpin 12s ease-in-out infinite;
}

/* N3 - Effet onde de choc électrique */
.hero-bg-slide[data-index="3"].active img { 
    animation: electricShock 12s ease-in-out infinite;
}

/* N4 - Effet galaxie tourbillonnante violette */
.hero-bg-slide[data-index="4"].active img { 
    animation: galaxySwirl 12s ease-in-out infinite;
}

/* N5 - Effet feu pulsant avec chaleur intense */
.hero-bg-slide[data-index="5"].active img { 
    animation: firePulse 12s ease-in-out infinite;
}

/* Overlay sombre pour lisibilité du texte */
.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* Overlay équilibré pour lisibilité et visibilité des images */
    background: linear-gradient(
        135deg,
        rgba(0, 0, 0, 0.35) 0%,
        rgba(0, 0, 0, 0.25) 50%,
        rgba(0, 0, 0, 0.35) 100%
    );
    z-index: 2;
}

/* Overlay plus fort en mode sombre */
.dark .hero-overlay {
    background: linear-gradient(
        135deg,
        rgba(0, 0, 0, 0.50) 0%,
        rgba(0, 0, 0, 0.40) 50%,
        rgba(0, 0, 0, 0.50) 100%
    );
}

/* Navigation visible avec effets modernes */
.hero-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background: rgba(34, 197, 94, 0.15);
    backdrop-filter: blur(15px);
    border: 2px solid rgba(34, 197, 94, 0.3);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    opacity: 0.7;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.hero-section:hover .hero-nav {
    opacity: 1;
}

.hero-nav:hover {
    background: rgba(34, 197, 94, 0.9);
    border-color: rgba(34, 197, 94, 1);
    transform: translateY(-50%) scale(1.2);
    box-shadow: 0 15px 40px rgba(34, 197, 94, 0.5);
}

.hero-nav:active {
    transform: translateY(-50%) scale(1.1);
}

.hero-nav i {
    font-size: 1.3rem;
    transition: transform 0.3s ease;
}

.hero-nav:hover i {
    transform: scale(1.2);
}

.hero-nav.prev {
    left: 30px;
    animation: slide-in-left 1s ease-out;
}

.hero-nav.next {
    right: 30px;
    animation: slide-in-right 1s ease-out;
}

@keyframes slide-in-left {
    0% {
        transform: translateY(-50%) translateX(-100px);
        opacity: 0;
    }
    100% {
        transform: translateY(-50%) translateX(0);
        opacity: 0.7;
    }
}

@keyframes slide-in-right {
    0% {
        transform: translateY(-50%) translateX(100px);
        opacity: 0;
    }
    100% {
        transform: translateY(-50%) translateX(0);
        opacity: 0.7;
    }
}

/* Indicateurs (dots) améliorés et visibles */
.hero-indicators {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
    display: flex;
    gap: 12px;
    padding: 15px 25px;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.hero-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid rgba(255, 255, 255, 0.3);
    position: relative;
}

.hero-indicator::after {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.hero-indicator:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.3);
    border-color: rgba(34, 197, 94, 0.5);
}

.hero-indicator:hover::after {
    border-color: rgba(34, 197, 94, 0.3);
    animation: pulse-ring 1.5s ease-out infinite;
}

.hero-indicator.active {
    background: #22c55e;
    transform: scale(1.4);
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.8);
    border-color: #22c55e;
}

.hero-indicator.active::after {
    border-color: rgba(34, 197, 94, 0.5);
    animation: pulse-ring 2s ease-out infinite;
}

@keyframes pulse-ring {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Smart Fill: afficher l'image complète avec fond flou artistique */
.smart-fill {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.smart-fill .smart-fill-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: blur(40px) saturate(120%) brightness(0.6);
    transform: scale(1.15);
    z-index: 0;
    opacity: 0.8;
    transition: all 3s ease-in-out;
}

.smart-fill.active .smart-fill-bg {
    filter: blur(35px) saturate(130%) brightness(0.7);
    transform: scale(1.2);
}

.smart-fill img {
    position: relative;
    z-index: 1;
    /* Image complète visible sans coupure */
    object-fit: contain !important;
    max-width: 100%;
    max-height: 100%;
    box-shadow: 0 20px 80px rgba(0, 0, 0, 0.5);
}

/* Barre de progression animée et visible */
.hero-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 5px;
    width: 0%;
    background: linear-gradient(90deg, #22c55e 0%, #10b981 50%, #22c55e 100%);
    background-size: 200% 100%;
    z-index: 10;
    transition: width 0.1s linear;
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.6), 
                0 -2px 10px rgba(34, 197, 94, 0.4);
    animation: shimmer-progress 2s linear infinite;
}

@keyframes shimmer-progress {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Effet de pulsation sur la barre */
.hero-progress::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    box-shadow: 0 0 15px rgba(34, 197, 94, 0.8);
    animation: pulse-dot 1s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% {
        transform: translateY(-50%) scale(1);
        opacity: 1;
    }
    50% {
        transform: translateY(-50%) scale(1.3);
        opacity: 0.7;
    }
}

/* Animations de transition visibles et fluides */
@keyframes fadeInSlide {
    0% {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
        filter: blur(15px) brightness(0.5);
    }
    50% {
        opacity: 0.5;
        transform: scale(0.95) translateY(10px);
        filter: blur(8px) brightness(0.75);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
        filter: blur(0) brightness(1);
    }
}

@keyframes fadeOutSlide {
    0% {
        opacity: 1;
        transform: scale(1) translateY(0);
        filter: blur(0) brightness(1);
    }
    100% {
        opacity: 0;
        transform: scale(0.92) translateY(-20px);
        filter: blur(15px) brightness(0.5);
    }
}

/* Appliquer l'animation au changement avec effet visible */
.hero-bg-slide.active {
    animation: fadeInSlide 2.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

/* Animation pour la sortie */
.hero-bg-slide.leaving {
    animation: fadeOutSlide 2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

/* Respect de prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
    .hero-bg-slide,
    .hero-bg-slide img,
    .hero-nav,
    .hero-indicator {
        transition: none !important;
        animation: none !important;
    }
    
    .hero-bg-slide img {
        transform: scale(1) !important;
    }
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */

/* Tablettes */
@media (max-width: 1024px) {
    .hero-section {
        min-height: 80vh;
    }
    
    .hero-nav {
        width: 45px;
        height: 45px;
    }
    
    /* 🎯 ANIMATIONS TABLETTE ADOUCIES - EFFETS PROFESSIONNELS */
    
    /* Image 1 - Effet Matrix adouci pour tablette (6s au lieu de 5s) */
    .hero-bg-slide[data-index="0"].active img { 
        animation: ultraMatrixTablet 6s ease-in-out infinite;
    }
    
    /* N1 - Effet doré adouci pour tablette */
    .hero-bg-slide[data-index="1"].active img {
        animation: dynamicN1Tablet 6s ease-in-out infinite;
    }
    
    /* N2 - Effet diamant adouci pour tablette */
    .hero-bg-slide[data-index="2"].active img { 
        animation: diamondSpinTablet 6s ease-in-out infinite;
    }
    
    /* N3 - Effet onde de choc adouci pour tablette */
    .hero-bg-slide[data-index="3"].active img {
        animation: shockwaveTablet 6s ease-in-out infinite;
    }
    
    /* N4 - Effet tourbillon galactique adouci pour tablette */
    .hero-bg-slide[data-index="4"].active img {
        animation: galacticTablet 6s ease-in-out infinite;
    }
    
    /* N5 - Effet pulsation adouci pour tablette */
    .hero-bg-slide[data-index="5"].active img {
        animation: pulseTablet 6s ease-in-out infinite;
    }
    
    /* N6 - Effet rotation douce pour tablette */
    .hero-bg-slide[data-index="6"].active img {
        animation: rotationTablet 6s ease-in-out infinite;
    }
    
    /* N7 - Effet zoom doux pour tablette */
    .hero-bg-slide[data-index="7"].active img {
        animation: zoomTablet 6s ease-in-out infinite;
    }
    
    /* N8 - Effet fade doux pour tablette */
    .hero-bg-slide[data-index="8"].active img {
        animation: fadeTablet 6s ease-in-out infinite;
    }
    
    .hero-nav.prev {
        left: 20px;
    }
    
    .hero-nav.next {
        right: 20px;
    }
    
    .hero-indicators {
        bottom: 30px;
        padding: 12px 20px;
    }
    
    .hero-content {
        padding: 0 1.5rem;
    }
    
    .hero-title {
        font-size: 2.5rem !important;
    }
    
    .hero-subtitle {
        font-size: 1rem !important;
    }
    
    .hero-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-hero {
        width: 100%;
        justify-content: center;
    }
    
    .news-grid-2x2 {
        grid-template-columns: 1fr !important;
        gap: 2rem;
    }
    
    .services-grid {
        grid-template-columns: 1fr !important;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .hero-section {
        min-height: 100vh;
    }
    
    /* Cacher les flèches sur mobile */
    .hero-nav {
        display: none;
    }
    
    .hero-indicators {
        bottom: 80px;
        gap: 8px;
        padding: 10px 16px;
    }
    
    .hero-indicator {
        width: 8px;
        height: 8px;
    }
    
    /* 🎯 ANIMATIONS MOBILES ADOUCIES - EFFETS PROFESSIONNELS */
    
    /* Image 1 - Effet Matrix adouci pour mobile */
    .hero-bg-slide[data-index="0"].active img { 
        animation: ultraMatrixMobile 8s ease-in-out infinite;
    }
    
    /* N1 - Effet doré adouci pour mobile */
    .hero-bg-slide[data-index="1"].active img {
        animation: dynamicN1Mobile 8s ease-in-out infinite;
    }
    
    /* N2 - Effet diamant adouci pour mobile */
    .hero-bg-slide[data-index="2"].active img { 
        animation: diamondSpinMobile 8s ease-in-out infinite;
    }
    
    /* N3 - Effet onde de choc adouci pour mobile */
    .hero-bg-slide[data-index="3"].active img {
        animation: shockwaveMobile 8s ease-in-out infinite;
    }
    
    /* N4 - Effet tourbillon galactique adouci pour mobile */
    .hero-bg-slide[data-index="4"].active img {
        animation: galacticMobile 8s ease-in-out infinite;
    }
    
    /* N5 - Effet pulsation adouci pour mobile */
    .hero-bg-slide[data-index="5"].active img {
        animation: pulseMobile 8s ease-in-out infinite;
    }
    
    /* N6 - Effet rotation douce pour mobile */
    .hero-bg-slide[data-index="6"].active img {
        animation: rotationMobile 8s ease-in-out infinite;
    }
    
    /* N7 - Effet zoom doux pour mobile */
    .hero-bg-slide[data-index="7"].active img {
        animation: zoomMobile 8s ease-in-out infinite;
    }
    
    /* N8 - Effet fade doux pour mobile */
    .hero-bg-slide[data-index="8"].active img {
        animation: fadeMobile 8s ease-in-out infinite;
    }
    
    .hero-progress {
        height: 2px;
    }
    
    /* Ajuster les images pour mobile - sans zoom */
    .hero-bg-slide img {
        object-position: center center;
        transform: scale(1);
    }
    
    .hero-bg-slide.active img {
        transform: scale(1);
        animation: none;
    }
    
    /* Transitions plus douces sur mobile */
    .hero-bg-slide {
        transition: opacity 1s ease-in-out, 
                    visibility 1s ease-in-out,
                    transform 1s ease-in-out;
    }
    
    .hero-title {
        font-size: 2rem !important;
    }
    
    .hero-subtitle {
        font-size: 0.95rem !important;
        line-height: 1.5;
    }
    
    .container {
        padding: 0 1rem;
    }
    
    .stat-card-ultra {
        padding: 2rem 1.5rem !important;
    }
    
    .counter {
        font-size: 3rem !important;
    }
    
    .service-card-ultra {
        padding: 2rem 1.5rem !important;
    }
    
    .partner-card-pro {
        padding: 1.5rem !important;
    }
}

/* Très petits écrans */
@media (max-width: 480px) {
    .hero-indicators {
        bottom: 70px;
        gap: 6px;
    }
    
    .hero-indicator {
        width: 6px;
        height: 6px;
    }
}

.hero-content {
    text-align: center;
    z-index: 10;
    position: relative;
    max-width: 900px;
    padding: 0 2rem;
    color: white;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    /* Ombre de texte renforcée pour lisibilité avec overlay léger */
    text-shadow: 
        0 2px 4px rgba(0, 0, 0, 0.8),
        0 4px 8px rgba(0, 0, 0, 0.6),
        0 8px 16px rgba(0, 0, 0, 0.4),
        2px 2px 4px rgba(0, 0, 0, 0.9);
    line-height: 1.2;
    opacity: 0;
    transform: translateY(30px);
    animation: heroFadeIn 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.3s forwards;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 2.5rem;
    /* Ombre de texte renforcée pour lisibilité avec overlay léger */
    text-shadow: 
        0 2px 4px rgba(0, 0, 0, 0.8),
        0 4px 8px rgba(0, 0, 0, 0.5),
        1px 1px 3px rgba(0, 0, 0, 0.9);
    line-height: 1.6;
    opacity: 0;
    transform: translateY(30px);
    animation: heroFadeIn 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.6s forwards;
}

.hero-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 2rem;
    width: 100%;
    text-align: center;
    opacity: 0;
    transform: translateY(30px);
    animation: heroFadeIn 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.9s forwards;
}

/* Animation d'entrée douce */
@keyframes heroFadeIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Désactiver les animations si prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
    .hero-title,
    .hero-subtitle,
    .hero-buttons {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
}

.btn-hero {
    padding: 1rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-size: 1.1rem;
    min-width: 200px;
    justify-content: center;
    /* Ombre portée pour visibilité sur fond clair */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.btn-primary-hero {
    background: #3b82f6;
    color: white;
    border: 2px solid #3b82f6;
}

.btn-primary-hero:hover {
    background: #16a34a;
    border-color: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.6), 0 4px 12px rgba(0, 0, 0, 0.4);
}

.btn-secondary-hero {
    background: white;
    color: #6b7280;
    border: 2px solid white;
}

.btn-secondary-hero:hover {
    background: white;
    color: #1f2937;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.6), 0 4px 12px rgba(0, 0, 0, 0.4);
}

/* Services Section */
.services-section {
    padding: 5rem 0;
    background: #f8fafc;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.service-card {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.03) 0%, rgba(16, 185, 129, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 0;
}

.service-card:hover::before {
    opacity: 1;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(34, 197, 94, 0.2);
    border: 2px solid rgba(34, 197, 94, 0.1);
}

.service-card > * {
    position: relative;
    z-index: 1;
}

.service-icon {
    width: 80px;
    height: 80px;
    background: #22c55e;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2rem;
    color: white;
    transition: all 0.3s ease;
}

.service-card:hover .service-icon {
    transform: scale(1.1) rotate(5deg);
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);
}

.service-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    transition: color 0.3s ease;
}

.service-card:hover .service-title {
    color: #22c55e;
}

.service-description {
    color: #6b7280;
    line-height: 1.6;
}

/* News Section */
.news-section {
    padding: 5rem 0;
    background: white;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.news-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.12);
}

.news-image {
    height: 200px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #9ca3af;
}

.news-content {
    padding: 1.5rem;
}

.news-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.75rem;
}

.news-excerpt {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.news-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    color: #9ca3af;
    font-size: 0.9rem;
}

/* Gallery Section */
.gallery-section {
    padding: 5rem 0;
    background: #f8fafc;
}

/* ====== Adoucissement global des animations de section ====== */
.service-card-ultra,
.news-card-ultra,
.stat-card-ultra { animation-duration: 8s !important; }

@media (max-width: 1024px) {
  .service-card-ultra,
  .news-card-ultra,
  .stat-card-ultra { animation: none !important; transform: none !important; }
}

/* ====== Galerie: hauteur responsive et images contenues ====== */
.gallery-slider { height: 70vh; max-height: 680px; min-height: 320px; }
.gallery-slider img { width: 100%; height: 100%; object-fit: cover; object-position: center; }

@media (max-width: 1024px) { .gallery-slider { height: 50vh; max-height: 520px; } }
@media (max-width: 576px)  { .gallery-slider { height: 38vh; max-height: 420px; } }

/* Désactiver mouvements complexes de la galerie sur mobile/tablette */
@media (max-width: 1024px) {
  .gallery-section-pro .slider-slide,
  .gallery-section-pro .gallery-image-hover,
  .gallery-section-pro .color-overlay,
  .gallery-section-pro .zoom-icon-gallery,
  .gallery-section-pro .border-animation,
  .gallery-section-pro .ripple-effect-gallery { animation: none !important; transition: opacity .4s ease, transform .4s ease !important; transform: none !important; }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .services-grid,
    .news-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Hero Section avec Diaporama d'Arrière-plan Ultra-Luxe -->
<section class="hero-section">
    <!-- Diaporama d'arrière-plan -->
    <div class="hero-background-slider">
        <!-- Slide 1 - Image originale du site -->
        <div class="hero-bg-slide active" data-index="0">
            <img src="{{ asset('img/1.jpg') }}" alt="CSAR - Image d'accueil" loading="eager" fetchpriority="high" decoding="async" sizes="100vw">
        </div>
        
        <!-- Slide 2 - N1.jpg (préchargée) -->
        <div class="hero-bg-slide next-up" data-index="1">
            <img src="{{ asset('images/arriere plan/N1.jpg') }}" alt="CSAR Background 1" loading="eager" fetchpriority="high" decoding="async" sizes="100vw">
        </div>
        
        <!-- Slide 3 - N2.jpg -->
        <div class="hero-bg-slide" data-index="2">
            <img src="{{ asset('images/arriere plan/N2.jpg') }}" alt="CSAR Background 2" loading="lazy" decoding="async" sizes="100vw">
        </div>
        
        <!-- Slide 4 - N3.jpg -->
        <div class="hero-bg-slide" data-index="3">
            <img src="{{ asset('images/arriere plan/N3.jpg') }}" alt="CSAR Background 3" loading="lazy" decoding="async" sizes="100vw">
        </div>
        
        <!-- Slide 5 - N4.jpg -->
        <div class="hero-bg-slide" data-index="4">
            <img src="{{ asset('images/arriere plan/N4.jpg') }}" alt="CSAR Background 4" loading="lazy" decoding="async" sizes="100vw">
        </div>
        
        <!-- Slide 6 - N5.jpg -->
        <div class="hero-bg-slide" data-index="5">
            <img src="{{ asset('images/arriere plan/N5.jpg') }}" alt="CSAR Background 5" loading="lazy" decoding="async" sizes="100vw">
        </div>
    </div>
    
    <!-- Overlay sombre pour lisibilité -->
    <div class="hero-overlay"></div>
    
    <!-- Navigation discrète (visible au survol) -->
    <div class="hero-nav prev" onclick="heroSlider.prev()">
        <i class="fas fa-chevron-left"></i>
    </div>
    <div class="hero-nav next" onclick="heroSlider.next()">
        <i class="fas fa-chevron-right"></i>
    </div>
    
    <!-- Indicateurs (dots) -->
    <div class="hero-indicators">
        <div class="hero-indicator active" onclick="heroSlider.goTo(0)"></div>
        <div class="hero-indicator" onclick="heroSlider.goTo(1)"></div>
        <div class="hero-indicator" onclick="heroSlider.goTo(2)"></div>
        <div class="hero-indicator" onclick="heroSlider.goTo(3)"></div>
        <div class="hero-indicator" onclick="heroSlider.goTo(4)"></div>
        <div class="hero-indicator" onclick="heroSlider.goTo(5)"></div>
    </div>
    
    <!-- Barre de progression -->
    <div class="hero-progress"></div>
    
    <!-- Contenu Hero -->
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title" id="typewriter-title"></h1>
            <p class="hero-subtitle">
                {{ __('messages.home.subtitle') }}
            </p>
            <div class="hero-buttons">
                <a href="{{ '/demande' }}" class="btn-hero btn-primary-hero">
                    <i class="fas fa-file-alt"></i>
                    {{ __('messages.home.request_button') }}
                </a>
                <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="btn-hero btn-secondary-hero">
                    <i class="fas fa-info-circle"></i>
                    {{ __('messages.home.discover_button') }}
                </a>
            </div>
        </div>
    </div>
</section>

<script>
/* ============================================
   DIAPORAMA HERO ULTRA-LUXE ET PERFORMANT
   ============================================ */

const heroSlider = (function() {
    'use strict';
    
    // Configuration - Défilement automatique optimisé
    const config = {
        interval: 8000, // 8s par défaut (plus doux)
        transitionDuration: 2500, // 2.5 secondes de transition fluide
        enableAutoplay: true, // Démarrage automatique activé
        respectReducedMotion: true
    };
    
    // Variables d'état
    let currentIndex = 0;
    let totalSlides = 6;
    let autoplayInterval = null;
    let isAutoplayPaused = false;
    let reducedMotion = false;
    
    // Éléments DOM
    const slides = document.querySelectorAll('.hero-bg-slide');
    const indicators = document.querySelectorAll('.hero-indicator');
    const progressBar = document.querySelector('.hero-progress');
    
    // Vérifier la préférence de mouvement réduit
    function checkReducedMotion() {
        if (config.respectReducedMotion) {
            const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            reducedMotion = mediaQuery.matches;
            
            // Écouter les changements
            mediaQuery.addEventListener('change', (e) => {
                reducedMotion = e.matches;
                if (reducedMotion) {
                    stopAutoplay();
                } else if (config.enableAutoplay) {
                    startAutoplay();
                }
            });
        }
    }
    
    // Attendre que l'image d'une slide soit prête
    function ensureImageReady(slide) {
        return new Promise((resolve) => {
            const img = slide.querySelector('img');
            if (!img) return resolve();
            if (img.complete && img.naturalWidth > 0) {
                // Si supporte decode(), l'utiliser pour éviter flash
                if (img.decode) {
                    img.decode().then(resolve).catch(resolve);
                } else {
                    resolve();
                }
            } else {
                img.addEventListener('load', () => resolve(), { once: true });
                img.addEventListener('error', () => resolve(), { once: true });
            }
        });
    }

    // Changer de slide avec effets de transition visibles
    async function changeSlide(newIndex) {
        const oldIndex = currentIndex;
        let targetIndex = newIndex;
        
        // Calculer l'index cible avec boucle
        if (targetIndex >= totalSlides) targetIndex = 0;
        if (targetIndex < 0) targetIndex = totalSlides - 1;

        // Ne rien faire si on essaie d'aller à la même slide
        if (targetIndex === currentIndex) {
            console.log('⚠️ Même index, saut de transition');
            return;
        }

        const oldSlide = slides[oldIndex];
        const newSlide = slides[targetIndex];

        console.log(`🔄 TRANSITION: slide ${oldIndex} → ${targetIndex}`);

        // Assurer que la nouvelle image est prête avant la transition
        await ensureImageReady(newSlide);

        // Marquer l'ancienne slide comme sortante avec effet
        if (oldSlide && oldSlide !== newSlide) {
            oldSlide.classList.add('leaving');
            oldSlide.classList.remove('active');
        }

        // Marquer tous les indicateurs inactifs
        indicators.forEach(ind => ind.classList.remove('active'));

        // Afficher la nouvelle slide avec effet d'entrée
        newSlide.style.visibility = 'visible';
        newSlide.classList.remove('leaving');
        newSlide.classList.add('active');

        // Forcer un reflow pour l'animation
        void newSlide.offsetWidth;
        
        // Activer l'indicateur
        if (indicators[targetIndex]) {
        indicators[targetIndex].classList.add('active');
        }

        // Masquer complètement l'ancienne slide après la transition
        setTimeout(() => {
            if (oldSlide && oldSlide !== newSlide) {
                oldSlide.classList.remove('leaving');
                oldSlide.style.visibility = 'hidden';
            }
        }, 2500); // Attendre la fin de l'animation de sortie

        currentIndex = targetIndex;

        // Préparer la prochaine slide
        const nextIndex = (currentIndex + 1) % totalSlides;
        slides.forEach(slide => slide.classList.remove('next-up'));
        if (slides[nextIndex]) {
        slides[nextIndex].classList.add('next-up');
        }

        // Réinitialiser la barre de progression avec animation
        if (progressBar && config.enableAutoplay) {
            progressBar.style.width = '0%';
            progressBar.style.transition = 'none';
            setTimeout(() => {
                progressBar.style.transition = `width ${config.interval}ms linear`;
                progressBar.style.width = '100%';
            }, 100);
        }

        console.log(`✅ Transition terminée - Slide ${targetIndex} active`);
    }
    
    // Slide suivante
    function next(fromAutoplay = false) {
        console.log(`▶️ NEXT appelé (auto: ${fromAutoplay})`);
        changeSlide(currentIndex + 1);
        if (!fromAutoplay) {
        resetAutoplay();
        }
    }
    
    // Slide précédente
    function prev() {
        console.log('◀️ PREV appelé');
        changeSlide(currentIndex - 1);
        resetAutoplay();
    }
    
    // Aller à une slide spécifique
    function goTo(index) {
        console.log(`🎯 GOTO ${index} appelé`);
        if (index >= 0 && index < totalSlides) {
            changeSlide(index);
            resetAutoplay();
        }
    }
    
    // Démarrer l'autoplay automatique
    function startAutoplay() {
        if (!config.enableAutoplay) {
            console.log('⏸️  Autoplay désactivé dans la configuration');
            return;
        }
        
        // Arrêter tout autoplay existant d'abord
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            console.log('🔄 Arrêt de l\'autoplay existant');
        }
        
        console.log('▶️  DÉFILEMENT AUTOMATIQUE DÉMARRÉ');
        console.log(`⏱️  Changement toutes les ${config.interval}ms (${config.interval/1000}s)`);
        console.log('🎨  Effets dynamiques activés sur toutes les images');
        
        // Initialiser la barre de progression
        if (progressBar) {
            progressBar.style.width = '0%';
            setTimeout(() => {
                progressBar.style.transition = `width ${config.interval}ms linear`;
                progressBar.style.width = '100%';
            }, 100);
        }
        
        // Créer l'intervalle d'autoplay - APPELER next(true) pour indiquer que c'est automatique
        autoplayInterval = setInterval(() => {
            if (!isAutoplayPaused) {
                const nextSlide = (currentIndex + 1) % totalSlides;
                console.log(`⏭️  AUTO-TRANSITION: ${currentIndex} → ${nextSlide}`);
                next(true); // true = fromAutoplay, ne réinitialise pas l'autoplay
            } else {
                console.log('⏸️  Autoplay en pause (survol)');
            }
        }, config.interval);
        
        console.log(`✅ Autoplay activé - Interval ID: ${autoplayInterval}`);
        console.log(`⏰ Prochaine transition dans ${config.interval/1000} secondes`);
    }
    
    // Arrêter l'autoplay
    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
            console.log('⏸️  Autoplay arrêté');
        }
    }
    
    // Redémarrer l'autoplay
    function resetAutoplay() {
        if (config.enableAutoplay) {
            console.log('🔄 Redémarrage de l\'autoplay');
            stopAutoplay();
            startAutoplay();
        }
    }
    
    // Pause au survol de la section hero
    function setupHoverPause() {
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.addEventListener('mouseenter', () => {
                console.log('🖱️ Souris sur le hero - Pause autoplay');
                isAutoplayPaused = true;
                if (progressBar) {
                    progressBar.style.transition = 'none';
                }
            });
            
            heroSection.addEventListener('mouseleave', () => {
                console.log('🖱️ Souris quitte le hero - Reprise autoplay');
                isAutoplayPaused = false;
                if (progressBar) {
                    progressBar.style.transition = `width ${config.interval}ms linear`;
                    progressBar.style.width = '100%';
                }
            });
        }
    }
    
    // Navigation au clavier
    function setupKeyboardNav() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prev();
            } else if (e.key === 'ArrowRight') {
                next();
            }
        });
    }
    
    // Précharger les images suivantes pour performance
    function preloadNextImages() {
        for (let i = 1; i <= 3; i++) {
            const nextIndex = (currentIndex + i) % totalSlides;
            const img = slides[nextIndex].querySelector('img');
            if (img && img.loading === 'lazy') {
                img.loading = 'eager';
            }
        }
    }
    
    // Initialisation
    function init() {
        console.log('═══════════════════════════════════════════════');
        console.log('🎬 INITIALISATION DU DIAPORAMA HERO CSAR');
        console.log('═══════════════════════════════════════════════');
        console.log(`📊 Configuration:`);
        console.log(`   • ${totalSlides} images (1.jpg + N1-N5)`);
        console.log(`   • Slides trouvées: ${slides.length}`);
        console.log(`   • Indicateurs: ${indicators.length}`);
        console.log(`   • Intervalle: ${config.interval}ms (${config.interval/1000}s)`);
        console.log(`   • Transition: ${config.transitionDuration}ms`);
        console.log(`   • Autoplay: ${config.enableAutoplay ? 'ACTIVÉ ✅' : 'DÉSACTIVÉ ❌'}`);
        
        // Vérifier que les slides existent
        if (slides.length === 0) {
            console.error('❌ ERREUR: Aucune slide trouvée!');
            return;
        }
        
        // Ignorer complètement reducedMotion pour forcer l'autoplay
        reducedMotion = false;
        console.log(`🔥 FORÇAGE AUTOPLAY: reducedMotion désactivé`);
        
        // Configurer les événements
            setupHoverPause();
            setupKeyboardNav();
            
        // FORCER LE DÉMARRAGE IMMÉDIAT DE L'AUTOPLAY
        console.log('🚀🚀🚀 DÉMARRAGE IMMÉDIAT DE L\'AUTOPLAY 🚀🚀🚀');
        console.log(`⏰ Changement automatique toutes les ${config.interval}ms (${config.interval/1000}s)`);
        
        // Démarrer immédiatement sans délai
                startAutoplay();
        console.log('✅ Autoplay activé - Les images vont changer automatiquement');
        
        // Précharger les images suivantes
        preloadNextImages();
        
        console.log('═══════════════════════════════════════════════');
        console.log('✅ DIAPORAMA PRÊT ET EN DÉFILEMENT AUTOMATIQUE');
        console.log('   🔥 6 images avec animations dynamiques');
        console.log('   ⚡ Changement automatique toutes les 5 secondes');
        console.log('   🎯 Surveillez la console pour voir les transitions');
        console.log('═══════════════════════════════════════════════');
    }
    
    // API publique
    return {
        init,
        next,
        prev,
        goTo,
        start: startAutoplay,
        stop: stopAutoplay
    };
})();

// Effet machine à écrire en boucle pour le titre
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le diaporama
    heroSlider.init();
    
    const titleElement = document.getElementById('typewriter-title');
    const text = '{{ __('messages.home.title') }}';
    let index = 0;
    let isDeleting = false;
    
    function typeWriter() {
        if (!isDeleting && index < text.length) {
            // Phase d'écriture
            titleElement.textContent += text.charAt(index);
            index++;
            setTimeout(typeWriter, 80); // Vitesse de frappe (80ms par caractère)
        } else if (!isDeleting && index === text.length) {
            // Pause à la fin de l'écriture
            setTimeout(function() {
                isDeleting = true;
                typeWriter();
            }, 2000); // Pause de 2 secondes avant d'effacer
        } else if (isDeleting && index > 0) {
            // Phase d'effacement
            titleElement.textContent = text.substring(0, index - 1);
            index--;
            setTimeout(typeWriter, 50); // Vitesse d'effacement (50ms par caractère)
        } else if (isDeleting && index === 0) {
            // Pause avant de recommencer
            isDeleting = false;
            setTimeout(typeWriter, 500); // Pause de 0.5 seconde avant de recommencer
        }
    }
    
    // Démarrer l'effet après un court délai
    setTimeout(typeWriter, 500);
});
</script>

<!-- Services Section ULTRA PRO -->
<section class="services-section-ultra fade-in-up" style="padding: 100px 0; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); position: relative; overflow: hidden;">
    <!-- Animated Background Shapes -->
    <div style="position: absolute; top: -50px; left: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(34, 197, 94, 0.08), transparent); border-radius: 50%; filter: blur(50px); animation: float-shape 12s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -80px; right: -80px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59, 130, 246, 0.06), transparent); border-radius: 50%; filter: blur(60px); animation: float-shape 15s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title with Animation -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-down">
            <div style="display: inline-block; padding: 8px 24px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); border-radius: 50px; margin-bottom: 1.5rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); animation: shine-badge 3s infinite;"></div>
                <span style="color: #22c55e; font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px; position: relative; z-index: 1;">
                    <i class="fas fa-concierge-bell" style="margin-right: 8px; animation: ring-bell 3s ease-in-out infinite;"></i>
                    {{ __('home.services.badge') }}
                </span>
                </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1rem; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                {{ __('home.services.title') }}
            </h2>
        </div>
        
        <div class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem;">
            <!-- Service Card 1 -->
            <a href="{{ route('gallery') }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="100" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite;">
                <!-- Animated Border -->
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #22c55e, #3b82f6, #22c55e); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                
                <!-- Glow Effect -->
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(34, 197, 94, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);">
                        <i class="fas fa-truck" style="animation: bounce-icon 2s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #22c55e; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                    </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        Distributions alimentaires
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    Nos équipes distribuent des denrées alimentaires aux populations dans le besoin à travers tout le Sénégal
                </p>
            </div>
            </a>
            
            <!-- Service Card 2 -->
            <a href="{{ route('map') }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="250" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite; animation-delay: 1s;">
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #3b82f6, #8b5cf6, #3b82f6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(59, 130, 246, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);">
                        <i class="fas fa-warehouse" style="animation: bounce-icon 2s ease-in-out infinite; animation-delay: 0.3s;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #3b82f6; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        Magasins de stockage CSAR
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    Notre réseau de magasins de stockage stratégiques assure le stockage et la distribution des denrées alimentaires
                </p>
            </div>
            </a>
            
            <!-- Service Card 3 -->
            <a href="{{ route('suivi_static') }}" class="service-card-ultra" data-aos="flip-left" data-aos-delay="400" style="text-decoration: none; color: inherit; background: white; padding: 2.5rem; border-radius: 24px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: block; cursor: pointer; animation: float-service 4s ease-in-out infinite; animation-delay: 2s;">
                <div class="service-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #8b5cf6, #ec4899, #8b5cf6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-rotate 4s linear infinite;"></div>
                <div class="service-glow" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; background: radial-gradient(circle, rgba(139, 92, 246, 0.3), transparent); opacity: 0; filter: blur(30px); transition: opacity 0.6s ease; z-index: 0;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div class="service-icon-ultra" style="width: 100px; height: 100px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: white; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; box-shadow: 0 10px 30px rgba(139, 92, 246, 0.3);">
                        <i class="fas fa-search" style="animation: pulse-search 2s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -5px; border-radius: 50%; border: 3px dashed #8b5cf6; opacity: 0; animation: rotate-dashed 10s linear infinite;"></div>
                </div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; text-align: center; transition: all 0.3s ease;">
                        Suivre ma demande
                    </h3>
                    <p style="color: #6b7280; line-height: 1.7; text-align: center; transition: all 0.3s ease;">
                    Consultez l'état d'avancement de votre demande avec votre code de suivi unique
                </p>
            </div>
            </a>
        </div>
    </div>
</section>

<!-- Publications et Rapports SIM Section -->
@if((isset($publications) && $publications->count() > 0) || (isset($simReports) && $simReports->count() > 0))
<section class="publications-section fade-in-left" style="padding: 100px 0; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); position: relative; overflow: hidden;">
    <!-- Animated Particles -->
    <div style="position: absolute; top: 15%; left: 8%; width: 180px; height: 180px; background: radial-gradient(circle, rgba(34, 197, 94, 0.06), transparent); border-radius: 50%; filter: blur(35px); animation: float-particle-slow 18s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: 25%; right: 12%; width: 220px; height: 220px; background: radial-gradient(circle, rgba(59, 130, 246, 0.05), transparent); border-radius: 50%; filter: blur(45px); animation: float-particle-slow 22s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1; max-width: 1400px; margin: 0 auto; padding: 0 20px;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 60px;">
            <div style="display: inline-block; position: relative;">
                <h2 style="font-size: 3rem; font-weight: 800; color: #1f2937; margin-bottom: 20px; position: relative; z-index: 2;">
                    Publications & Rapports SIM
                </h2>
                <div style="position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 120px; height: 4px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 2px; z-index: 1;"></div>
            </div>
            <p style="font-size: 1.2rem; color: #6b7280; max-width: 700px; margin: 0 auto; line-height: 1.6;">
                Découvrez nos rapports SIM, études de marché et documents officiels du CSAR
            </p>
        </div>
        
        <!-- Publications et Rapports Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 50px;">
            
            <!-- Rapports SIM -->
            @if(isset($simReports) && $simReports->count() > 0)
                @foreach($simReports->take(4) as $report)
                <div class="sim-report-card" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; transition: all 0.4s ease; position: relative;">
                    <!-- Image de couverture -->
                    <div style="height: 220px; overflow: hidden; position: relative;">
                        @if($report->cover_image)
                        <img src="{{ asset('storage/' . $report->cover_image) }}" 
                             alt="{{ $report->title }}" 
                             class="news-image-hover"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                             onerror="this.parentElement.innerHTML='<div style=\'width: 100%; height: 100%; background: linear-gradient(135deg, #059669 0%, #047857 100%); display: flex; align-items: center; justify-content: center; flex-direction: column;\'><i class=\'fas fa-chart-line\' style=\'color: #fff; font-size: 50px; margin-bottom: 10px;\'></i><span style=\'color: #fff; font-weight: 600;\'>Rapport SIM</span></div>'">
                        @else
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #059669 0%, #047857 100%); display: flex; align-items: center; justify-content: center; flex-direction: column;">
                            <i class="fas fa-chart-line" style="color: #fff; font-size: 50px; margin-bottom: 10px;"></i>
                            <span style="color: #fff; font-weight: 600; font-size: 14px;">Rapport SIM</span>
                        </div>
                        @endif
                        
                        <!-- Overlay avec gradient -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 60px; background: linear-gradient(transparent, rgba(0,0,0,0.7));"></div>
                        
                        <!-- Badge Type de Rapport -->
                        <div style="position: absolute; top: 15px; right: 15px; background: rgba(5, 150, 105, 0.95); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; backdrop-filter: blur(10px);">
                            <i class="fas fa-chart-bar" style="margin-right: 5px;"></i>{{ $report->report_type_label ?? 'SIM' }}
                        </div>
                        
                        <!-- Badge Période -->
                        @if($report->formatted_period)
                        <div style="position: absolute; top: 15px; left: 15px; background: rgba(0, 0, 0, 0.7); color: white; padding: 4px 10px; border-radius: 15px; font-size: 0.75rem; font-weight: 500; backdrop-filter: blur(10px);">
                            {{ $report->formatted_period }}
                        </div>
                        @endif
                    </div>
                    
                    <!-- Contenu -->
                    <div style="padding: 25px;">
                        <h3 style="font-size: 1.3rem; font-weight: 700; color: #1f2937; margin-bottom: 12px; line-height: 1.4;">
                            {{ Str::limit($report->title, 60) }}
                        </h3>
                        
                        <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                            {{ $report->excerpt }}
                        </p>
                        
                        <!-- Statistiques -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 12px; background: #f8fafc; border-radius: 10px;">
                            <div style="text-align: center;">
                                <div style="font-weight: 700; color: #059669; font-size: 1.1rem;">{{ $report->view_count ?? 0 }}</div>
                                <div style="font-size: 0.75rem; color: #6b7280;">Vues</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-weight: 700; color: #3b82f6; font-size: 1.1rem;">{{ $report->download_count ?? 0 }}</div>
                                <div style="font-size: 0.75rem; color: #6b7280;">Téléchargements</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-weight: 700; color: #f59e0b; font-size: 1.1rem;">
                                    {{ $report->published_at ? $report->published_at->format('M Y') : 'Récent' }}
                                </div>
                                <div style="font-size: 0.75rem; color: #6b7280;">Publication</div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div style="display: flex; gap: 10px;">
                            <a href="{{ route('sim.show', ['locale' => app()->getLocale(), 'simReport' => $report->id]) }}" 
                               style="flex: 1; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; text-decoration: none; padding: 12px 20px; border-radius: 10px; font-weight: 600; text-align: center; transition: all 0.3s ease; font-size: 0.9rem;">
                                <i class="fas fa-eye" style="margin-right: 6px;"></i>Consulter
                            </a>
                            @if($report->document_file || $report->file_path)
                            <a href="{{ route('sim.download', ['locale' => app()->getLocale(), 'simReport' => $report->id]) }}" 
                               style="background: #f59e0b; color: white; text-decoration: none; padding: 12px 16px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease;">
                                <i class="fas fa-download"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
            
            <!-- Publications traditionnelles -->
            @if(isset($publications) && $publications->count() > 0)
                @foreach($publications->take(2) as $publication)
            <div class="publication-card" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; transition: all 0.4s ease; position: relative;">
                <!-- Image de couverture -->
                <div style="height: 200px; overflow: hidden; position: relative;">
                    @if($publication->document_cover_image)
                    <img src="{{ asset('storage/' . $publication->document_cover_image) }}" 
                         alt="{{ $publication->document_title }}" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                         onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                    @else
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-pdf" style="color: #fff; font-size: 60px;"></i>
                    </div>
                    @endif
                    
                    <!-- Overlay avec gradient -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 60px; background: linear-gradient(transparent, rgba(0,0,0,0.7));"></div>
                    
                    <!-- Badge PDF -->
                    <div style="position: absolute; top: 15px; right: 15px; background: rgba(239, 68, 68, 0.9); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; backdrop-filter: blur(10px);">
                        <i class="fas fa-file-pdf" style="margin-right: 5px;"></i>PDF
                    </div>
                </div>
                
                <!-- Contenu -->
                <div style="padding: 25px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 10px; line-height: 1.4;">
                        {{ $publication->document_title }}
                    </h3>
                    
                    <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 15px; line-height: 1.5;">
                        {{ Str::limit(strip_tags($publication->content), 100) }}
                    </p>
                    
                    <!-- Métadonnées -->
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px; font-size: 0.8rem; color: #9ca3af;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-calendar" style="color: #22c55e;"></i>
                            <span>{{ $publication->published_at->format('d F Y') }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-user" style="color: #3b82f6;"></i>
                            <span>{{ $publication->creator->name ?? 'CSAR' }}</span>
                        </div>
                    </div>
                    
                    <!-- Bouton de téléchargement -->
                    <a href="{{ $publication->document_url }}" target="_blank" 
                       style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(245, 158, 11, 0.4)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(245, 158, 11, 0.3)'">
                        <i class="fas fa-download"></i>
                        Télécharger
                    </a>
                </div>
            </div>
            @endforeach
            @endif
        </div>
        
        <!-- Bouton "Voir toutes les publications" -->
        <div style="text-align: center;">
            <a href="{{ route('news', ['locale' => app()->getLocale()]) }}?filter=publications" 
               style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; padding: 15px 30px; border-radius: 15px; text-decoration: none; font-weight: 700; font-size: 1.1rem; transition: all 0.3s ease; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);"
               onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(245, 158, 11, 0.4)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(245, 158, 11, 0.3)'">
                <i class="fas fa-book-open"></i>
                Voir toutes les publications
            </a>
        </div>
    </div>
</section>
@endif

<!-- Actualités Section ULTRA PRO -->
<section class="news-section-ultra fade-in-left" style="padding: 100px 0; background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); position: relative; overflow: hidden;">
    <!-- Animated Particles -->
    <div style="position: absolute; top: 10%; left: 5%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(34, 197, 94, 0.05), transparent); border-radius: 50%; filter: blur(40px); animation: float-particle-slow 20s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: 20%; right: 10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(59, 130, 246, 0.04), transparent); border-radius: 50%; filter: blur(50px); animation: float-particle-slow 25s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1; max-width: 1400px; margin: 0 auto; padding: 0 20px;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 5rem;" data-aos="fade-up">
            <h2 style="font-size: 3.5rem; font-weight: 700; margin-bottom: 1rem; color: #5a7d70; letter-spacing: 2px; text-transform: uppercase;">
                {{ __('home.news.title') }}
            </h2>
            <!-- Ligne décorative -->
            <div style="width: 200px; height: 2px; background: #5a7d70; margin: 0 auto 3rem;"></div>
        </div>
        
        <div class="news-grid-2x2" style="margin-bottom: 3rem;">
        @php $newsCount = isset($latestNews) ? $latestNews->count() : 0; @endphp
            @if(isset($latestNews) && $latestNews->count() > 0)
                @foreach($latestNews as $index => $news)
                <div class="news-card-ultra" data-aos="fade-up" data-aos-delay="{{ $index * 150 }}" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); position: relative; cursor: pointer; animation: float-news 5s ease-in-out infinite; animation-delay: {{ $index * 1 }}s;">
                    <!-- Glow on hover -->
                    <div style="position: absolute; inset: -3px; background: linear-gradient(135deg, #3b82f6, #22c55e, #3b82f6); background-size: 200% 200%; border-radius: 24px; opacity: 0; z-index: -1; animation: gradient-news 4s ease infinite; transition: opacity 0.5s ease;"></div>
                    
                    @if($news->featured_image || $news->cover_image)
                    <div style="position: relative; height: 220px; overflow: hidden;">
                        @php
                            // Priorité : cover_image > featured_image
                            $imagePath = $news->cover_image ?: $news->featured_image;
                            $imagePath = trim((string) $imagePath);
                            if (preg_match('/^https?:\/\//i', $imagePath)) {
                                $imageUrl = $imagePath;
                            } else {
                                $imageUrl = asset('storage/' . ltrim($imagePath, '/'));
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" alt="{{ $news->title }}" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);"
                             class="news-image-hover"
                             onerror="this.src='{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}'">
                        
                        <!-- Overlay gradient -->
                        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.3) 100%); transition: all 0.5s ease;"></div>
                        
                        <!-- Date Badge -->
                        <div style="position: absolute; top: 15px; right: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 20px; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <i class="fas fa-calendar-alt" style="color: #3b82f6; font-size: 0.9rem;"></i>
                            <span style="color: #1f2937; font-weight: 600; font-size: 0.85rem;">
                                {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}
                            </span>
                    </div>
                    </div>
                    @else
                    <div style="position: relative; height: 220px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <i class="far fa-newspaper" style="font-size: 4rem; color: rgba(255, 255, 255, 0.3); animation: pulse-news-icon 3s ease-in-out infinite;"></i>
                        <div style="position: absolute; top: 15px; right: 15px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 8px 16px; border-radius: 20px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-calendar-alt" style="color: #3b82f6; font-size: 0.9rem;"></i>
                            <span style="color: #1f2937; font-weight: 600; font-size: 0.85rem;">
                                {{ $news->published_at ? $news->published_at->format('d/m/Y') : $news->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    @endif
                    
                    <div style="padding: 2rem; position: relative;">
                        <!-- Badge ACTUALITÉ -->
                        <div style="display: inline-block; background: #5a7d70; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                            ACTUALITÉ
                        </div>
                        
                        <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; line-height: 1.4; transition: color 0.3s ease;">
                            {{ $news->title }}
                        </h3>
                        <p style="color: #6b7280; line-height: 1.7; margin-bottom: 1.5rem; font-size: 0.95rem;">
                            {{ $news->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($news->content), 120) }}
                        </p>
                        
                        <a href="/fr/actualites/{{ $news->id }}" style="display: inline-block; padding: 10px 24px; background: transparent; color: #d4a574; border: 2px solid #d4a574; border-radius: 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; font-size: 0.9rem;">
                            Lire la suite
                        </a>
                    </div>
                </div>
                @endforeach
            @else
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <i class="fas fa-newspaper" style="font-size: 3rem; color: #9ca3af; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280; margin-bottom: 0.5rem;">Aucune actualité disponible pour le moment</h3>
                    <p style="color: #9ca3af;">Les actualités du CSAR seront publiées ici dès qu'elles seront disponibles.</p>
                </div>
            @endif
        </div>
        
        @if(isset($latestNews) && $latestNews->count() > 0)
        <div style="text-align: center; margin-top: 3rem;">
            <a href="{{ route('news', ['locale' => app()->getLocale()]) }}" class="btn btn-success" style="padding: 0.75rem 2rem; border-radius: 50px;">
                <i class="fas fa-arrow-right"></i>
                Voir toutes les actualités
            </a>
        </div>
        @endif
    </div>
</section>

<!-- Stats Section ULTRA PRO with Counter Animation -->
<section class="stats-section-ultra slide-in-bottom" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background Elements -->
    <div style="position: absolute; top: -100px; left: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(34, 197, 94, 0.15), transparent); border-radius: 50%; filter: blur(80px); animation: pulse-orb-stats 8s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -150px; right: -150px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(59, 130, 246, 0.12), transparent); border-radius: 50%; filter: blur(100px); animation: pulse-orb-stats 10s ease-in-out infinite reverse;"></div>
    
    <!-- Particle Stars -->
    <div class="stars-container" style="position: absolute; inset: 0; overflow: hidden; pointer-events: none;">
        @for($i = 0; $i < 30; $i++)
        <div class="star" style="position: absolute; width: {{ rand(2, 4) }}px; height: {{ rand(2, 4) }}px; background: white; border-radius: 50%; top: {{ rand(0, 100) }}%; left: {{ rand(0, 100) }}%; opacity: {{ rand(30, 80) / 100 }}; animation: twinkle-star {{ rand(2, 5) }}s ease-in-out infinite; animation-delay: {{ rand(0, 50) / 10 }}s;"></div>
        @endfor
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-down">
            <div style="display: inline-block; padding: 6px 20px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.2)); border-radius: 50px; margin-bottom: 1rem; position: relative; overflow: hidden; border: 1px solid rgba(34, 197, 94, 0.3);">
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); animation: shine-badge 3s infinite;"></div>
                <span style="color: #22c55e; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.2px; position: relative; z-index: 1;">
                    <i class="fas fa-chart-line" style="margin-right: 6px; animation: pulse-chart 2s ease-in-out infinite;"></i>
                    {{ __('home.stats.badge') }}
                </span>
            </div>
            <h2 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0.8rem; color: white; text-shadow: 0 0 20px rgba(34, 197, 94, 0.2);">
                {{ __('home.stats.title') }}
            </h2>
            <p style="color: rgba(255, 255, 255, 0.75); font-size: 1rem; max-width: 650px; margin: 0 auto; line-height: 1.6;">
                {{ __('home.stats.subtitle') }}
            </p>
        </div>
        
        <!-- Stats Grid - 4 colonnes sur une ligne -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; max-width: 1400px; margin: 0 auto;">
            <!-- Stat 1: Agents -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="100" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(34, 197, 94, 0.2); border-radius: 24px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <!-- Glow Effect -->
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(34, 197, 94, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <!-- Icon -->
                <div style="position: relative; z-index: 2; margin-bottom: 1.5rem;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 40px rgba(34, 197, 94, 0.4); position: relative;">
                        <i class="fas fa-users" style="font-size: 2.5rem; color: white; animation: float-icon 3s ease-in-out infinite;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(34, 197, 94, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <!-- Counter -->
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper" style="margin-bottom: 0.5rem;">
                        <span class="counter" data-target="{{ $stats['agents'] }}" style="font-size: 4.5rem; font-weight: 900; background: linear-gradient(135deg, #22c55e, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; animation: pulse-number 2s ease-in-out infinite;">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.95); font-size: 1.4rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Agents recensés
                    </p>
                </div>
                
                <!-- Animated Border -->
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #22c55e, #10b981, #22c55e); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 2: Magasins -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="250" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(59, 130, 246, 0.2); border-radius: 24px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(59, 130, 246, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1.5rem;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 40px rgba(59, 130, 246, 0.4); position: relative;">
                        <i class="fas fa-warehouse" style="font-size: 2.5rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 0.5s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(59, 130, 246, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper" style="margin-bottom: 0.5rem;">
                        <span class="counter" data-target="{{ $stats['warehouses'] }}" style="font-size: 4.5rem; font-weight: 900; background: linear-gradient(135deg, #3b82f6, #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; animation: pulse-number 2s ease-in-out infinite;">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.95); font-size: 1.4rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Magasins de stockage
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #3b82f6, #2563eb, #3b82f6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 3: Capacité -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="400" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(124, 58, 237, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(139, 92, 246, 0.2); border-radius: 24px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(139, 92, 246, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1.5rem;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 40px rgba(139, 92, 246, 0.4); position: relative;">
                        <i class="fas fa-boxes" style="font-size: 2.5rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 1s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(139, 92, 246, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper" style="margin-bottom: 0.5rem;">
                        <span class="counter" data-target="{{ preg_replace('/[^0-9]/', '', $stats['capacity']) }}" style="font-size: 4.5rem; font-weight: 900; background: linear-gradient(135deg, #8b5cf6, #7c3aed); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; animation: pulse-number 2s ease-in-out infinite;">0</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.95); font-size: 1.4rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        {{ $stats['capacity'] }}
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #8b5cf6, #7c3aed, #8b5cf6); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
            
            <!-- Stat 4: Expérience -->
            <div class="stat-card-ultra" data-aos="zoom-in" data-aos-delay="550" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(219, 39, 119, 0.05)); backdrop-filter: blur(10px); border: 2px solid rgba(236, 72, 153, 0.2); border-radius: 24px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
                <div class="stat-glow" style="position: absolute; inset: -50px; background: radial-gradient(circle at center, rgba(236, 72, 153, 0.3), transparent); opacity: 0; filter: blur(40px); transition: opacity 0.6s ease;"></div>
                
                <div style="position: relative; z-index: 2; margin-bottom: 1.5rem;">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #ec4899, #db2777); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 40px rgba(236, 72, 153, 0.4); position: relative;">
                        <i class="fas fa-award" style="font-size: 2.5rem; color: white; animation: float-icon 3s ease-in-out infinite; animation-delay: 1.5s;"></i>
                        <div style="position: absolute; inset: -10px; border: 3px dashed rgba(236, 72, 153, 0.5); border-radius: 50%; animation: rotate-dashed 15s linear infinite;"></div>
                    </div>
                </div>
                
                <div style="position: relative; z-index: 2;">
                    <div class="counter-wrapper" style="display: flex; align-items: center; justify-content: center; gap: 0.3rem; margin-bottom: 0.5rem;">
                        <span class="counter" data-target="{{ preg_replace('/[^0-9]/', '', $stats['experience']) }}" style="font-size: 4.5rem; font-weight: 900; background: linear-gradient(135deg, #ec4899, #db2777); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; animation: pulse-number 2s ease-in-out infinite;">0</span>
                        <span style="font-size: 4.5rem; font-weight: 900; background: linear-gradient(135deg, #ec4899, #db2777); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; animation: bounce 2s ease-in-out infinite;">+</span>
                    </div>
                    <p style="color: rgba(255, 255, 255, 0.95); font-size: 1.4rem; font-weight: 600; margin-top: 0.8rem; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                        Années d'expérience
                    </p>
                </div>
                
                <div class="stat-border" style="position: absolute; inset: -2px; border-radius: 24px; background: linear-gradient(135deg, #ec4899, #db2777, #ec4899); background-size: 200% 200%; opacity: 0; z-index: -1; animation: border-flow 3s ease infinite;"></div>
            </div>
        </div>
    </div>
</section>

<!-- Galerie Section PRO -->
<section class="gallery-section-pro fade-in-right" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(34, 197, 94, 0.1), transparent); border-radius: 50%; filter: blur(60px); animation: float-orb 15s ease-in-out infinite;"></div>
    <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(59, 130, 246, 0.08), transparent); border-radius: 50%; filter: blur(80px); animation: float-orb 20s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title Section -->
        <div style="text-align: center; margin-bottom: 4rem;" data-aos="fade-up">
            <div style="display: inline-block; padding: 8px 24px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); border-radius: 50px; margin-bottom: 1.5rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); animation: shine-effect 3s infinite;"></div>
                <span style="color: #22c55e; font-weight: 700; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1.5px; position: relative; z-index: 1;">
                    <i class="fas fa-camera-retro" style="margin-right: 8px; animation: pulse-icon 2s ease-in-out infinite;"></i>
                    {{ __('home.gallery.badge') }}
                </span>
            </div>
            <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 1.2rem; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                {{ __('home.gallery.title') }}
            </h2>
            <p style="color: #6b7280; font-size: 1.2rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                {{ __('home.gallery.subtitle') }}
            </p>
        </div>
        
        @if(isset($galleryImages) && $galleryImages->count() > 0)
        <!-- SLIDER PROFESSIONNEL AVEC DIAPORAMA AUTOMATIQUE -->
        <div class="gallery-slider-container" style="position: relative; max-width: 1200px; margin: 0 auto; border-radius: 30px; overflow: hidden; box-shadow: 0 20px 80px rgba(0, 0, 0, 0.15);">
            <!-- Slider Wrapper -->
            <div class="gallery-slider" style="position: relative; height: 600px; overflow: hidden;">
                @foreach($galleryImages as $index => $image)
                <!-- Slide {{ $index + 1 }} -->
                <div class="slider-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" data-effect="{{ $index % 6 }}" onclick="changeSlide(1)" style="position: absolute; inset: 0; opacity: {{ $index === 0 ? '1' : '0' }}; transform: {{ $index === 0 ? 'scale(1)' : 'scale(1.1)' }}; transition: all 1.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;"
                
                <!-- Glow Effect Background -->
                <div class="glow-bg" style="position: absolute; inset: -30px; background: radial-gradient(circle at center, rgba(34, 197, 94, 0.4), transparent 70%); opacity: 0; filter: blur(20px); transition: all 0.6s ease; z-index: -1;"></div>
                
                <!-- Sparkle Particles -->
                <div class="sparkles" style="position: absolute; inset: 0; pointer-events: none; z-index: 10; opacity: 0; transition: opacity 0.5s ease;">
                    @for($i = 0; $i < 5; $i++)
                    <div class="sparkle" style="position: absolute; width: 4px; height: 4px; background: white; border-radius: 50%; top: {{ rand(10, 90) }}%; left: {{ rand(10, 90) }}%; animation: twinkle {{ 1 + rand(1, 3) }}s ease-in-out infinite; animation-delay: {{ $i * 0.2 }}s; box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);"></div>
                    @endfor
                </div>
                
                <!-- Image Container -->
                <div style="position: relative; width: 100%; height: 100%; overflow: hidden;">
                    <!-- Animated Corners -->
                    <div class="corner corner-tl" style="position: absolute; top: 15px; left: 15px; width: 30px; height: 30px; border-top: 3px solid #22c55e; border-left: 3px solid #22c55e; opacity: 0; transform: translate(-10px, -10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    <div class="corner corner-tr" style="position: absolute; top: 15px; right: 15px; width: 30px; height: 30px; border-top: 3px solid #22c55e; border-right: 3px solid #22c55e; opacity: 0; transform: translate(10px, -10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    <div class="corner corner-bl" style="position: absolute; bottom: 15px; left: 15px; width: 30px; height: 30px; border-bottom: 3px solid #22c55e; border-left: 3px solid #22c55e; opacity: 0; transform: translate(-10px, 10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    <div class="corner corner-br" style="position: absolute; bottom: 15px; right: 15px; width: 30px; height: 30px; border-bottom: 3px solid #22c55e; border-right: 3px solid #22c55e; opacity: 0; transform: translate(10px, 10px); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); z-index: 5;"></div>
                    
                    <!-- Image with Multiple Layers -->
                    <div style="position: relative; width: 100%; height: 100%;">
                        <!-- Main Image -->
                        <img src="{{ asset('storage/' . $image->file_path) }}" 
                             alt="{{ $image->alt_text ?? $image->title }}" 
                             style="width: 100%; height: 100%; object-fit: cover; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1); filter: brightness(0.95) contrast(1.05);"
                             class="gallery-image-hover">
                        
                        <!-- Color Overlay Effect -->
                        <div class="color-overlay" style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(59, 130, 246, 0.2)); mix-blend-mode: overlay; opacity: 0; transition: opacity 0.5s ease;"></div>
                    </div>
                    
                    <!-- Gradient Overlay -->
                    <div class="gallery-overlay" style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.1) 50%, rgba(0, 0, 0, 0.8) 100%); opacity: 0.8; transition: all 0.5s ease;"></div>
                    
                    <!-- Scan Line Effect -->
                    <div class="scan-line" style="position: absolute; top: -100%; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.8), transparent); opacity: 0; transition: all 0.5s ease;"></div>
                    
                    <!-- Content Overlay -->
                    <div class="gallery-content" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1.5rem; transform: translateY(20px); opacity: 0; transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); z-index: 2;">
                        <h3 style="color: white; font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 2px 8px rgba(0,0,0,0.3); transform: translateX(-20px); transition: all 0.5s ease 0.1s;">
                            {{ $image->title }}
                        </h3>
                        @if($image->category)
                        <div style="display: inline-block; padding: 4px 12px; background: rgba(34, 197, 94, 0.9); border-radius: 20px; font-size: 0.75rem; color: white; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; transform: scale(0.8); transition: all 0.4s ease 0.2s; box-shadow: 0 0 20px rgba(34, 197, 94, 0.6);">
                            {{ $image->category }}
                        </div>
                        @endif
                        @if($image->description)
                        <p style="color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; margin: 0.5rem 0 0; line-height: 1.5; transform: translateY(10px); transition: all 0.5s ease 0.3s;">
                            {{ Str::limit($image->description, 80) }}
                        </p>
                        @endif
                    </div>
                    
                    <!-- Zoom Icon -->
                    <div class="zoom-icon-gallery" style="position: absolute; top: 20px; right: 20px; width: 50px; height: 50px; background: rgba(255, 255, 255, 0.95); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transform: scale(0) rotate(-180deg); transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2); z-index: 3;">
                        <i class="fas fa-search-plus" style="color: #22c55e; font-size: 1.2rem; animation: pulse-zoom 2s ease-in-out infinite;"></i>
                    </div>
                    
                    <!-- Border Animation -->
                    <div class="border-animation" style="position: absolute; inset: -3px; border-radius: 24px; background: linear-gradient(135deg, #22c55e, #3b82f6, #8b5cf6, #ec4899, #22c55e); background-size: 400% 400%; opacity: 0; z-index: -1; animation: rotate-border-wave 6s ease-in-out infinite;"></div>
                    
                    <!-- Ripple Effect -->
                    <div class="ripple-effect-gallery" style="position: absolute; inset: 0; opacity: 0; transition: all 0.8s ease;"></div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Indicateurs minimalistes (sans boutons) -->
        <div class="slider-dots" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 100;">
            @foreach($galleryImages as $index => $image)
            <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" style="width: 8px; height: 8px; border-radius: 50%; background: {{ $index === 0 ? '#22c55e' : 'rgba(255, 255, 255, 0.4)' }}; transition: all 0.4s ease; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);"></div>
            @endforeach
        </div>
        
        <!-- Progress Bar -->
        <div class="slider-progress-container" style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: rgba(255, 255, 255, 0.3); z-index: 100; overflow: hidden;">
            <div class="slider-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #22c55e, #10b981); transition: width 0.1s linear;"></div>
        </div>
    </div>
        @else
        <div style="text-align: center; padding: 4rem 0;">
            <i class="fas fa-images" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <p style="color: #6b7280; font-size: 1.1rem;">Aucune image disponible pour le moment</p>
        </div>
        @endif
    </div>
</section>

<!-- Lightbox Modal -->
<div id="gallery-lightbox" class="gallery-lightbox" onclick="closeLightbox()" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.95); backdrop-filter: blur(10px); animation: fadeIn 0.3s ease;">
    <span onclick="closeLightbox()" style="position: absolute; top: 30px; right: 50px; color: #fff; font-size: 50px; font-weight: 300; cursor: pointer; z-index: 10000; transition: all 0.3s ease; text-shadow: 0 0 10px rgba(0,0,0,0.5);" onmouseover="this.style.color='#22c55e'" onmouseout="this.style.color='#fff'">&times;</span>
    
    <!-- Navigation Arrows -->
    <button onclick="changeImage(-1); event.stopPropagation();" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.3); color: white; font-size: 2rem; padding: 1rem 1.5rem; cursor: pointer; border-radius: 50%; transition: all 0.3s ease; z-index: 10000; backdrop-filter: blur(10px);" onmouseover="this.style.background='rgba(34, 197, 94, 0.9)'; this.style.borderColor='#22c55e'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.3)'">
        <i class="fas fa-chevron-left"></i>
    </button>
    
    <button onclick="changeImage(1); event.stopPropagation();" style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.3); color: white; font-size: 2rem; padding: 1rem 1.5rem; cursor: pointer; border-radius: 50%; transition: all 0.3s ease; z-index: 10000; backdrop-filter: blur(10px);" onmouseover="this.style.background='rgba(34, 197, 94, 0.9)'; this.style.borderColor='#22c55e'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='rgba(255, 255, 255, 0.3)'">
        <i class="fas fa-chevron-right"></i>
    </button>
    
    <!-- Image Container -->
    <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 80px 120px;">
        <img id="lightbox-img" src="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5); animation: zoomIn 0.3s ease;">
    </div>
    
    <!-- Image Info -->
    <div id="lightbox-info" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 2rem; background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent); color: white; text-align: center;">
        <h3 id="lightbox-title" style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.5rem;"></h3>
        <p id="lightbox-category" style="font-size: 1rem; opacity: 0.9; margin-bottom: 0.5rem;"></p>
        <p id="lightbox-description" style="font-size: 1rem; opacity: 0.8; max-width: 800px; margin: 0 auto;"></p>
    </div>
    
    <!-- Image Counter -->
    <div style="position: absolute; top: 30px; left: 50%; transform: translateX(-50%); background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 10px 25px; border-radius: 50px; color: white; font-weight: 600; border: 2px solid rgba(255, 255, 255, 0.2);">
        <span id="image-counter">1 / 1</span>
    </div>
</div>

<style>
/* ========================================
   ULTRA LUXE GALLERY PRO ANIMATIONS
   ======================================== */

/* Floating Card Animation */
@keyframes float-card {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Sparkle/Twinkle Effect */
@keyframes twinkle {
    0%, 100% {
        opacity: 0;
        transform: scale(0);
    }
    50% {
        opacity: 1;
        transform: scale(1.5);
    }
}

/* Pulse Zoom Icon */
@keyframes pulse-zoom {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.15);
    }
}

/* ======================================
   STYLES POUR TRANSITIONS DYNAMIQUES
   ====================================== */

/* Container du slider avec perspective 3D */
.gallery-slider-container {
    perspective: 2000px;
    transform-style: preserve-3d;
}

.gallery-slider {
    transform-style: preserve-3d;
}

/* Slides avec transitions fluides */
.slider-slide {
    transform-style: preserve-3d;
    backface-visibility: hidden;
    will-change: transform, opacity, filter;
    transition: all 1.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.slider-slide.active {
    z-index: 2;
}

.slider-slide:not(.active) {
    z-index: 1;
    pointer-events: none;
}

/* Animation du hover sur le slider */
.slider-slide:hover .gallery-image-hover {
    transform: scale(1.05);
    filter: brightness(1.05) contrast(1.1);
}

.slider-slide:hover .color-overlay {
    opacity: 0.3;
}

/* Curseur pointer pour indiquer la cliquabilité */
.slider-slide {
    cursor: pointer;
    transition: all 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Dots avec animations fluides */
.slider-dot {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slider-dot.active {
    transform: scale(1.3);
}

/* Rotating Border Wave */
@keyframes rotate-border-wave {
    0% {
        background-position: 0% 50%;
        transform: rotate(0deg);
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
        transform: rotate(360deg);
    }
}

/* Scan Line Effect */
@keyframes scan-line {
    0% {
        top: -100%;
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    100% {
        top: 100%;
        opacity: 0;
    }
}

/* Ripple Pulse */
@keyframes ripple-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7),
                    0 0 0 0 rgba(34, 197, 94, 0.7);
    }
    50% {
        box-shadow: 0 0 0 15px rgba(34, 197, 94, 0),
                    0 0 0 30px rgba(34, 197, 94, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0),
                    0 0 0 0 rgba(34, 197, 94, 0);
    }
}

/* Gallery Item Hover Effects */
.gallery-item-pro:hover {
    transform: translateY(-20px) scale(1.03) !important;
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.35) !important;
    animation-play-state: paused !important;
}

.gallery-item-pro:hover .gallery-image-hover {
    transform: scale(1.15) rotate(2deg);
    filter: brightness(1.1) contrast(1.1) saturate(1.2);
}

.gallery-item-pro:hover .gallery-overlay {
    opacity: 0.95 !important;
    background: linear-gradient(to bottom, rgba(34, 197, 94, 0.1) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.9) 100%) !important;
}

.gallery-item-pro:hover .gallery-content {
    transform: translateY(0) !important;
    opacity: 1 !important;
}

.gallery-item-pro:hover .gallery-content h3 {
    transform: translateX(0) !important;
}

.gallery-item-pro:hover .gallery-content div {
    transform: scale(1) !important;
}

.gallery-item-pro:hover .gallery-content p {
    transform: translateY(0) !important;
}

.gallery-item-pro:hover .zoom-icon-gallery {
    opacity: 1 !important;
    transform: scale(1) rotate(0deg) !important;
    animation: ripple-pulse 2s infinite;
}

.gallery-item-pro:hover .border-animation {
    opacity: 1 !important;
}

.gallery-item-pro:hover .glow-bg {
    opacity: 1 !important;
}

.gallery-item-pro:hover .sparkles {
    opacity: 1 !important;
}

.gallery-item-pro:hover .corner {
    opacity: 1 !important;
    transform: translate(0, 0) !important;
}

.gallery-item-pro:hover .color-overlay {
    opacity: 0.3 !important;
}

.gallery-item-pro:hover .scan-line {
    animation: scan-line 2s ease-in-out;
    opacity: 1;
}

.gallery-item-pro:hover .ripple-effect-gallery {
    opacity: 1;
    background: radial-gradient(circle at center, transparent 0%, rgba(34, 197, 94, 0.2) 50%, transparent 100%);
    animation: ripple-expand 1.5s ease-out;
}

@keyframes ripple-expand {
    0% {
        transform: scale(0);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Background Animations */
@keyframes shine-effect {
    0% { left: -100%; }
    50%, 100% { left: 200%; }
}

@keyframes float-orb {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, -30px) scale(1.1); }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes zoomIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

@keyframes pulse-icon {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* 3D Perspective Effect on Grid */
.gallery-grid-pro {
    perspective: 1000px;
}

/* Individual Card Entrance Animations */
.gallery-item-pro {
    will-change: transform;
}

/* Corner Animations - Staggered */
.gallery-item-pro:hover .corner-tl {
    transition-delay: 0s !important;
}

.gallery-item-pro:hover .corner-tr {
    transition-delay: 0.1s !important;
}

.gallery-item-pro:hover .corner-br {
    transition-delay: 0.2s !important;
}

.gallery-item-pro:hover .corner-bl {
    transition-delay: 0.3s !important;
}

/* Smooth transitions */
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .gallery-grid-pro {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important;
        gap: 1.5rem !important;
    }
    
    .gallery-item-pro {
        animation: float-card 4s ease-in-out infinite !important;
    }
    
    .gallery-item-pro:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
    
    .gallery-lightbox button {
        padding: 0.5rem 1rem !important;
        font-size: 1.5rem !important;
    }
    
    .gallery-lightbox > div:first-of-type {
        padding: 60px 20px !important;
    }
    
    .corner {
        display: none !important;
    }
}

@media (max-width: 480px) {
    .gallery-grid-pro {
        grid-template-columns: 1fr !important;
    }
    
    .sparkles {
        display: none !important;
    }
}

/* Performance Optimizations */
.gallery-item-pro,
.gallery-image-hover,
.border-animation,
.glow-bg {
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* Prefers Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .gallery-item-pro,
    .sparkle,
    .scan-line,
    .border-animation {
        animation: none !important;
    }
    
    .gallery-item-pro:hover {
        transform: none !important;
    }
}

/* ========================================
   SERVICES SECTION ULTRA ANIMATIONS
   ======================================== */

/* Float Shape Background */
@keyframes float-shape {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -30px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

/* Shine Badge Effect */
@keyframes shine-badge {
    0% { left: -100%; }
    50%, 100% { left: 200%; }
}

/* Ring Bell Icon */
@keyframes ring-bell {
    0%, 100% {
        transform: rotate(0deg);
    }
    10%, 30% {
        transform: rotate(-10deg);
    }
    20%, 40% {
        transform: rotate(10deg);
    }
    50% {
        transform: rotate(0deg);
    }
}

/* Float Service Cards */
@keyframes float-service {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-15px);
    }
}

/* Border Rotate Effect */
@keyframes border-rotate {
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

/* Bounce Icon */
@keyframes bounce-icon {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

/* Rotate Dashed Circle */
@keyframes rotate-dashed {
    0% {
        transform: rotate(0deg);
        opacity: 0;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        transform: rotate(360deg);
        opacity: 0;
    }
}

/* Pulse Search Icon */
@keyframes pulse-search {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Service Card Hover Effects */
.service-card-ultra:hover {
    transform: translateY(-20px) scale(1.05) !important;
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.3) !important;
    animation-play-state: paused !important;
}

.service-card-ultra:hover .service-border {
    opacity: 1 !important;
}

.service-card-ultra:hover .service-glow {
    opacity: 1 !important;
}

.service-card-ultra:hover .service-icon-ultra {
    transform: scale(1.15) rotate(360deg);
    box-shadow: 0 15px 40px rgba(34, 197, 94, 0.5) !important;
}

.service-card-ultra:hover .service-icon-ultra div {
    opacity: 1 !important;
}

.service-card-ultra:hover h3 {
    color: #22c55e !important;
    transform: scale(1.05);
}

/* ========================================
   NEWS SECTION ULTRA ANIMATIONS
   ======================================== */

/* Float Particle Slow */
@keyframes float-particle-slow {
    0%, 100% {
        transform: translate(0, 0);
    }
    50% {
        transform: translate(50px, -50px);
    }
}

/* Pulse News Icon (badge) */
@keyframes pulse-news {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
}

/* Float News Cards */
@keyframes float-news {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-12px);
    }
}

/* Gradient News Border */
@keyframes gradient-news {
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

/* Pulse News Icon (no image) */
@keyframes pulse-news-icon {
    0%, 100% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.5;
    }
}

/* News Card Hover Effects */
.news-card-ultra:hover {
    transform: translateY(-15px) scale(1.03) !important;
    box-shadow: 0 25px 70px rgba(59, 130, 246, 0.3) !important;
    animation-play-state: paused !important;
}

.news-card-ultra:hover > div:first-child {
    opacity: 1 !important;
}

.news-card-ultra:hover .news-image-hover {
    transform: scale(1.1) rotate(2deg);
}

.news-card-ultra:hover h3 {
    color: #3b82f6 !important;
}

.news-card-ultra:hover a {
    color: #22c55e !important;
}

.news-card-ultra:hover a i {
    transform: translateX(5px) !important;
}

.news-card-ultra:hover a div {
    width: 100% !important;
}

/* ========================================
   RESPONSIVE FOR NEW SECTIONS
   ======================================== */

@media (max-width: 768px) {
    .services-section-ultra .services-grid {
        grid-template-columns: 1fr !important;
    }
    
    .service-card-ultra {
        animation-duration: 6s !important;
    }
    
    .news-section-ultra > div > div:last-child {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 480px) {
    .service-card-ultra:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
    
    .news-card-ultra:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
}

/* ========================================
   STATS SECTION ULTRA ANIMATIONS
   ======================================== */

/* Pulse Orb Stats */
@keyframes pulse-orb-stats {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
}

/* Twinkle Stars */
@keyframes twinkle-star {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 1;
        transform: scale(1.5);
    }
}

/* Pulse Chart Icon */
@keyframes pulse-chart {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Float Icon */
@keyframes float-icon {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Pulse Number Animation - Ultra Smooth */
@keyframes pulse-number {
    0%, 100% {
        transform: scale(1);
        filter: brightness(1);
    }
    50% {
        transform: scale(1.05);
        filter: brightness(1.2);
    }
}

/* Bounce Animation */
@keyframes bounce {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    25% {
        transform: translateY(-8px) rotate(5deg);
    }
    50% {
        transform: translateY(0) rotate(0deg);
    }
    75% {
        transform: translateY(-4px) rotate(-5deg);
    }
}

/* Border Flow */
@keyframes border-flow {
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

/* Counter Number Pulse */
@keyframes counter-pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* Stat Card Hover Effects */
.stat-card-ultra:hover {
    transform: translateY(-15px) scale(1.05) !important;
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.4) !important;
}

.stat-card-ultra:hover .stat-glow {
    opacity: 1 !important;
}

.stat-card-ultra:hover .stat-border {
    opacity: 1 !important;
}

.stat-card-ultra:hover .counter {
    animation: counter-pulse 0.6s ease !important;
}

/* Counter Animation State */
.counter.counting {
    animation: counter-pulse 0.3s ease;
    transform: scale(1.05);
}

.counter.completed {
    animation: completion-bounce 0.5s ease;
}

@keyframes completion-bounce {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}

/* Responsive Stats */
/* Tablettes - 2 colonnes */
@media (max-width: 1024px) {
    .stats-section-ultra > div > div:last-child {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 2rem !important;
    }
}

/* Mobile - 1 colonne */
@media (max-width: 768px) {
    .stats-section-ultra > div > div:last-child {
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
    }
    
    .stat-card-ultra {
        padding: 2rem 1.5rem !important;
    }
    
    .counter {
        font-size: 3rem !important;
        line-height: 1.1 !important;
        display: block !important;
        text-align: center !important;
    }
    
    .counter-wrapper {
        margin-bottom: 1rem !important;
    }
    
    /* Forcer l'affichage des compteurs sur mobile */
    .counter[data-target] {
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
    }
}

@media (max-width: 480px) {
    .stats-section-ultra > div > div:last-child {
        grid-template-columns: 1fr !important;
    }
    
    .stat-card-ultra:hover {
        transform: translateY(-10px) scale(1.02) !important;
    }
}

/* ========================================
   SLIDER NAVIGATION STYLES
   ======================================== */

.slider-nav:hover {
    background: #22c55e !important;
    transform: translateY(-50%) scale(1.1) !important;
    box-shadow: 0 12px 32px rgba(34, 197, 94, 0.4) !important;
}

.slider-nav:hover i {
    color: white !important;
    transform: scale(1.2);
}

.slider-nav:active {
    transform: translateY(-50%) scale(0.95) !important;
}

.slider-dot:hover {
    transform: scale(1.4);
    background: #22c55e !important;
    border-color: #22c55e !important;
}

.slider-dot.active {
    width: 40px !important;
    border-radius: 10px !important;
    background: #22c55e !important;
    border-color: #22c55e !important;
}

/* Ken Burns Effect - Zoom Animation on Active Slide */
.slider-slide.active .gallery-image-hover {
    animation: kenBurns 20s ease-in-out infinite;
}

@keyframes kenBurns {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Content Fade In on Active Slide */
.slider-slide.active .gallery-content {
    opacity: 1 !important;
    transform: translateY(0) !important;
}

.slider-slide.active .gallery-content h3 {
    transform: translateX(0) !important;
}

.slider-slide.active .gallery-content div {
    transform: scale(1) !important;
}

.slider-slide.active .gallery-content p {
    transform: translateY(0) !important;
}

</style>

<script>
@if(isset($galleryImages) && $galleryImages->count() > 0)
const galleryData = [
    @foreach($galleryImages as $image)
    {
        src: "{{ asset('storage/' . $image->file_path) }}",
        title: "{{ $image->title }}",
        category: "{{ $image->category ?? '' }}",
        description: "{{ $image->description ?? '' }}"
    },
    @endforeach
];

let currentImageIndex = 0;

function openLightbox(index) {
    currentImageIndex = index;
    updateLightboxImage();
    document.getElementById('gallery-lightbox').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('gallery-lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function changeImage(direction) {
    currentImageIndex += direction;
    if (currentImageIndex < 0) currentImageIndex = galleryData.length - 1;
    if (currentImageIndex >= galleryData.length) currentImageIndex = 0;
    updateLightboxImage();
}

function updateLightboxImage() {
    const image = galleryData[currentImageIndex];
    document.getElementById('lightbox-img').src = image.src;
    document.getElementById('lightbox-title').textContent = image.title;
    document.getElementById('lightbox-category').textContent = image.category;
    document.getElementById('lightbox-description').textContent = image.description;
    document.getElementById('image-counter').textContent = `${currentImageIndex + 1} / ${galleryData.length}`;
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('gallery-lightbox');
    if (lightbox.style.display === 'block') {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') changeImage(-1);
        if (e.key === 'ArrowRight') changeImage(1);
    }
});
@endif

// ========================================
// ANIMATED COUNTERS (EFFECT CHRONO)
// ========================================

function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        
        const current = Math.floor(easeOutQuart * (end - start) + start);
        element.textContent = current.toLocaleString('fr-FR');
        
        // Add pulse class during counting
        if (progress < 1) {
            element.classList.add('counting');
            window.requestAnimationFrame(step);
        } else {
            element.classList.remove('counting');
            element.textContent = end.toLocaleString('fr-FR');
        }
    };
    window.requestAnimationFrame(step);
}

// Intersection Observer for triggering counter animation when visible
const observerOptions = {
    threshold: 0.3,
    rootMargin: '0px'
};

const counters = document.querySelectorAll('.counter');
let hasAnimated = false;

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !hasAnimated) {
            hasAnimated = true;
            
            // Start all counters with slight delays
            counters.forEach((counter, index) => {
                const target = parseInt(counter.getAttribute('data-target'));
                
                setTimeout(() => {
                    animateCounter(counter, 0, target, 2000); // 2 seconds animation
                }, index * 200); // 200ms delay between each counter
            });
        }
    });
}, observerOptions);

// Observe the stats section
const statsSection = document.querySelector('.stats-section-ultra');
if (statsSection) {
    counterObserver.observe(statsSection);
}
</script>

<!-- Partners Section with PRO Effects -->
@if(isset($partners) && $partners->count() > 0)
<section class="partners-section-pro blur-in" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Animated Background Particles -->
    <div class="particles-container">
        @for($i = 0; $i < 20; $i++)
        <div class="particle" style="
            position: absolute;
            width: {{ rand(4, 12) }}px;
            height: {{ rand(4, 12) }}px;
            background: linear-gradient(135deg, rgba(34, 197, 94, {{ rand(10, 30) / 100 }}), rgba(59, 130, 246, {{ rand(10, 30) / 100 }}));
            border-radius: 50%;
            left: {{ rand(0, 100) }}%;
            top: {{ rand(0, 100) }}%;
            animation: float-particle {{ rand(15, 30) }}s ease-in-out infinite;
            animation-delay: {{ rand(0, 10) }}s;
            filter: blur({{ rand(1, 3) }}px);
        "></div>
        @endfor
    </div>
    
    <!-- Animated Gradient Orbs -->
    <div class="gradient-orb orb-1"></div>
    <div class="gradient-orb orb-2"></div>
    <div class="gradient-orb orb-3"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Title with PRO Animation -->
        <div style="text-align: center; margin-bottom: 4rem;" class="partners-title-pro">
            <div class="badge-pulse" style="display: inline-block; padding: 8px 20px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%); border-radius: 50px; margin-bottom: 1rem; position: relative; overflow: hidden;">
                <div class="badge-shine"></div>
                <span style="color: #22c55e; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; position: relative; z-index: 1;">
                    <i class="fas fa-handshake pulse-icon"></i> Nos Partenaires
                </span>
            </div>
            <h2 class="gradient-text-animated" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; position: relative;">
                Ensemble pour la Résilience
                <div class="text-glow"></div>
            </h2>
            <p class="subtitle-fade" style="color: #6b7280; font-size: 1.15rem; max-width: 700px; margin: 0 auto; line-height: 1.8;">
                Avec nos partenaires stratégiques, nous œuvrons pour la sécurité alimentaire et le développement durable au Sénégal
            </p>
        </div>
        
        <!-- Partners Grid with PRO 3D Effects -->
        <div class="partners-grid-pro" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2.5rem; margin-bottom: 4rem; perspective: 1000px;">
            @foreach($partners as $index => $partner)
            <div class="partner-card-pro" data-index="{{ $index }}" style="position: relative; transform-style: preserve-3d;">
                <a href="{{ $partner->website ?: '#' }}" target="_blank" rel="noopener noreferrer" style="text-decoration: none; display: block;">
                    <div class="card-inner" style="background: #ffffff; border-radius: 28px; padding: 3rem 2rem; text-align: center; position: relative; overflow: hidden; border: 2px solid transparent; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08); min-height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: center; transform-style: preserve-3d;">
                        <!-- Ripple Effect on Hover -->
                        <div class="ripple-effect"></div>
                        
                        <!-- Animated Gradient Border -->
                        <div class="animated-gradient-border"></div>
                        
                        <!-- Shine Effect -->
                        <div class="shine-effect"></div>
                        
                        <!-- Background Pattern -->
                        <div class="card-pattern"></div>
                        
                        <!-- Partner Logo -->
                        <div style="position: relative; z-index: 2; margin-bottom: 1.5rem;">
                            @php
                                $logoFile = null;
                                switch(strtolower($partner->name)) {
                                    case 'fsrp':
                                        $logoFile = 'fsrp.png';
                                        break;
                                    case 'jica':
                                        $logoFile = 'jica.jpg';
                                        break;
                                    case 'ansd':
                                        $logoFile = 'ANSD.png';
                                        break;
                                    case 'fongip':
                                        $logoFile = 'fongip.jpeg';
                                        break;
                                }
                            @endphp
                            
                            @if($logoFile && file_exists(public_path('images/partners/' . $logoFile)))
                            <div style="width: 140px; height: 140px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 20px; padding: 1.5rem; transition: all 0.4s ease; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                                <img src="{{ asset('images/partners/' . $logoFile) }}" 
                                     alt="{{ $partner->name }}" 
                                     title="{{ $partner->name }}"
                                     class="partner-logo-img"
                                     style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; transition: all 0.4s ease;">
                            </div>
                            @else
                            <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.4s ease;">
                                <i class="fas fa-handshake" style="font-size: 3rem; color: white;"></i>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Partner Info -->
                        <div style="position: relative; z-index: 2;">
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; transition: color 0.3s ease;">
                                {{ $partner->name }}
                            </h3>
                            <p style="font-size: 0.85rem; color: #6b7280; margin: 0; transition: color 0.3s ease;">
                                {{ $partner->organization }}
                            </p>
                            
                            <!-- Badge Type -->
                            <div style="margin-top: 1rem; display: inline-block; padding: 4px 12px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%); border-radius: 20px;">
                                <span style="font-size: 0.75rem; color: #22c55e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                    @if($partner->type === 'agency') Agence
                                    @elseif($partner->type === 'institution') Institution
                                    @elseif($partner->type === 'ong') ONG
                                    @else Partenaire
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <!-- Hover Icon -->
                        <div class="hover-icon" style="position: absolute; bottom: 20px; right: 20px; width: 40px; height: 40px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transform: scale(0) rotate(-180deg); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); z-index: 3;">
                            <i class="fas fa-arrow-right" style="color: white; font-size: 1rem;"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        
        <!-- View All Partners Button with Animation -->
        <div style="text-align: center;" data-aos="fade-up" data-aos-delay="400">
            <a href="{{ route('partners.index') }}" class="btn-partners-modern" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 40px; background: linear-gradient(135deg, #22c55e 0%, #10b981 100%); color: white; font-weight: 700; font-size: 1rem; border-radius: 50px; text-decoration: none; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                <span style="position: relative; z-index: 2; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-users"></i>
                    Découvrir tous nos partenaires
                    <i class="fas fa-arrow-right" style="transition: transform 0.3s ease;"></i>
                </span>
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #10b981 0%, #059669 100%); opacity: 0; transition: opacity 0.3s ease;"></div>
            </a>
        </div>
    </div>
</section>

<style>
/* ========================================
   ULTRA PROFESSIONAL PARTNERS SECTION
   ======================================== */

/* Animated Particles */
.particles-container {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

@keyframes float-particle {
    0%, 100% {
        transform: translate(0, 0) scale(1);
        opacity: 0.3;
    }
    25% {
        transform: translate(100px, -100px) scale(1.2);
        opacity: 0.6;
    }
    50% {
        transform: translate(-50px, -150px) scale(0.8);
        opacity: 0.4;
    }
    75% {
        transform: translate(-100px, -50px) scale(1.1);
        opacity: 0.5;
    }
}

/* Animated Gradient Orbs */
.gradient-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: pulse-orb 8s ease-in-out infinite;
}

.orb-1 {
    top: -10%;
    right: -5%;
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.3), rgba(16, 185, 129, 0.2));
    animation-delay: 0s;
}

.orb-2 {
    bottom: -15%;
    left: -10%;
    width: 600px;
    height: 600px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(34, 197, 94, 0.2));
    animation-delay: 2s;
}

.orb-3 {
    top: 40%;
    right: 30%;
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(59, 130, 246, 0.15));
    animation-delay: 4s;
}

@keyframes pulse-orb {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(50px, 30px) scale(1.1);
    }
}

/* Title Animations */
.badge-pulse {
    animation: pulse-badge 2s ease-in-out infinite;
}

@keyframes pulse-badge {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.badge-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
    animation: shine-badge 3s ease-in-out infinite;
}

@keyframes shine-badge {
    0% {
        left: -100%;
    }
    50%, 100% {
        left: 200%;
    }
}

.pulse-icon {
    animation: pulse-icon 1.5s ease-in-out infinite;
}

@keyframes pulse-icon {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.2);
    }
}

/* Gradient Text Animation */
.gradient-text-animated {
    background: linear-gradient(135deg, #1f2937 0%, #22c55e 50%, #3b82f6 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradient-flow 3s ease infinite;
}

@keyframes gradient-flow {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.text-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #22c55e, #3b82f6);
    filter: blur(40px);
    opacity: 0.2;
    animation: glow-pulse 2s ease-in-out infinite;
    z-index: -1;
}

@keyframes glow-pulse {
    0%, 100% {
        opacity: 0.2;
    }
    50% {
        opacity: 0.4;
    }
}

.subtitle-fade {
    animation: fade-in-up 1s ease 0.5s backwards;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* PRO Partner Card Effects */
.partner-card-pro {
    animation: card-entrance 0.6s ease backwards;
    animation-delay: calc(var(--index, 0) * 0.1s);
}

@keyframes card-entrance {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.partner-card-pro:nth-child(1) { --index: 1; }
.partner-card-pro:nth-child(2) { --index: 2; }
.partner-card-pro:nth-child(3) { --index: 3; }
.partner-card-pro:nth-child(4) { --index: 4; }

/* 3D Tilt Effect on Hover */
.partner-card-pro:hover .card-inner {
    transform: translateY(-20px) scale(1.03) rotateX(5deg);
    box-shadow: 0 30px 80px rgba(34, 197, 94, 0.25), 0 0 0 1px rgba(34, 197, 94, 0.1);
}

/* Ripple Effect */
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34, 197, 94, 0.4) 0%, transparent 70%);
    width: 0;
    height: 0;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    pointer-events: none;
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.partner-card-pro:hover .ripple-effect {
    width: 500px;
    height: 500px;
    opacity: 1;
}

/* Animated Gradient Border */
.animated-gradient-border {
    position: absolute;
    inset: -2px;
    border-radius: 28px;
    background: linear-gradient(135deg, #22c55e, #3b82f6, #8b5cf6, #22c55e);
    background-size: 300% 300%;
    opacity: 0;
    animation: rotate-gradient 4s linear infinite;
    z-index: -1;
    transition: opacity 0.5s ease;
}

.partner-card-pro:hover .animated-gradient-border {
    opacity: 1;
}

@keyframes rotate-gradient {
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

/* Shine Effect Diagonal */
.shine-effect {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
    transform: translateX(-100%) translateY(-100%);
    transition: transform 0.8s ease;
    pointer-events: none;
}

.partner-card-pro:hover .shine-effect {
    transform: translateX(50%) translateY(50%);
}

/* Background Pattern */
.card-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 30%, rgba(34, 197, 94, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.03) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.5s ease;
    z-index: 0;
}

.partner-card-pro:hover .card-pattern {
    opacity: 1;
}

/* Logo Animation */
.partner-card-pro:hover .partner-logo-img {
    transform: scale(1.15) rotate(360deg);
    filter: drop-shadow(0 10px 20px rgba(34, 197, 94, 0.3));
}

.partner-logo-img {
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover Icon with Bounce */
.partner-card-pro:hover .hover-icon {
    opacity: 1;
    transform: scale(1) rotate(0deg);
    animation: bounce-icon 0.5s ease;
}

@keyframes bounce-icon {
    0%, 100% {
        transform: scale(1) rotate(0deg);
    }
    50% {
        transform: scale(1.2) rotate(10deg);
    }
}

.hover-icon {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #22c55e, #10b981);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0) rotate(-180deg);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 3;
    box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
}

/* Title Color Change */
.partner-card-pro:hover h3 {
    color: #22c55e !important;
    text-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

/* Badge Animation */
.partner-card-pro:hover .badge-type-animated {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(34, 197, 94, 0.3);
}

/* Button PRO Effects */
.btn-partners-modern {
    position: relative;
    overflow: hidden;
}

.btn-partners-modern::before {
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

.btn-partners-modern:hover::before {
    width: 300px;
    height: 300px;
}

.btn-partners-modern:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 20px 60px rgba(34, 197, 94, 0.5);
}

.btn-partners-modern:hover > span > i:last-child {
    transform: translateX(8px);
    animation: arrow-bounce 0.6s ease infinite;
}

@keyframes arrow-bounce {
    0%, 100% {
        transform: translateX(8px);
    }
    50% {
        transform: translateX(12px);
    }
}

.btn-partners-modern:hover > div {
    opacity: 1;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .partner-card-pro:hover .card-inner {
        transform: translateY(-10px) scale(1.02);
    }
    
    .gradient-orb {
        width: 300px !important;
        height: 300px !important;
    }
}

/* Loading Animation */
@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

/* Smooth Entrance for All Elements */
.partners-section-pro > * {
    animation: fade-in 0.8s ease backwards;
}
</style>
@endif

<!-- ===================================================
     DIAPORAMA AUTOMATIQUE ULTRA-DYNAMIQUE AVEC EFFETS 3D
     =================================================== -->
<script>
@if(isset($galleryImages) && $galleryImages->count() > 0)
// Variables du diaporama
let currentSlideIndex = 0;
let totalSlides = {{ $galleryImages->count() }};
let autoplayInterval = null;
let isAutoplayRunning = true;
let autoplayDelay = 4000; // 4 secondes par image

// 6 effets de transition différents ultra-dynamiques
const transitionEffects = [
    // Effet 1: Zoom + Rotation
    {
        out: { opacity: 0, transform: 'scale(0.8) rotate(-5deg)', filter: 'blur(10px)' },
        in: { opacity: 1, transform: 'scale(1) rotate(0deg)', filter: 'blur(0px)' }
    },
    // Effet 2: Slide de gauche avec rotation 3D
    {
        out: { opacity: 0, transform: 'translateX(100%) rotateY(45deg) scale(0.8)' },
        in: { opacity: 1, transform: 'translateX(0) rotateY(0deg) scale(1)' }
    },
    // Effet 3: Flip vertical
    {
        out: { opacity: 0, transform: 'perspective(1000px) rotateX(-90deg) scale(0.9)' },
        in: { opacity: 1, transform: 'perspective(1000px) rotateX(0deg) scale(1)' }
    },
    // Effet 4: Explosion de zoom
    {
        out: { opacity: 0, transform: 'scale(1.5)', filter: 'blur(20px) brightness(1.5)' },
        in: { opacity: 1, transform: 'scale(1)', filter: 'blur(0px) brightness(1)' }
    },
    // Effet 5: Rotation 3D complète
    {
        out: { opacity: 0, transform: 'perspective(1000px) rotateY(180deg) scale(0.7)' },
        in: { opacity: 1, transform: 'perspective(1000px) rotateY(0deg) scale(1)' }
    },
    // Effet 6: Fondu avec rotation et échelle
    {
        out: { opacity: 0, transform: 'scale(1.2) rotate(10deg)', filter: 'blur(15px)' },
        in: { opacity: 1, transform: 'scale(1) rotate(0deg)', filter: 'blur(0px)' }
    }
];

// Fonction pour changer de slide avec effets variés
function changeSlide(direction) {
    const slides = document.querySelectorAll('.slider-slide');
    const dots = document.querySelectorAll('.slider-dot');
    const progressBar = document.querySelector('.slider-progress-bar');
    
    if (slides.length === 0) return;
    
    const currentSlide = slides[currentSlideIndex];
    const currentEffect = transitionEffects[currentSlideIndex % 6];
    
    // Retirer l'active de la slide actuelle avec effet
    currentSlide.classList.remove('active');
    Object.assign(currentSlide.style, currentEffect.out);
    dots[currentSlideIndex].classList.remove('active');
    dots[currentSlideIndex].style.background = 'rgba(255, 255, 255, 0.4)';
    
    // Calculer le nouvel index
    currentSlideIndex += direction;
    
    if (currentSlideIndex >= totalSlides) {
        currentSlideIndex = 0;
    } else if (currentSlideIndex < 0) {
        currentSlideIndex = totalSlides - 1;
    }
    
    const newSlide = slides[currentSlideIndex];
    const newEffect = transitionEffects[currentSlideIndex % 6];
    
    // Activer la nouvelle slide avec effet
    setTimeout(() => {
        newSlide.classList.add('active');
        Object.assign(newSlide.style, newEffect.in);
        dots[currentSlideIndex].classList.add('active');
        dots[currentSlideIndex].style.background = '#22c55e';
        dots[currentSlideIndex].style.transform = 'scale(1.4)';
        
        // Réinitialiser le scale du dot après animation
        setTimeout(() => {
            dots[currentSlideIndex].style.transform = 'scale(1)';
        }, 300);
        
        // Activer les effets de la slide active
        activateSlideEffects(currentSlideIndex);
    }, 100);
    
    // Réinitialiser la barre de progression
    if (progressBar) {
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.width = '100%';
            progressBar.style.transition = `width ${autoplayDelay}ms linear`;
        }, 100);
    }
    
    // Redémarrer l'autoplay
    if (isAutoplayRunning) {
        restartAutoplay();
    }
}

// Fonction pour aller à une slide spécifique
function goToSlide(index) {
    const direction = index > currentSlideIndex ? 1 : -1;
    currentSlideIndex = index - direction;
    changeSlide(direction);
}

// Fonction pour activer les effets de la slide
function activateSlideEffects(index) {
    const slide = document.querySelectorAll('.slider-slide')[index];
    if (!slide) return;
    
    // Animer les coins
    const corners = slide.querySelectorAll('.corner');
    corners.forEach((corner, i) => {
        setTimeout(() => {
            corner.style.opacity = '1';
            corner.style.transform = 'translate(0, 0)';
        }, i * 100);
    });
    
    // Animer le contenu
    const content = slide.querySelector('.gallery-content');
    if (content) {
        setTimeout(() => {
            content.style.opacity = '1';
            content.style.transform = 'translateY(0)';
            
            // Animer les enfants du contenu
            const children = content.querySelectorAll('h3, div, p');
            children.forEach((child, i) => {
                child.style.opacity = '1';
                child.style.transform = 'translateX(0) translateY(0) scale(1)';
            });
        }, 200);
    }
    
    // Animer l'icône zoom
    const zoomIcon = slide.querySelector('.zoom-icon-gallery');
    if (zoomIcon) {
        setTimeout(() => {
            zoomIcon.style.opacity = '1';
            zoomIcon.style.transform = 'scale(1) rotate(0deg)';
        }, 400);
    }
    
    // Activer le glow
    const glow = slide.querySelector('.glow-bg');
    if (glow) {
        glow.style.opacity = '0.6';
    }
    
    // Activer les sparkles
    const sparkles = slide.querySelector('.sparkles');
    if (sparkles) {
        setTimeout(() => {
            sparkles.style.opacity = '1';
        }, 300);
    }
    
    // Activer la scan line
    const scanLine = slide.querySelector('.scan-line');
    if (scanLine) {
        scanLine.style.opacity = '1';
        scanLine.style.top = '-100%';
        setTimeout(() => {
            scanLine.style.top = '100%';
        }, 50);
        setTimeout(() => {
            scanLine.style.opacity = '0';
        }, 800);
    }
}

// Fonction pour démarrer l'autoplay
function startAutoplay() {
    if (totalSlides <= 1) return;
    
    // Initialiser la barre de progression
    const progressBar = document.querySelector('.slider-progress-bar');
    if (progressBar) {
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.width = '100%';
            progressBar.style.transition = `width ${autoplayDelay}ms linear`;
        }, 50);
    }
    
    autoplayInterval = setInterval(() => {
        if (isAutoplayRunning) {
            changeSlide(1);
        }
    }, autoplayDelay);
}

// Fonction pour arrêter l'autoplay
function stopAutoplay() {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
}

// Fonction pour redémarrer l'autoplay
function restartAutoplay() {
    stopAutoplay();
    startAutoplay();
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎬 Diaporama galerie initialisé avec', totalSlides, 'images');
    
    if (totalSlides > 1) {
        // Activer les effets de la première slide
        setTimeout(() => {
            activateSlideEffects(0);
        }, 500);
        
        // Démarrer l'autoplay
        startAutoplay();
        
        // Pause au survol du slider
        const sliderContainer = document.querySelector('.gallery-slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => {
                isAutoplayRunning = false;
                const progressBar = document.querySelector('.slider-progress-bar');
                if (progressBar) {
                    progressBar.style.transition = 'none';
                }
            });
            
            sliderContainer.addEventListener('mouseleave', () => {
                isAutoplayRunning = true;
                restartAutoplay();
            });
        }
        
        // Navigation au clavier (optionnel)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === ' ') {
                e.preventDefault();
                changeSlide(1);
            }
        });
        
        // Animation des dots au survol
        const dots = document.querySelectorAll('.slider-dot');
        dots.forEach((dot, index) => {
            dot.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1.5)';
                    this.style.background = 'rgba(34, 197, 94, 0.6)';
                }
            });
            
            dot.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1)';
                    this.style.background = 'rgba(255, 255, 255, 0.4)';
                }
            });
        });
        
        // Curseur pointer sur les slides
        const slideContainer = document.querySelector('.gallery-slider');
        if (slideContainer) {
            slideContainer.style.cursor = 'pointer';
        }
    }
});

// Nettoyage lors du déchargement
window.addEventListener('beforeunload', function() {
    stopAutoplay();
});
@endif
</script>

<!-- Section La Directrice Générale -->
<section class="minister-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h2 class="text-center mb-5 minister-title">
                    <span class="minister-title-text">LA DIRECTRICE GÉNÉRALE</span>
                </h2>
                
                <div class="minister-card">
                    <div class="row align-items-center">
                        <!-- Image de la DG -->
                        <div class="col-lg-5">
                            <div class="minister-image-container">
                                <div class="minister-image-wrapper">
                                    <img src="{{ asset('images/DG csar.jpg') }}" alt="La Directrice Générale" class="minister-image" onerror="this.src='{{ asset('images/personnel/DG csar.jpg') }}';">
                                    <div class="minister-flags">
                                        <div class="flag flag-senegal"></div>
                                        <div class="flag flag-csar"></div>
                                    </div>
                                    <div class="minister-glow"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contenu de la DG -->
                        <div class="col-lg-7">
                            <div class="minister-content">
                                <div class="minister-subtitle">
                                    <div class="subtitle-line"></div>
                                    <span class="subtitle-text">{{ __('messages.dg.subtitle') }}</span>
                                </div>
                                
                                <div class="minister-message">
                                    <p class="greeting">{{ __('messages.dg.greeting') }}</p>
                                    
                                    <p>{!! __('messages.dg.welcome_message') !!}</p>
                                    
                                    <p>{{ __('messages.dg.mission_message') }}</p>
                                    
                                    <p>{{ __('messages.dg.invitation_message') }}</p>
                                    
                                    <p class="closing">{{ __('messages.dg.closing') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Script de correction pour l'animation des compteurs -->
<script src="{{ asset('js/counter-animation-fix.js') }}?v={{ time() }}"></script>

@endsection