@extends('layouts.public')

@section('title', 'Mentions Légales - CSAR')
@section('meta_description', 'Mentions légales de la plateforme CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience du Sénégal')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-white to-green-50 min-h-screen py-20">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                Mentions Légales
            </h1>
            <div class="w-24 h-1 bg-green-500 mx-auto mb-6"></div>
            <p class="text-xl text-gray-600">
                Informations légales et réglementaires
            </p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
            
            <!-- 1. Identification de l'Organisme -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    1. Identification de l'Organisme
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        <strong class="text-gray-900">Dénomination officielle :</strong><br>
                        Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Nature juridique :</strong><br>
                        Institution publique rattachée à la Présidence de la République du Sénégal
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Adresse du siège social :</strong><br>
                        [Adresse complète à renseigner]<br>
                        Dakar, Sénégal
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Téléphone :</strong><br>
                        +221 [XX XXX XX XX]
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Email :</strong><br>
                        <a href="mailto:contact@csar.sn" class="text-green-600 hover:text-green-700 underline">contact@csar.sn</a>
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Site web :</strong><br>
                        <a href="https://csar.sn" class="text-green-600 hover:text-green-700 underline">https://csar.sn</a>
                    </p>
                </div>
            </section>

            <!-- 2. Directeur de Publication -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    2. Directeur de Publication
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        <strong class="text-gray-900">Nom :</strong><br>
                        [Nom du Directeur Général ou Commissaire]
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Fonction :</strong><br>
                        Directeur Général du CSAR
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Email :</strong><br>
                        <a href="mailto:dg@csar.sn" class="text-green-600 hover:text-green-700 underline">dg@csar.sn</a>
                    </p>
                </div>
            </section>

            <!-- 3. Hébergement du Site -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    3. Hébergement du Site
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        <strong class="text-gray-900">Hébergeur :</strong><br>
                        [Nom de l'hébergeur - À renseigner]
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Adresse :</strong><br>
                        [Adresse complète de l'hébergeur]
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Téléphone :</strong><br>
                        [Téléphone hébergeur]
                    </p>
                </div>
            </section>

            <!-- 4. Propriété Intellectuelle -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    4. Propriété Intellectuelle
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        L'ensemble du contenu de ce site (textes, images, vidéos, logos, graphiques, etc.) est la propriété exclusive du <strong>CSAR</strong> ou de ses partenaires.
                    </p>

                    <p class="mb-4">
                        Toute reproduction, représentation, modification, publication, transmission, dénaturation, totale ou partielle du site ou de son contenu, par quelque procédé que ce soit, et sur quelque support que ce soit est interdite sans l'autorisation écrite préalable du CSAR.
                    </p>

                    <p class="mb-4">
                        Le non-respect de cette interdiction constitue une contrefaçon pouvant engager la responsabilité civile et pénale du contrefacteur.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Exceptions :</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Les documents et rapports publics peuvent être téléchargés et partagés à des fins d'information</li>
                        <li>Les articles de presse peuvent être cités avec mention de la source</li>
                        <li>Les partenaires peuvent utiliser les logos du CSAR selon la charte fournie</li>
                    </ul>
                </div>
            </section>

            <!-- 5. Protection des Données Personnelles -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    5. Protection des Données Personnelles
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        Le CSAR s'engage à protéger les données personnelles des utilisateurs conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi sénégalaise sur les données personnelles.
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Délégué à la Protection des Données (DPO) :</strong><br>
                        Email : <a href="mailto:dpo@csar.sn" class="text-green-600 hover:text-green-700 underline">dpo@csar.sn</a>
                    </p>

                    <p class="mb-4">
                        Pour plus d'informations, consultez notre 
                        <a href="{{ route('politique') }}" class="text-green-600 hover:text-green-700 underline font-semibold">Politique de Confidentialité</a>.
                    </p>
                </div>
            </section>

            <!-- 6. Cookies -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    6. Utilisation des Cookies
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        Ce site utilise des cookies pour améliorer l'expérience utilisateur et réaliser des statistiques de visite.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Types de cookies utilisés :</h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <li><strong>Cookies essentiels</strong> : Nécessaires au fonctionnement du site (session, sécurité)</li>
                        <li><strong>Cookies analytiques</strong> : Google Analytics (avec consentement)</li>
                        <li><strong>Cookies de préférence</strong> : Langue, paramètres d'affichage</li>
                    </ul>

                    <p class="mt-4">
                        Vous pouvez à tout moment modifier vos préférences de cookies via le banner de consentement ou les paramètres de votre navigateur.
                    </p>
                </div>
            </section>

            <!-- 7. Responsabilité -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    7. Limitation de Responsabilité
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        Le CSAR s'efforce d'assurer au mieux l'exactitude et la mise à jour des informations diffusées sur ce site.
                    </p>

                    <p class="mb-4">
                        Toutefois, le CSAR ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition sur ce site.
                    </p>

                    <p class="mb-4">
                        Le CSAR ne pourra être tenu responsable des dommages directs ou indirects résultant de l'accès ou de l'utilisation de ce site, notamment :
                    </p>

                    <ul class="list-disc pl-6 space-y-2">
                        <li>Perte de données</li>
                        <li>Intrusion malveillante</li>
                        <li>Dysfonctionnement technique</li>
                        <li>Informations obsolètes</li>
                    </ul>

                    <p class="mt-4">
                        Le CSAR se réserve le droit de suspendre, modifier ou interrompre l'accès au site sans préavis pour des raisons de maintenance, de sécurité ou de mise à jour.
                    </p>
                </div>
            </section>

            <!-- 8. Liens Hypertextes -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    8. Liens Hypertextes
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Liens sortants :</h3>
                    <p class="mb-4">
                        Ce site peut contenir des liens vers des sites externes. Le CSAR n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 mt-6 mb-3">Liens entrants :</h3>
                    <p class="mb-4">
                        La création de liens hypertextes vers la page d'accueil du site est libre et ne nécessite pas d'autorisation préalable.
                    </p>

                    <p class="mb-4">
                        Toutefois, les liens profonds (deep links) vers des pages internes doivent faire l'objet d'une demande d'autorisation écrite auprès du CSAR à l'adresse : 
                        <a href="mailto:contact@csar.sn" class="text-green-600 hover:text-green-700 underline">contact@csar.sn</a>
                    </p>
                </div>
            </section>

            <!-- 9. Droit Applicable -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    9. Droit Applicable et Juridiction
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        Les présentes mentions légales sont régies par le droit sénégalais.
                    </p>

                    <p class="mb-4">
                        En cas de litige relatif à l'utilisation de ce site, les tribunaux sénégalais seront seuls compétents.
                    </p>

                    <p class="mb-4">
                        Conformément à la loi sénégalaise sur les données personnelles et au RGPD, tout utilisateur dispose d'un droit d'accès, de rectification et de suppression des données le concernant.
                    </p>
                </div>
            </section>

            <!-- 10. Crédits -->
            <section class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    10. Crédits
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        <strong class="text-gray-900">Conception et développement :</strong><br>
                        Direction des Systèmes d'Information du CSAR
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Design graphique :</strong><br>
                        Équipe Communication CSAR
                    </p>

                    <p class="mb-4">
                        <strong class="text-gray-900">Technologies utilisées :</strong>
                    </p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Framework : Laravel 12.x</li>
                        <li>Frontend : Bootstrap 5 / Tailwind CSS</li>
                        <li>Cartes : Leaflet.js / OpenStreetMap</li>
                        <li>Graphiques : Chart.js</li>
                        <li>Icônes : Font Awesome</li>
                    </ul>

                    <p class="mt-4 mb-4">
                        <strong class="text-gray-900">Crédits photos :</strong><br>
                        Les photos présentes sur ce site sont la propriété du CSAR sauf mention contraire.
                        Certaines images proviennent de banques d'images libres de droits (Unsplash, Pexels).
                    </p>
                </div>
            </section>

            <!-- 11. Contact -->
            <section class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-2 h-8 bg-green-500 mr-4"></span>
                    11. Contact et Réclamations
                </h2>
                
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p class="mb-4">
                        Pour toute question concernant les mentions légales ou le fonctionnement de ce site :
                    </p>

                    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg">
                        <p class="mb-2">
                            <strong class="text-gray-900">Email :</strong> 
                            <a href="mailto:contact@csar.sn" class="text-green-600 hover:text-green-700 underline">contact@csar.sn</a>
                        </p>
                        <p class="mb-2">
                            <strong class="text-gray-900">Téléphone :</strong> +221 [XX XXX XX XX]
                        </p>
                        <p class="mb-2">
                            <strong class="text-gray-900">Formulaire de contact :</strong> 
                            <a href="{{ route('contact') }}" class="text-green-600 hover:text-green-700 underline">Nous contacter</a>
                        </p>
                    </div>
                </div>
            </section>

            <!-- Dernière mise à jour -->
            <div class="mt-12 pt-8 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-500">
                    <strong>Dernière mise à jour :</strong> 24 Octobre 2025
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Version 1.0
                </p>
            </div>

        </div>

        <!-- Liens vers autres pages légales -->
        <div class="mt-12 text-center">
            <p class="text-gray-600 mb-4">Consultez également :</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('politique') }}" class="inline-block px-6 py-3 bg-white hover:bg-green-50 text-green-600 border-2 border-green-500 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg">
                    📜 Politique de Confidentialité
                </a>
                <a href="{{ route('conditions') }}" class="inline-block px-6 py-3 bg-white hover:bg-green-50 text-green-600 border-2 border-green-500 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg">
                    📋 Conditions d'Utilisation
                </a>
            </div>
        </div>

    </div>
</div>

<style>
    /* Animations smooth */
    section {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Hover sur les liens */
    a:hover {
        transform: translateY(-1px);
        display: inline-block;
    }
</style>
@endsection








































