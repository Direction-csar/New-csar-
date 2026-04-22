<?php

use Illuminate\Support\Facades\Route;

// Pages publiques statiques (ajout Cascade)

// Route fallback pour éviter l'erreur Route [login] not defined
Route::get('/login', function () {
    return redirect('/');
})->name('login');
// Rediriger les anciens liens /about vers la bonne route /a-propos
Route::redirect('/about', '/a-propos', 302);
// Route directe pour /demande (sans préfixe de locale) - utilise directement le contrôleur
Route::get('/demande', [\App\Http\Controllers\Public\DemandeController::class, 'create'])->name('demande.create.direct');
Route::post('/demande', [\App\Http\Controllers\Public\DemandeController::class, 'store'])->name('demande.store.direct');
// Route directe pour /suivi (sans préfixe de locale) - utilise directement le contrôleur
Route::get('/suivi', [\App\Http\Controllers\Public\TrackController::class, 'index'])->name('suivi.direct');
Route::post('/suivi', [\App\Http\Controllers\Public\TrackController::class, 'track'])->name('suivi.track.direct');
Route::view('/missions', 'public.missions')->name('missions_static');
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\InstitutionController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\ReportsController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ActionController;
use App\Http\Controllers\Public\TrackController;
use App\Http\Controllers\Public\DemandeController;
use App\Http\Controllers\Public\PartnersController;
use App\Http\Controllers\Public\DonationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NewsletterSubscriptionController;

// Contrôleurs Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DemandesController;
use App\Http\Controllers\Admin\EntrepotsController;
use App\Http\Controllers\Admin\StockController;
// use App\Http\Controllers\Admin\PersonnelController; // Supprimé
// use App\Http\Controllers\Admin\ContenuController; // Supprimé - section non utilisée
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ActualitesController;
use App\Http\Controllers\Admin\GalerieController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\MessagesController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SimReportsController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationControllerNew;
use App\Http\Controllers\Admin\AdminMessageController;

// Contrôleurs DG
use App\Http\Controllers\DG\DashboardController as DGDashboardController;

// Contrôleurs Auth
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\DGLoginController;

// Contrôleurs Public
use App\Http\Controllers\Public\AboutController as PublicAboutController;
use App\Http\Controllers\Public\ActualitesController as PublicActualitesController;
use App\Http\Controllers\Public\GalerieController as PublicGalerieController;

// Contrôleurs Admin et DG (déjà importés plus haut)

// Password reset routes (global)
use App\Http\Controllers\Auth\PasswordResetController;

// Routes de connexion simplifiées
require_once __DIR__ . '/simple-login.php';
require_once __DIR__ . '/simple-auth.php';
// Language switching routes
Route::get('/set-locale/{locale}', [LanguageController::class, 'setLocale'])->name('set-locale');

// Téléchargement de l'application mobile SIM
Route::get('/telecharger-app', function () {
    return view('public.download-app');
})->name('app.download');

Route::get('/telecharger-app/apk', function () {
    $file = public_path('downloads/sim-collecte.apk');
    if (!file_exists($file)) {
        abort(404, 'Fichier APK non disponible');
    }
    return response()->download($file, 'CSAR-SIM-Collecte.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
    ]);
})->name('app.download.apk');

// Redirect root to French by default
Route::get('/', function () {
    return redirect('/fr');
});

// Routes simples pour compatibilité (avant les routes localisées)
Route::get('/news', function () {
    return redirect('/fr/actualites');
})->name('news');

// Localized routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'fr|en|ar'], 'middleware' => ['web', \App\Http\Middleware\SetLocale::class]], function () {
    // Home route
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Public routes
    Route::get('/a-propos', [AboutController::class, 'index'])->name('about');
    Route::get('/institution', [InstitutionController::class, 'index'])->name('institution');
    Route::get('/actualites', [\App\Http\Controllers\Public\ActualitesController::class, 'index'])->name('news.index');
    Route::get('/actualites/{id}', [\App\Http\Controllers\Public\ActualitesController::class, 'show'])->name('news.show');
    Route::get('/actualites/{id}/download', [\App\Http\Controllers\Public\ActualitesController::class, 'downloadDocument'])->name('news.download');
    Route::post('/actualites/{id}/comment', [\App\Http\Controllers\Public\NewsCommentController::class, 'store'])->name('news.comment.store');
    Route::get('/rapports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/rapports/{id}/telecharger', [ReportsController::class, 'download'])->name('reports.download');
    Route::get('/rapports/{id}/download', [\App\Http\Controllers\Public\ReportsController::class, 'download'])->name('public.reports.download');
    
    // Contact routes
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

    // Testimonials routes (public)
    Route::get('/temoignages', [\App\Http\Controllers\TestimonialController::class, 'index'])->name('testimonials.index');
    Route::get('/temoignages/create', [\App\Http\Controllers\TestimonialController::class, 'create'])->name('testimonials.create');
    Route::post('/temoignages', [\App\Http\Controllers\TestimonialController::class, 'store'])->name('testimonials.store');

    // Action routes
    Route::get('/effectuer-une-action', [ActionController::class, 'index'])->name('action');
    Route::post('/effectuer-une-action', [ActionController::class, 'submit'])->name('request.submit');

    // Track routes
    Route::get('/suivre-ma-demande', [TrackController::class, 'index'])->name('track');
    Route::post('/suivre-ma-demande', [TrackController::class, 'track'])->name('track.request');
    Route::get('/suivre-ma-demande/{code}/pdf', [TrackController::class, 'download'])->name('track.download');
    Route::get('/verifier/{code}', [TrackController::class, 'verify'])->name('track.verify');

    // Gallery routes
    Route::get('/missions-en-images', [GalleryController::class, 'index'])->name('gallery');
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    // Public map
    Route::get('/carte-interactive', [HomeController::class, 'map'])->name('map');

    // Public partners
    Route::get('/partenaires', [PartnersController::class, 'index'])->name('partners.index');

    // Projets et interventions
    Route::get('/projets', [\App\Http\Controllers\Public\ProjetsController::class, 'index'])->name('projets.index');

    // Ressources (espace documentaire)
    Route::get('/ressources', [\App\Http\Controllers\Public\RessourcesController::class, 'index'])->name('ressources.index');

    // FAQ
    Route::get('/faq', [\App\Http\Controllers\Public\FaqController::class, 'index'])->name('faq.index');

    // Recherche (outil de recherche plateforme)
    Route::get('/recherche', [\App\Http\Controllers\Public\SearchController::class, 'index'])->name('search.index');

    // Faire un don - Nouveau système de donations
    Route::get('/faire-un-don', [DonationController::class, 'index'])->name('donations.index');
    Route::post('/faire-un-don/process', [DonationController::class, 'process'])->name('donations.process');
    Route::get('/faire-un-don/success/{donation}', [DonationController::class, 'success'])->name('donations.success');
    Route::get('/faire-un-don/cancel', [DonationController::class, 'cancel'])->name('donations.cancel');
    Route::post('/faire-un-don/callback', [DonationController::class, 'callback'])->name('donations.callback');
    Route::get('/faire-un-don/track', [DonationController::class, 'track'])->name('donations.track');
    
    // PayPal specific routes
    Route::get('/faire-un-don/paypal/success/{donation}', [DonationController::class, 'paypalSuccess'])->name('donations.paypal.success');
    Route::get('/faire-un-don/paypal/cancel', [DonationController::class, 'paypalCancel'])->name('donations.paypal.cancel');
    Route::post('/faire-un-don/paypal/webhook', [DonationController::class, 'paypalWebhook'])->name('donations.paypal.webhook');
    
    // API Routes pour donations
    Route::prefix('api/donations')->group(function () {
        Route::get('/statistics', [DonationController::class, 'statistics'])->name('donations.statistics');
        Route::get('/history', [DonationController::class, 'history'])->name('donations.history');
    });

    // Application Mobile - Téléchargement APK
    Route::get('/telecharger-apk', function () {
        $apkPath = public_path('downloads/csar-mobile.apk');
        $placeholderPath = public_path('downloads/csar-mobile.apk.placeholder');

        // Si l'APK réel existe, le télécharger
        if (file_exists($apkPath)) {
            return response()->download($apkPath, 'csar-mobile-v1.0.0.apk', [
                'Content-Type' => 'application/vnd.android.package-archive',
            ]);
        }

        // Sinon, afficher la page placeholder
        if (file_exists($placeholderPath)) {
            return view('public.mobile-app.placeholder', [
                'content' => file_get_contents($placeholderPath)
            ]);
        }

        return redirect()->back()->with('info', 'L\'application mobile sera disponible prochainement.');
    })->name('mobile-app.download');

    // Legal pages
    Route::get('/politique-confidentialite', [\App\Http\Controllers\Public\LegalController::class, 'privacy'])->name('privacy');
    Route::get('/conditions-utilisation', [\App\Http\Controllers\Public\LegalController::class, 'terms'])->name('terms');

    
    // Newsletter - Routes publiques unifiées
    Route::post('/newsletter', [\App\Http\Controllers\Public\NewsletterController::class, 'subscribe'])->name('newsletter.store');
    Route::post('/newsletter/subscribe', [\App\Http\Controllers\Public\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::get('/newsletter/unsubscribe', [\App\Http\Controllers\Public\NewsletterController::class, 'unsubscribePage'])->name('newsletter.unsubscribe');
    Route::post('/newsletter/unsubscribe', [\App\Http\Controllers\Public\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.submit');
    Route::get('/newsletter/check', [\App\Http\Controllers\Public\NewsletterController::class, 'checkSubscription'])->name('newsletter.check');

    // Success page for request submission
    Route::get('/demande-succes', [HomeController::class, 'requestSuccess'])->name('request.success');

    // SIM Reports Routes (routes spécifiques AVANT les routes avec paramètres)
    Route::middleware('throttle:90,1')->group(function () {
        Route::get('/sim', [\App\Http\Controllers\Public\SimController::class, 'index'])->name('sim.index');
        Route::get('/sim/presentation', [\App\Http\Controllers\Public\SimController::class, 'presentation'])->name('sim.presentation');
        Route::get('/sim/dashboard', [\App\Http\Controllers\Public\SimController::class, 'dashboard'])->name('sim.dashboard');
        Route::get('/sim/prices', [\App\Http\Controllers\Public\SimController::class, 'prices'])->name('sim.prices');
        Route::get('/sim/consultation-prix', [\App\Http\Controllers\Public\SimController::class, 'consultationPrix'])->name('sim.consultation-prix');
        Route::get('/sim/carte-marches', [\App\Http\Controllers\Public\SimController::class, 'carteMarches'])->name('sim.carte-marches');
        Route::get('/sim/request-access', [\App\Http\Controllers\Public\SimController::class, 'requestAccess'])->name('sim.request-access');
        Route::post('/sim/request-access', [\App\Http\Controllers\Public\SimController::class, 'storeAccessRequest'])->name('sim.request-access.store');
        Route::get('/sim/supply', [\App\Http\Controllers\Public\SimController::class, 'supply'])->name('sim.supply');
        Route::get('/sim/regional', [\App\Http\Controllers\Public\SimController::class, 'regional'])->name('sim.regional');
        Route::get('/sim/distributions', [\App\Http\Controllers\Public\SimController::class, 'distributions'])->name('sim.distributions');
        Route::get('/sim/magasins', [\App\Http\Controllers\Public\SimController::class, 'magasins'])->name('sim.magasins');
        Route::get('/sim/operations', [\App\Http\Controllers\Public\SimController::class, 'operations'])->name('sim.operations');
        Route::get('/sim/{simReport}/download', [\App\Http\Controllers\Public\SimController::class, 'download'])->name('sim.download');
        Route::get('/sim/{simReport}', [\App\Http\Controllers\Public\SimController::class, 'show'])->name('sim.show');
    });

    // Public Routes - Formulaire de demande
    Route::get('/demande', [DemandeController::class, 'create'])->name('demande.create');
    Route::post('/demande', [DemandeController::class, 'store'])->name('demande.store');
    Route::get('/demande-succes', [DemandeController::class, 'success'])->name('demande.success');
    
    // Alias pour les routes requests (compatibilité avec la navigation)
    Route::get('/demandes', [DemandeController::class, 'create'])->name('requests.index');
    Route::get('/demandes/create', [DemandeController::class, 'create'])->name('requests.create');

    // Alias pour la compatibilité avec les anciens liens
    Route::redirect('/demande-static', '/demande', 301);
});

// DRH — Interface Avances Tabaski
Route::prefix('drh')->name('drh.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\DRHLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\DRHLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Auth\DRHLoginController::class, 'logout'])->name('logout');
    Route::get('/', fn () => redirect()->route('drh.login'))->name('dashboard');
    Route::match(['get', 'post'], '/{path}', fn () => redirect()->route('drh.login'))->where('path', '.*')->name('fallback');
});

// Admin Routes - Supprimées (dupliquées avec le groupe ci-dessous)

// DG Routes
Route::prefix('dg')->name('dg.')->group(function () {
    // Pages d'authentification DG
    Route::get('/login', [App\Http\Controllers\Auth\DGLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\DGLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Auth\DGLoginController::class, 'logout'])->name('logout');

    // Routes protégées DG (lecture seule)
    Route::middleware([\App\Http\Middleware\DGMiddleware::class])->group(function () {
        // Dashboard DG
        Route::get('/', [App\Http\Controllers\DG\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [App\Http\Controllers\DG\DashboardController::class, 'index'])->name('dashboard.alt');
        Route::get('/api/realtime', [App\Http\Controllers\DG\DashboardController::class, 'getRealtimeStats'])->name('api.realtime');
        Route::post('/api/generate-report', [App\Http\Controllers\DG\DashboardController::class, 'generateReport'])->name('api.generate-report');
        Route::get('/reports/download/{filename}', [App\Http\Controllers\DG\DashboardController::class, 'downloadReport'])->name('reports.download');
        
        // Gestion des demandes (système unifié)
        Route::get('/demandes', [App\Http\Controllers\DG\DemandeController::class, 'index'])->name('demandes.index');
        Route::get('/demandes/{id}', [App\Http\Controllers\DG\DemandeController::class, 'show'])->name('demandes.show');
        Route::put('/demandes/{id}', [App\Http\Controllers\DG\DemandeController::class, 'update'])->name('demandes.update');
        
        // Consultation des entrepôts (lecture seule)
        Route::get('/warehouses', [App\Http\Controllers\DG\WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/warehouses/{id}', [App\Http\Controllers\DG\WarehouseController::class, 'show'])->name('warehouses.show');
        
        // Consultation des stocks (lecture seule)
        Route::get('/stocks', [App\Http\Controllers\DG\StockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/{id}', [App\Http\Controllers\DG\StockController::class, 'show'])->name('stocks.show');
        
        // Consultation du personnel (lecture seule)
        Route::get('/personnel', [App\Http\Controllers\DG\PersonnelController::class, 'index'])->name('personnel.index');
        Route::get('/personnel/{id}', [App\Http\Controllers\DG\PersonnelController::class, 'show'])->name('personnel.show');
        
        // Consultation des utilisateurs (lecture seule)
        Route::get('/users', [App\Http\Controllers\DG\UsersController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [App\Http\Controllers\DG\UsersController::class, 'show'])->name('users.show');
        
        // Rapports (lecture seule)
        Route::get('/reports', [App\Http\Controllers\DG\ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/generate', [App\Http\Controllers\DG\ReportsController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/export', [App\Http\Controllers\DG\ReportsController::class, 'export'])->name('reports.export');
        Route::get('/reports/{filename}', [App\Http\Controllers\DG\ReportsController::class, 'show'])->name('reports.show');
        
        // Carte interactive
        Route::get('/map', [App\Http\Controllers\DG\MapController::class, 'index'])->name('map');
        Route::get('/map/data', [App\Http\Controllers\DG\MapController::class, 'getData'])->name('map.data');

        // SIM (prix nationaux, évolution, alertes, comparaison régions)
        Route::get('/sim', [App\Http\Controllers\DG\SimController::class, 'index'])->name('sim.index');

        // SIM — Suivi collecteurs terrain temps réel (lecture seule)
        Route::prefix('sim-suivi')->name('sim-suivi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'index'])->name('index');
            Route::get('/temps-reel', fn() => view('supervisor.live-tracking'))->name('live');
            Route::get('/collecteurs/{id}', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'collectorDetails'])->name('collector');
            Route::get('/collectes', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'collections'])->name('collectes');
        });

        // Profil DG
        // Routes à implémenter si nécessaire
    });
});

Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Routes de test (uniquement en environnement local)
if (app()->environment('local', 'testing', 'development')) {
    Route::get('/test', [\App\Http\Controllers\Public\TestController::class, 'test'])->name('test');
    Route::get('/test-form', [\App\Http\Controllers\Public\TestController::class, 'testForm'])->name('test.form');
    Route::post('/test-submit', [\App\Http\Controllers\Public\TestController::class, 'testSubmit'])->name('test.submit');
}

// Public Routes - Formulaire de demande - Routes déjà définies dans le groupe {locale} (lignes 164-166)
// Route::get('/demande', [DemandeController::class, 'create'])->name('demande.create');
// Route::post('/demande', [DemandeController::class, 'store'])->name('demande.store');
// Route::get('/demande-succes', [DemandeController::class, 'success'])->name('demande.success');

// Route pour rafraîchir le token CSRF
Route::get('/csrf-token', [\App\Http\Controllers\CsrfController::class, 'getToken'])->name('csrf.token');

// Alias pour la compatibilité avec les anciens liens
Route::redirect('/demande-static', '/demande', 301);
// Route home déjà définie dans le groupe {locale} (ligne 95)
// Route::get('/', [HomeController::class, 'index'])->name('home');
// Route about déjà définie dans le groupe {locale} (ligne 98)
// Route::get('/a-propos', [AboutController::class, 'index'])->name('about');
// Route::get('/institution', [InstitutionController::class, 'index'])->name('institution');
// Routes rapports déjà définies dans le groupe {locale} (lignes 103-105)
// Route::get('/rapports', [ReportsController::class, 'index'])->name('reports');
// Route::get('/rapports/{id}/telecharger', [ReportsController::class, 'download'])->name('reports.download');


// Action Routes - Routes déjà définies dans le groupe {locale} (lignes 112-113)
// Route::get('/effectuer-une-action', [ActionController::class, 'index'])->name('action');
// Route::post('/effectuer-une-action', [ActionController::class, 'submit'])->name('request.submit');

// Track Routes - Routes déjà définies dans le groupe {locale} (lignes 116-118)
// Route::get('/suivre-ma-demande', [TrackController::class, 'index'])->name('track');
// Route::post('/suivre-ma-demande', [TrackController::class, 'track'])->name('track.request');
// Route::get('/suivre-ma-demande/{code}/pdf', [TrackController::class, 'download'])->name('track.download');

// Gallery Routes - Routes déjà définies dans le groupe {locale} (lignes 121-122)
// Route::get('/missions-en-images', [GalleryController::class, 'index'])->name('gallery');
// Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// Public Map - Route déjà définie dans le groupe {locale} (ligne 125)
// Route::get('/carte-interactive', [HomeController::class, 'map'])->name('map');

// Public Partners - Route déjà définie dans le groupe {locale} (ligne 128)
// Route::get('/partenaires', [PartnersController::class, 'index'])->name('partners.index');


// Success page for request submission - Route déjà définie dans le groupe {locale} (ligne 147)
// Route::get('/demande-succes', [HomeController::class, 'requestSuccess'])->name('request.success');

// SIM Reports Routes - Routes déjà définies dans le groupe {locale} (lignes 151-160)
// Ajout d'un throttling léger pour éviter les abus (90 req/min par IP)
// Route::middleware('throttle:90,1')->group(function () {
//     Route::get('/sim', [\App\Http\Controllers\Public\SimController::class, 'index'])->name('sim.index');
//     Route::get('/sim/dashboard', [\App\Http\Controllers\Public\SimController::class, 'dashboard'])->name('sim.dashboard');
//     Route::get('/sim/prices', [\App\Http\Controllers\Public\SimController::class, 'prices'])->name('sim.prices');
//     Route::get('/sim/supply', [\App\Http\Controllers\Public\SimController::class, 'supply'])->name('sim.supply');
//     Route::get('/sim/regional', [\App\Http\Controllers\Public\SimController::class, 'regional'])->name('sim.regional');
//     Route::get('/sim/distributions', [\App\Http\Controllers\Public\SimController::class, 'distributions'])->name('sim.distributions');
//     Route::get('/sim/magasins', [\App\Http\Controllers\Public\SimController::class, 'magasins'])->name('sim.magasins');
//     Route::get('/sim/operations', [\App\Http\Controllers\Public\SimController::class, 'operations'])->name('sim.operations');
//     Route::get('/sim/{simReport}/download', [\App\Http\Controllers\Public\SimController::class, 'download'])->name('sim.download');
//     Route::get('/sim/{simReport}', [\App\Http\Controllers\Public\SimController::class, 'show'])->name('sim.show');
// });

// Routes Admin
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentification Admin
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::get('/login/google', [GoogleAuthController::class, 'redirect'])->name('login.google');
    Route::get('/login/google/callback', [GoogleAuthController::class, 'callback'])->name('login.google.callback');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    // Routes protégées Admin
    Route::middleware(['admin'])->group(function () {
        // Redirection de admin/ vers admin/dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        // Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard'); // Dupliquée - déjà définie ligne 342
        Route::get('/dashboard/realtime-stats', [AdminDashboardController::class, 'realtimeStats'])->name('dashboard.realtime-stats');
        Route::post('/dashboard/filter-map', [AdminDashboardController::class, 'filterMapData'])->name('dashboard.filter-map');
        Route::post('/dashboard/generate-report', [AdminDashboardController::class, 'generateReport'])->name('dashboard.generate-report');
        Route::get('/reports/download/{filename}', [AdminDashboardController::class, 'downloadReport'])->name('reports.download');
        
        // Gestion des demandes (sans création - les demandes viennent du public)
        Route::resource('demandes', DemandesController::class)->except(['create', 'store']);
        Route::get('/carte-demandes', [\App\Http\Controllers\Admin\CarteDemandesController::class, 'index'])->name('carte-demandes.index');
        Route::get('/carte-demandes/api', [\App\Http\Controllers\Admin\CarteDemandesController::class, 'getMapData'])->name('carte-demandes.api');
        Route::get('/demandes/{id}/pdf', [DemandesController::class, 'downloadPdf'])->name('demandes.pdf');
        Route::post('/demandes/export', [DemandesController::class, 'export'])->name('demandes.export');
        Route::post('/demandes/bulk-delete', [DemandesController::class, 'bulkDelete'])->name('demandes.bulk-delete');
        
        // Gestion des entrepôts
        Route::resource('entrepots', EntrepotsController::class);
        Route::get('/entrepots/export', [EntrepotsController::class, 'export'])->name('entrepots.export');
        
        // Gestion des stocks
        Route::post('/stock/generate-reference', [\App\Http\Controllers\Admin\StockController::class, 'generateReference'])->name('stock.generate-reference');
        Route::resource('stock', \App\Http\Controllers\Admin\StockController::class);
        Route::get('/stock/{id}/receipt', [\App\Http\Controllers\Admin\StockController::class, 'downloadReceipt'])->name('stock.receipt');
        Route::post('/stock/export', [\App\Http\Controllers\Admin\StockController::class, 'export'])->name('stock.export');
        
        // Gestion des produits
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::get('/products-api', [\App\Http\Controllers\Admin\ProductController::class, 'getProducts'])->name('products.api');
        Route::post('/products/quick-create', [\App\Http\Controllers\Admin\ProductController::class, 'quickCreate'])->name('products.quick-create');
        
        
        // Gestion du personnel
        Route::resource('personnel', \App\Http\Controllers\Admin\PersonnelController::class);
        Route::post('/personnel/{id}/toggle-status', [\App\Http\Controllers\Admin\PersonnelController::class, 'toggleStatus'])->name('personnel.toggle-status');
        Route::post('/personnel/{id}/reset-password', [\App\Http\Controllers\Admin\PersonnelController::class, 'resetPassword'])->name('personnel.reset-password');
        Route::get('/personnel/export', [\App\Http\Controllers\Admin\PersonnelController::class, 'export'])->name('personnel.export');
        
        // Gestion du contenu - SUPPRIMÉ (section non utilisée)
        // Route::resource('contenu', ContenuController::class);
        
        // Gestion des actualités
        Route::resource('actualites', \App\Http\Controllers\Admin\ActualitesController::class);
        Route::get('actualites/{id}/download', [\App\Http\Controllers\Admin\ActualitesController::class, 'downloadDocument'])->name('actualites.download');
        Route::get('actualites/{id}/preview', [\App\Http\Controllers\Admin\ActualitesController::class, 'preview'])->name('actualites.preview');
        
        // Gestion de la galerie
        Route::resource('galerie', \App\Http\Controllers\Admin\GalerieController::class);
        Route::post('/galerie/{id}/toggle-status', [\App\Http\Controllers\Admin\GalerieController::class, 'toggleStatus'])->name('galerie.toggle-status');
        
        
        // Logs d'audit
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/{id}', [AuditController::class, 'show'])->name('audit.show');
        Route::post('/audit/export', [AuditController::class, 'export'])->name('audit.export');
        Route::post('/audit/clear', [AuditController::class, 'clearOldLogs'])->name('audit.clear');

        // Gestion des communications
        Route::get('/communications', [\App\Http\Controllers\Admin\CommunicationsController::class, 'index'])->name('communications.index');
        Route::get('/communications/realtime-stats', [\App\Http\Controllers\Admin\CommunicationsController::class, 'realtimeStats'])->name('communications.realtime-stats');

        // Gestion des utilisateurs
        Route::resource('users', UserController::class);
        
        // Gestion du contenu
        Route::resource('content', \App\Http\Controllers\Admin\ContentController::class);
        Route::post('/content/{id}/toggle-status', [\App\Http\Controllers\Admin\ContentController::class, 'toggleStatus'])->name('content.toggle-status');
        Route::get('/content-preview', [\App\Http\Controllers\Admin\ContentController::class, 'preview'])->name('content.preview');
        
        // Routes pour la gestion des statistiques de contenu (supprimées car non implémentées)
        // Route::get('/content/statistics', [\App\Http\Controllers\Admin\ContentController::class, 'statistics'])->name('content.statistics');
        // Route::post('/content/statistics/create', [\App\Http\Controllers\Admin\ContentController::class, 'createStatistic'])->name('content.statistics.create');
        // Route::post('/content/statistics/{id}/update', [\App\Http\Controllers\Admin\ContentController::class, 'updateStatistic'])->name('content.statistics.update');
        // Route::delete('/content/statistics/{id}/delete', [\App\Http\Controllers\Admin\ContentController::class, 'deleteStatistic'])->name('content.statistics.delete');
        
        // Routes pour les statistiques générales
        Route::get('/statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics');
        Route::post('/statistics/export', [\App\Http\Controllers\Admin\StatisticsController::class, 'export'])->name('statistics.export');
        
        // Routes pour la gestion des chiffres clés
        Route::resource('chiffres-cles', \App\Http\Controllers\Admin\ChiffresClesController::class)->except(['create', 'show', 'destroy']);
        Route::post('/chiffres-cles/update-batch', [\App\Http\Controllers\Admin\ChiffresClesController::class, 'updateBatch'])->name('chiffres-cles.update-batch');
        Route::post('/chiffres-cles/{id}/toggle-status', [\App\Http\Controllers\Admin\ChiffresClesController::class, 'toggleStatus'])->name('chiffres-cles.toggle-status');
        Route::post('/chiffres-cles/reset', [\App\Http\Controllers\Admin\ChiffresClesController::class, 'reset'])->name('chiffres-cles.reset');
        Route::get('/chiffres-cles/api', [\App\Http\Controllers\Admin\ChiffresClesController::class, 'api'])->name('chiffres-cles.api');
        
        // Routes pour le nettoyage de la base de données
        Route::get('/database-cleanup', [\App\Http\Controllers\Admin\DatabaseCleanupController::class, 'index'])->name('database-cleanup');
        Route::post('/database-cleanup/cleanup', [\App\Http\Controllers\Admin\DatabaseCleanupController::class, 'cleanup'])->name('database-cleanup.cleanup');
        Route::get('/database-cleanup/check-connection', [\App\Http\Controllers\Admin\DatabaseCleanupController::class, 'checkConnection'])->name('database-cleanup.check-connection');
        
        // Gestion des actualités
        Route::resource('news', \App\Http\Controllers\Admin\NewsController::class);
        Route::post('/news/{id}/toggle-status', [\App\Http\Controllers\Admin\NewsController::class, 'toggleStatus'])->name('news.toggle-status');
        Route::post('/news/{id}/toggle-featured', [\App\Http\Controllers\Admin\NewsController::class, 'toggleFeatured'])->name('news.toggle-featured');
        Route::get('/news-preview', [\App\Http\Controllers\Admin\NewsController::class, 'preview'])->name('news.preview');
        Route::delete('/news-comments/{id}', [\App\Http\Controllers\Admin\NewsCommentController::class, 'destroy'])->name('news.comments.destroy');
        Route::get('/news/{id}/comments', [\App\Http\Controllers\Admin\NewsCommentController::class, 'index'])->name('news.comments.index');
        
        // Gestion de la galerie
        Route::resource('gallery', \App\Http\Controllers\Admin\GalleryController::class);
        Route::post('/gallery/upload', [\App\Http\Controllers\Admin\GalleryController::class, 'upload'])->name('gallery.upload');
        Route::post('/gallery/album', [\App\Http\Controllers\Admin\GalleryController::class, 'createAlbum'])->name('gallery.album');
        Route::get('/gallery/{id}/download', [\App\Http\Controllers\Admin\GalleryController::class, 'download'])->name('gallery.download');
        Route::post('/gallery/move', [\App\Http\Controllers\Admin\GalleryController::class, 'move'])->name('gallery.move');
        Route::post('/gallery/optimize', [\App\Http\Controllers\Admin\GalleryController::class, 'optimize'])->name('gallery.optimize');
        
        // Communication
        Route::resource('communication', \App\Http\Controllers\Admin\CommunicationController::class);
        Route::post('/communication/send-message', [\App\Http\Controllers\Admin\CommunicationController::class, 'sendMessage'])->name('communication.send-message');
        Route::post('/communication/create-channel', [\App\Http\Controllers\Admin\CommunicationController::class, 'createChannel'])->name('communication.create-channel');
        Route::post('/communication/create-template', [\App\Http\Controllers\Admin\CommunicationController::class, 'createTemplate'])->name('communication.create-template');
        Route::post('/communication/send-broadcast', [\App\Http\Controllers\Admin\CommunicationController::class, 'sendBroadcast'])->name('communication.send-broadcast');
        Route::get('/communication/stats', [\App\Http\Controllers\Admin\CommunicationController::class, 'getStats'])->name('communication.stats');
        Route::get('/communication/analytics', [\App\Http\Controllers\Admin\CommunicationController::class, 'getAnalytics'])->name('communication.analytics');
        
        // Messages (lecture seule - pas de création depuis l'admin)
        Route::get('/messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [\App\Http\Controllers\Admin\MessageController::class, 'show'])->name('messages.show');
        Route::delete('/messages/{id}', [\App\Http\Controllers\Admin\MessageController::class, 'destroy'])->name('messages.destroy');
        Route::post('/messages/{id}/mark-read', [\App\Http\Controllers\Admin\MessageController::class, 'markAsRead'])->name('messages.mark-read');
        Route::post('/messages/mark-all-read', [\App\Http\Controllers\Admin\MessageController::class, 'markAllAsRead'])->name('messages.mark-all-read');
        Route::post('/messages/{id}/reply', [\App\Http\Controllers\Admin\MessageController::class, 'reply'])->name('messages.reply');
        
        // Newsletter (lecture seule - pas de création depuis l'admin)
        Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('/newsletter/export', [\App\Http\Controllers\Admin\NewsletterController::class, 'exportSubscribers'])->name('newsletter.export');
        Route::get('/newsletter/stats', [\App\Http\Controllers\Admin\NewsletterController::class, 'getStats'])->name('newsletter.stats');
        Route::get('/newsletter/analytics', [\App\Http\Controllers\Admin\NewsletterController::class, 'getAnalytics'])->name('newsletter.analytics');
        
        // Rapports SIM
        // Route::resource('sim-reports', \App\Http\Controllers\Admin\SimReportsController::class); // CONFLIT - remplacé par routes manuelles ci-dessous
        Route::get('/sim-reports', [\App\Http\Controllers\Admin\SimReportsController::class, 'index'])->name('sim-reports.index');
        Route::get('/sim-reports/create', [\App\Http\Controllers\Admin\SimReportsController::class, 'create'])->name('sim-reports.create');
        Route::post('/sim-reports', [\App\Http\Controllers\Admin\SimReportsController::class, 'store'])->name('sim-reports.store');
        Route::get('/sim-reports/{id}', [\App\Http\Controllers\Admin\SimReportsController::class, 'show'])->name('sim-reports.show');
        Route::get('/sim-reports/{id}/edit', [\App\Http\Controllers\Admin\SimReportsController::class, 'edit'])->name('sim-reports.edit');
        Route::put('/sim-reports/{id}', [\App\Http\Controllers\Admin\SimReportsController::class, 'update'])->name('sim-reports.update');
        Route::delete('/sim-reports/{id}', [\App\Http\Controllers\Admin\SimReportsController::class, 'destroy'])->name('sim-reports.destroy');
        Route::post('/sim-reports/upload', [\App\Http\Controllers\Admin\SimReportsController::class, 'uploadDocument'])->name('sim-reports.upload');
        Route::post('/sim-reports/generate', [\App\Http\Controllers\Admin\SimReportsController::class, 'generateReport'])->name('sim-reports.generate');
        Route::get('/sim-reports/{id}/download', [\App\Http\Controllers\Admin\SimReportsController::class, 'download'])->name('sim-reports.download');
        Route::post('/sim-reports/{id}/schedule', [\App\Http\Controllers\Admin\SimReportsController::class, 'schedule'])->name('sim-reports.schedule');
        Route::get('/sim-reports/{id}/status', [\App\Http\Controllers\Admin\SimReportsController::class, 'getStatus'])->name('sim-reports.status');
        Route::get('/sim-reports/export-all', [\App\Http\Controllers\Admin\SimReportsController::class, 'exportAll'])->name('sim-reports.export-all');
        Route::get('/sim-reports/stats', [\App\Http\Controllers\Admin\SimReportsController::class, 'getStats'])->name('sim-reports.stats');

        // Gestion des témoignages
        Route::prefix('testimonials')->name('testimonials.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'reject'])->name('reject');
            Route::post('/{id}/toggle-featured', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\AdminTestimonialController::class, 'destroy'])->name('destroy');
        });

        // Gestion opérationnelle du SIM
        Route::prefix('sim')->name('sim.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SimManagementController::class, 'dashboard'])->name('dashboard');

            // Gestion des collecteurs (création identifiants APK)
            Route::get('/collectors', [\App\Http\Controllers\Admin\SimCollectorsController::class, 'index'])->name('collectors');
            Route::post('/collectors', [\App\Http\Controllers\Admin\SimCollectorsController::class, 'store'])->name('collectors.store');
            Route::put('/collectors/{id}', [\App\Http\Controllers\Admin\SimCollectorsController::class, 'update'])->name('collectors.update');
            Route::delete('/collectors/{id}', [\App\Http\Controllers\Admin\SimCollectorsController::class, 'destroy'])->name('collectors.destroy');
            Route::get('/live', [\App\Http\Controllers\Admin\SimManagementController::class, 'live'])->name('live');

            Route::get('/regions', [\App\Http\Controllers\Admin\SimManagementController::class, 'regions'])->name('regions');
            Route::post('/regions', [\App\Http\Controllers\Admin\SimManagementController::class, 'storeRegion'])->name('regions.store');
            Route::get('/regions/{simRegion}/edit', [\App\Http\Controllers\Admin\SimManagementController::class, 'editRegion'])->name('regions.edit');
            Route::put('/regions/{simRegion}', [\App\Http\Controllers\Admin\SimManagementController::class, 'updateRegion'])->name('regions.update');
            Route::delete('/regions/{simRegion}', [\App\Http\Controllers\Admin\SimManagementController::class, 'destroyRegion'])->name('regions.destroy');

            Route::get('/departments', [\App\Http\Controllers\Admin\SimManagementController::class, 'departments'])->name('departments');
            Route::post('/departments', [\App\Http\Controllers\Admin\SimManagementController::class, 'storeDepartment'])->name('departments.store');
            Route::get('/departments/{simDepartment}/edit', [\App\Http\Controllers\Admin\SimManagementController::class, 'editDepartment'])->name('departments.edit');
            Route::put('/departments/{simDepartment}', [\App\Http\Controllers\Admin\SimManagementController::class, 'updateDepartment'])->name('departments.update');
            Route::delete('/departments/{simDepartment}', [\App\Http\Controllers\Admin\SimManagementController::class, 'destroyDepartment'])->name('departments.destroy');

            Route::get('/assignments', [\App\Http\Controllers\Admin\SimManagementController::class, 'assignments'])->name('assignments');
            Route::post('/assignments', [\App\Http\Controllers\Admin\SimManagementController::class, 'storeAssignment'])->name('assignments.store');
            Route::put('/assignments/{simCollectorAssignment}', [\App\Http\Controllers\Admin\SimManagementController::class, 'updateAssignment'])->name('assignments.update');
            Route::delete('/assignments/{simCollectorAssignment}', [\App\Http\Controllers\Admin\SimManagementController::class, 'destroyAssignment'])->name('assignments.destroy');

            Route::get('/markets', [\App\Http\Controllers\Admin\SimManagementController::class, 'markets'])->name('markets');
            Route::post('/markets', [\App\Http\Controllers\Admin\SimManagementController::class, 'storeMarket'])->name('markets.store');
            Route::get('/markets/{simMarket}/edit', [\App\Http\Controllers\Admin\SimManagementController::class, 'editMarket'])->name('markets.edit');
            Route::put('/markets/{simMarket}', [\App\Http\Controllers\Admin\SimManagementController::class, 'updateMarket'])->name('markets.update');
            Route::delete('/markets/{simMarket}', [\App\Http\Controllers\Admin\SimManagementController::class, 'destroyMarket'])->name('markets.destroy');

            Route::get('/categories', [\App\Http\Controllers\Admin\SimManagementController::class, 'categories'])->name('categories');
            Route::post('/categories', [\App\Http\Controllers\Admin\SimManagementController::class, 'storeCategory'])->name('categories.store');
            Route::get('/categories/{simProductCategory}/edit', [\App\Http\Controllers\Admin\SimManagementController::class, 'editCategory'])->name('categories.edit');
            Route::put('/categories/{simProductCategory}', [\App\Http\Controllers\Admin\SimManagementController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{simProductCategory}', [\App\Http\Controllers\Admin\SimManagementController::class, 'destroyCategory'])->name('categories.destroy');

            Route::get('/products', [\App\Http\Controllers\Admin\SimManagementController::class, 'products'])->name('products');
            Route::post('/products', [\App\Http\Controllers\Admin\SimManagementController::class, 'storeProduct'])->name('products.store');
            Route::get('/products/{simProduct}/edit', [\App\Http\Controllers\Admin\SimManagementController::class, 'editProduct'])->name('products.edit');
            Route::put('/products/{simProduct}', [\App\Http\Controllers\Admin\SimManagementController::class, 'updateProduct'])->name('products.update');
            Route::delete('/products/{simProduct}', [\App\Http\Controllers\Admin\SimManagementController::class, 'destroyProduct'])->name('products.destroy');

            Route::get('/collections', [\App\Http\Controllers\Admin\SimManagementController::class, 'collections'])->name('collections');
            Route::get('/collections/{simCollection}', [\App\Http\Controllers\Admin\SimManagementController::class, 'showCollection'])->name('collections.show');
            Route::post('/collections/{simCollection}/validate', [\App\Http\Controllers\Admin\SimManagementController::class, 'validateCollection'])->name('collections.validate');
            Route::post('/collections/{simCollection}/reject', [\App\Http\Controllers\Admin\SimManagementController::class, 'rejectCollection'])->name('collections.reject');
            Route::post('/collections/{simCollection}/publish', [\App\Http\Controllers\Admin\SimManagementController::class, 'publishCollection'])->name('collections.publish');

            Route::get('/access-requests', [\App\Http\Controllers\Admin\SimManagementController::class, 'accessRequests'])->name('access-requests');
            Route::get('/access-requests/{simDataAccessRequest}', [\App\Http\Controllers\Admin\SimManagementController::class, 'showAccessRequest'])->name('access-requests.show');
            Route::post('/access-requests/{simDataAccessRequest}/decision', [\App\Http\Controllers\Admin\SimManagementController::class, 'decideAccessRequest'])->name('access-requests.decision');
        });
        
        // Routes API pour les notifications (pour le dropdown et AJAX)
        Route::get('/api/notifications', [\App\Http\Controllers\Admin\NotificationsController::class, 'getNotifications'])->name('notifications.api');
        Route::get('/api/notifications/count', [\App\Http\Controllers\Admin\NotificationsController::class, 'getUnreadCount'])->name('notifications.count');
        Route::post('/api/notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAsRead'])->name('notifications.api.mark-read');
        Route::post('/api/notifications/{id}/mark-unread', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAsUnread'])->name('notifications.api.mark-unread');
        Route::post('/api/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAllAsRead'])->name('notifications.api.mark-all-read');
        Route::delete('/api/notifications/{id}', [\App\Http\Controllers\Admin\NotificationsController::class, 'destroy'])->name('notifications.api.destroy');
        
        // Audit & Sécurité
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/logs', [\App\Http\Controllers\Admin\AuditController::class, 'getLogs'])->name('audit.logs');
        Route::get('/admin/audit/logs', function() { return view('admin.audit.logs'); })->name('admin.audit.logs');
        Route::post('/admin/audit/logs', [\App\Http\Controllers\Admin\AuditController::class, 'getLogs'])->name('admin.audit.logs.data');
        Route::get('/admin/audit/logs/{id}', [\App\Http\Controllers\Admin\AuditController::class, 'showLog'])->name('admin.audit.logs.show');
        Route::get('/audit/sessions', [\App\Http\Controllers\Admin\AuditController::class, 'getSessions'])->name('audit.sessions');
        Route::post('/audit/sessions/{id}/terminate', [\App\Http\Controllers\Admin\AuditController::class, 'terminateSession'])->name('audit.terminate-session');
        Route::post('/audit/sessions/terminate-all', [\App\Http\Controllers\Admin\AuditController::class, 'terminateAllSessions'])->name('audit.terminate-all-sessions');
        Route::post('/audit/security-report', [\App\Http\Controllers\Admin\AuditController::class, 'generateSecurityReport'])->name('audit.security-report');
        Route::post('/audit/clear-logs', [\App\Http\Controllers\Admin\AuditController::class, 'clearOldLogs'])->name('audit.clear-logs');
        Route::get('/audit/stats', [\App\Http\Controllers\Admin\AuditController::class, 'getStats'])->name('audit.stats');
        Route::get('/audit/chart-data', [\App\Http\Controllers\Admin\AuditController::class, 'getChartData'])->name('audit.chart-data');
        
        
        // Profil Utilisateur
        Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

        // Communication
        Route::resource('communication', CommunicationController::class);
        
        // Stocks
        Route::get('/stocks', [\App\Http\Controllers\Admin\StockController::class, 'index'])->name('stocks.index');
        Route::get('/stocks/create', [\App\Http\Controllers\Admin\StockController::class, 'create'])->name('stocks.create');
        Route::post('/stocks', [\App\Http\Controllers\Admin\StockController::class, 'store'])->name('stocks.store');
        Route::get('/stocks/{stock}', [\App\Http\Controllers\Admin\StockController::class, 'show'])->name('stocks.show');
        Route::get('/stocks/{stock}/edit', [\App\Http\Controllers\Admin\StockController::class, 'edit'])->name('stocks.edit');
        Route::put('/stocks/{stock}', [\App\Http\Controllers\Admin\StockController::class, 'update'])->name('stocks.update');
        Route::delete('/stocks/{stock}', [\App\Http\Controllers\Admin\StockController::class, 'destroy'])->name('stocks.destroy');
        
        // Warehouses
        Route::get('/warehouses', [\App\Http\Controllers\Admin\WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/warehouses/create', [\App\Http\Controllers\Admin\WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/warehouses', [\App\Http\Controllers\Admin\WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{warehouse}', [\App\Http\Controllers\Admin\WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('/warehouses/{warehouse}/edit', [\App\Http\Controllers\Admin\WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/warehouses/{warehouse}', [\App\Http\Controllers\Admin\WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [\App\Http\Controllers\Admin\WarehouseController::class, 'destroy'])->name('warehouses.destroy');

        // Messages
        // Routes messages déjà définies plus haut dans le groupe admin (lignes 454-459)
        // Route::resource('messages', MessagesController::class); // CONFLIT - routes définies manuellement avec {id}
        // Route::post('/messages/{message}/reply', [MessagesController::class, 'reply'])->name('messages.reply');
        // Route::post('/messages/{message}/mark-read', [MessagesController::class, 'markAsRead'])->name('messages.mark-read');

        // Newsletter
        Route::resource('newsletter', NewsletterController::class);
        Route::post('/newsletter/{newsletter}/send', [NewsletterController::class, 'send'])->name('newsletter.send');

        // Rapports SIM - Routes déjà définies plus haut dans le groupe admin (lignes 468-476)
        // Route::resource('sim-reports', SimReportsController::class); // CONFLIT - déjà remplacé par routes manuelles ligne 468
        // Route::post('/sim-reports/generate', [SimReportsController::class, 'generate'])->name('sim-reports.generate'); // DÉJÀ DÉFINI ligne 470
        // Route::get('/sim-reports/{report}/download', [SimReportsController::class, 'download'])->name('sim-reports.download'); // DÉJÀ DÉFINI ligne 471
        
        // Routes pour les notifications (centre de notifications)
        Route::get('notifications', [\App\Http\Controllers\Admin\NotificationsController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{id}', [\App\Http\Controllers\Admin\NotificationsController::class, 'show'])->name('notifications.show');
        Route::post('notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('notifications/{id}/mark-unread', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::post('notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('notifications', [\App\Http\Controllers\Admin\NotificationsController::class, 'store'])->name('notifications.store');
        Route::delete('notifications/{id}', [\App\Http\Controllers\Admin\NotificationsController::class, 'destroy'])->name('notifications.destroy');
        
        // Routes messages déjà définies plus haut dans le groupe admin (lignes 454-460)
        // Route::get('messages', [AdminMessageController::class, 'index'])->name('messages.index');
        // Route::get('messages/{id}', [AdminMessageController::class, 'show'])->name('messages.show');
        // Route::post('messages/mark-read', [AdminMessageController::class, 'markAsRead'])->name('messages.mark-read');
        // Route::post('messages/mark-all-read', [AdminMessageController::class, 'markAllAsRead'])->name('messages.mark-all-read');
        Route::post('messages/{id}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');
        Route::delete('messages/{id}', [AdminMessageController::class, 'destroy'])->name('messages.destroy');

        // Donations (lecture + export)
        Route::get('/donations', [\App\Http\Controllers\Admin\DonationController::class, 'index'])->name('donations.index');
        Route::get('/donations/{id}', [\App\Http\Controllers\Admin\DonationController::class, 'show'])->name('donations.show');
        Route::delete('/donations/{id}', [\App\Http\Controllers\Admin\DonationController::class, 'destroy'])->name('donations.destroy');
        Route::get('/donations/export', [\App\Http\Controllers\Admin\DonationController::class, 'export'])->name('donations.export');

        // FAQ (CRUD complet)
        Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class)->except(['show']);
        Route::post('/faqs/{id}/toggle-published', [\App\Http\Controllers\Admin\FaqController::class, 'togglePublished'])->name('faqs.toggle-published');

        // Projets (CRUD complet)
        Route::resource('projets', \App\Http\Controllers\Admin\ProjetController::class)->except(['show']);

        // Documents publics (CRUD complet)
        Route::resource('documents', \App\Http\Controllers\Admin\DocumentController::class)->except(['show']);
        Route::get('/documents/{id}/download', [\App\Http\Controllers\Admin\DocumentController::class, 'download'])->name('documents.download');

        // Partenaires techniques (CRUD complet)
        Route::resource('partenaires', \App\Http\Controllers\Admin\PartenaireController::class)->except(['show']);

        // (routes DRH Tabaski déplacées vers groupe drh-access ci-dessous)

        // SIM — Suivi collecteurs terrain (admin général)
        Route::prefix('sim')->name('sim.')->group(function () {
            Route::get('/suivi', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'index'])->name('suivi');
            Route::get('/suivi-temps-reel', fn() => view('supervisor.live-tracking'))->name('suivi-live');
            Route::get('/collecteurs/{id}', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'collectorDetails'])->name('collector');
            Route::get('/collectes', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'collections'])->name('collectes');
        });
    });
});

// Routes CTC - Conseil Technique de la Communication (publications, actualités, rapports, newsletter, galerie)
Route::prefix('ctc')->name('ctc.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\CTCLoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login/reset-rate-limit', [\App\Http\Controllers\Auth\CTCLoginController::class, 'resetRateLimit'])->name('login.reset-rate-limit');
    Route::post('/login', [\App\Http\Controllers\Auth\CTCLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Auth\CTCLoginController::class, 'logout'])->name('logout');

    Route::middleware(['ctc-admin'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CommunicationsController::class, 'index'])->name('dashboard');
        Route::get('/communications', [\App\Http\Controllers\Admin\CommunicationsController::class, 'index'])->name('communications.index');
        Route::get('/communications/realtime-stats', [\App\Http\Controllers\Admin\CommunicationsController::class, 'realtimeStats'])->name('communications.realtime-stats');

        Route::resource('actualites', \App\Http\Controllers\Admin\ActualitesController::class);
        Route::get('actualites/{id}/download', [\App\Http\Controllers\Admin\ActualitesController::class, 'downloadDocument'])->name('actualites.download');
        Route::get('actualites/{id}/preview', [\App\Http\Controllers\Admin\ActualitesController::class, 'preview'])->name('actualites.preview');

        Route::resource('galerie', \App\Http\Controllers\Admin\GalerieController::class);
        Route::post('/galerie/{id}/toggle-status', [\App\Http\Controllers\Admin\GalerieController::class, 'toggleStatus'])->name('galerie.toggle-status');

        Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('/newsletter/export', [\App\Http\Controllers\Admin\NewsletterController::class, 'exportSubscribers'])->name('newsletter.export');
        Route::get('/newsletter/stats', [\App\Http\Controllers\Admin\NewsletterController::class, 'getStats'])->name('newsletter.stats');
        Route::get('/newsletter/analytics', [\App\Http\Controllers\Admin\NewsletterController::class, 'getAnalytics'])->name('newsletter.analytics');

        Route::get('/messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [\App\Http\Controllers\Admin\MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{id}/mark-read', [\App\Http\Controllers\Admin\MessageController::class, 'markAsRead'])->name('messages.mark-read');
        Route::post('/messages/mark-all-read', [\App\Http\Controllers\Admin\MessageController::class, 'markAllAsRead'])->name('messages.mark-all-read');
        Route::post('/messages/{id}/reply', [\App\Http\Controllers\Admin\MessageController::class, 'reply'])->name('messages.reply');

        Route::get('/sim-reports', [\App\Http\Controllers\Admin\SimReportsController::class, 'index'])->name('sim-reports.index');
        Route::get('/sim-reports/create', [\App\Http\Controllers\Admin\SimReportsController::class, 'create'])->name('sim-reports.create');
        Route::post('/sim-reports', [\App\Http\Controllers\Admin\SimReportsController::class, 'store'])->name('sim-reports.store');
        Route::get('/sim-reports/{id}', [\App\Http\Controllers\Admin\SimReportsController::class, 'show'])->name('sim-reports.show');
        Route::get('/sim-reports/{id}/edit', [\App\Http\Controllers\Admin\SimReportsController::class, 'edit'])->name('sim-reports.edit');
        Route::put('/sim-reports/{id}', [\App\Http\Controllers\Admin\SimReportsController::class, 'update'])->name('sim-reports.update');
        Route::delete('/sim-reports/{id}', [\App\Http\Controllers\Admin\SimReportsController::class, 'destroy'])->name('sim-reports.destroy');
        Route::post('/sim-reports/upload', [\App\Http\Controllers\Admin\SimReportsController::class, 'uploadDocument'])->name('sim-reports.upload');
        Route::post('/sim-reports/generate', [\App\Http\Controllers\Admin\SimReportsController::class, 'generateReport'])->name('sim-reports.generate');
        Route::get('/sim-reports/{id}/download', [\App\Http\Controllers\Admin\SimReportsController::class, 'download'])->name('sim-reports.download');

        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationsController::class, 'index'])->name('notifications.index');
    });
});

// Routes publiques
Route::get('/about', [\App\Http\Controllers\Public\AboutController::class, 'index'])->name('public.about');
Route::get('/about/stats', [\App\Http\Controllers\Public\AboutController::class, 'getStats'])->name('public.about.stats');

// Routes publiques - Actualités
Route::get('/actualites', [\App\Http\Controllers\Public\ActualitesController::class, 'index'])->name('public.actualites');
Route::get('/actualites/{id}', [\App\Http\Controllers\Public\ActualitesController::class, 'show'])->name('public.actualites.show');
Route::get('/actualites/{id}/download', [\App\Http\Controllers\Public\ActualitesController::class, 'downloadDocument'])->name('public.actualites.download');
Route::get('/actualites/stats', [\App\Http\Controllers\Public\ActualitesController::class, 'getStats'])->name('public.actualites.stats');

// Routes publiques - Galerie
Route::get('/galerie', [\App\Http\Controllers\Public\GalerieController::class, 'index'])->name('public.galerie');
Route::get('/galerie/stats', [\App\Http\Controllers\Public\GalerieController::class, 'getStats'])->name('public.galerie.stats');

// Routes publiques - Messages et Newsletter (routes de test supprimées)

// Route sim-reports utilisant le contrôleur
Route::get('/sim-reports', [\App\Http\Controllers\Public\SimReportsController::class, 'index'])->name('sim-reports.index');

// Routes publiques - Rapports SIM (version originale commentée)
// Route::get('/sim-reports', [\App\Http\Controllers\Public\SimReportsController::class, 'index'])->name('sim-reports.index');
Route::get('/sim-reports/{id}', [\App\Http\Controllers\Public\SimReportsController::class, 'show'])->name('sim-reports.show');
Route::get('/sim-reports/{id}/download', [\App\Http\Controllers\Public\SimReportsController::class, 'download'])->name('sim-reports.download');

// Route proxy météo — côté serveur pour éviter CORS et exposer la clé API
Route::get('/api/weather', function (\Illuminate\Http\Request $request) {
    $lat = (float) ($request->query('lat', 14.6928)); // Dakar par défaut
    $lon = (float) ($request->query('lon', -17.4467));
    $apiKey = config('services.openweather.key');

    if (!$apiKey) {
        return response()->json(['error' => 'API key not configured'], 503);
    }

    $cacheKey = 'weather_' . round($lat, 2) . '_' . round($lon, 2);

    $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($lat, $lon, $apiKey) {
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=fr&appid={$apiKey}";
        $response = \Illuminate\Support\Facades\Http::timeout(8)->get($url);

        if ($response->successful()) {
            return $response->json();
        }
        return null;
    });

    if (!$data || !isset($data['main'])) {
        return response()->json(['error' => 'Weather data unavailable'], 503);
    }

    return response()->json([
        'city'        => $data['name'] ?? 'Dakar',
        'country'     => $data['sys']['country'] ?? 'SN',
        'temp'        => (int) round($data['main']['temp']),
        'feels_like'  => (int) round($data['main']['feels_like']),
        'humidity'    => $data['main']['humidity'],
        'pressure'    => $data['main']['pressure'],
        'wind'        => (int) round(($data['wind']['speed'] ?? 0) * 3.6),
        'wind_dir'    => $data['wind']['deg'] ?? null,
        'visibility'  => isset($data['visibility']) ? round($data['visibility'] / 1000, 1) : null,
        'description' => $data['weather'][0]['description'] ?? '',
        'icon'        => $data['weather'][0]['icon'] ?? '01d',
        'updated_at'  => now()->format('H:i'),
    ])->withHeaders(['Cache-Control' => 'no-cache']);
})->name('api.weather');

// Route proxy météo — prévisions 5 jours
Route::get('/api/weather/forecast', function (\Illuminate\Http\Request $request) {
    $lat = (float) ($request->query('lat', 14.6928));
    $lon = (float) ($request->query('lon', -17.4467));
    $apiKey = config('services.openweather.key');

    if (!$apiKey) {
        return response()->json(['error' => 'API key not configured'], 503);
    }

    $cacheKey = 'weather_forecast_' . round($lat, 2) . '_' . round($lon, 2);

    $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function () use ($lat, $lon, $apiKey) {
        $url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&lang=fr&cnt=40&appid={$apiKey}";
        $response = \Illuminate\Support\Facades\Http::timeout(8)->get($url);
        return $response->successful() ? $response->json() : null;
    });

    if (!$data || empty($data['list'])) {
        return response()->json(['error' => 'Forecast data unavailable'], 503);
    }

    // Grouper par jour (garder la prévision de 12h00 pour chaque jour)
    $byDay = [];
    foreach ($data['list'] as $item) {
        $day = date('Y-m-d', $item['dt']);
        $hour = (int) date('H', $item['dt']);
        if (!isset($byDay[$day]) || abs($hour - 12) < abs((int) date('H', $byDay[$day]['dt']) - 12)) {
            $byDay[$day] = $item;
        }
    }

    $today = date('Y-m-d');
    $forecast = [];
    foreach ($byDay as $day => $item) {
        if ($day === $today) continue; // Exclure aujourd'hui (déjà dans /api/weather)
        $forecast[] = [
            'date'        => $day,
            'day_label'   => ucfirst(\Carbon\Carbon::parse($day)->locale('fr')->dayName),
            'temp_max'    => (int) round($item['main']['temp_max']),
            'temp_min'    => (int) round($item['main']['temp_min']),
            'temp'        => (int) round($item['main']['temp']),
            'humidity'    => $item['main']['humidity'],
            'description' => $item['weather'][0]['description'] ?? '',
            'icon'        => $item['weather'][0]['icon'] ?? '01d',
            'wind'        => (int) round(($item['wind']['speed'] ?? 0) * 3.6),
            'rain_prob'   => isset($item['pop']) ? (int) round($item['pop'] * 100) : 0,
        ];
        if (count($forecast) >= 5) break;
    }

    return response()->json([
        'city'     => $data['city']['name'] ?? 'Dakar',
        'country'  => $data['city']['country'] ?? 'SN',
        'forecast' => $forecast,
    ])->withHeaders(['Cache-Control' => 'no-cache']);
})->name('api.weather.forecast');

// Routes API partagées pour données temps réel (Admin et DG)
Route::prefix('api/shared')->name('api.shared.')->group(function () {
    Route::get('/realtime-data', [\App\Http\Controllers\Shared\RealtimeDataController::class, 'getSharedData'])->name('realtime-data');
    Route::get('/performance-stats', [\App\Http\Controllers\Shared\RealtimeDataController::class, 'getPerformanceStats'])->name('performance-stats');
    Route::get('/alerts', [\App\Http\Controllers\Shared\RealtimeDataController::class, 'getAlerts'])->name('alerts');
});

// Routes Admin et DG supprimées

// Routes DG supprimées

// Interfaces Agent et Responsable désactivées — Admin et DG uniquement pour le moment
Route::prefix('responsable')->name('responsable.')->group(function () {
    Route::get('/', fn () => view('auth.interface-desactivee'))->name('dashboard');
    Route::get('/login', fn () => view('auth.interface-desactivee'))->name('login');
    Route::post('/login', fn () => redirect()->route('responsable.login')->with('message', 'Interface désactivée.'));
    Route::match(['get', 'post'], '/{path}', fn () => view('auth.interface-desactivee'))->where('path', '.*')->name('fallback');
});

Route::prefix('agent')->name('agent.')->group(function () {
    Route::get('/', fn () => view('auth.interface-desactivee'))->name('dashboard');
    Route::get('/login', fn () => view('auth.interface-desactivee'))->name('login');
    Route::post('/login', fn () => redirect()->route('agent.login')->with('message', 'Interface désactivée.'));
    Route::match(['get', 'post'], '/{path}', fn () => view('auth.interface-desactivee'))->where('path', '.*')->name('fallback');
});

Route::get('/entrepot', fn () => view('auth.interface-desactivee'));
Route::match(['get', 'post'], '/entrepot/{path}', fn () => view('auth.interface-desactivee'))->where('path', '.*');

// Interface Collecteurs SIM (désactivée - utiliser l'app mobile)
Route::match(['get', 'post'], '/collecteur/{path?}', fn () => view('auth.interface-desactivee'))->where('path', '.*');

// API temps réel collecteurs (utilisée par dashboard superviseur + app mobile)
Route::prefix('api/mobile')->name('api.mobile.')->group(function () {
    Route::get('/collectors/locations', [App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'getActiveCollectors'])->name('collectors.locations');
    Route::post('/collectors/location', [App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'updateLocation'])->name('collectors.location.update');
});

// Routes publiques — Avance Tabaski 2026
Route::get('/avance-tabaski', [\App\Http\Controllers\Public\TabaskiController::class, 'form'])->name('tabaski.form');
Route::post('/avance-tabaski/search', [\App\Http\Controllers\Public\TabaskiController::class, 'search'])->name('tabaski.search');
Route::post('/avance-tabaski/submit', [\App\Http\Controllers\Public\TabaskiController::class, 'submit'])->name('tabaski.submit');

// Routes DRH — Avances Tabaski (accès drh + admin)
Route::prefix('admin/drh')->name('admin.drh.')->middleware(['drh-access'])->group(function () {
    Route::get('/avances-tabaski', [\App\Http\Controllers\Drh\AvanceTabaskiController::class, 'index'])->name('tabaski.index');
    Route::post('/avances-tabaski/settings', [\App\Http\Controllers\Drh\AvanceTabaskiController::class, 'updateSettings'])->name('tabaski.settings');
    Route::get('/avances-tabaski/export-csv', [\App\Http\Controllers\Drh\AvanceTabaskiController::class, 'exportCsv'])->name('tabaski.export-csv');
    Route::get('/avances-tabaski/print', [\App\Http\Controllers\Drh\AvanceTabaskiController::class, 'exportPdf'])->name('tabaski.print');
});

// Redirect admin/sim/suivi et /collecteurs vers l'interface superviseur
Route::prefix('admin/sim')->middleware(['admin'])->group(function () {
    Route::get('/suivi',       fn() => redirect('/superviseur/'))->name('admin.sim.suivi');
    Route::get('/collecteurs', fn() => redirect('/superviseur/'))->name('admin.sim.collecteurs');
});

// Interface Superviseur SIM (suivi des collecteurs)
Route::prefix('superviseur')->name('supervisor.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\SupervisorLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\SupervisorLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Auth\SupervisorLoginController::class, 'logout'])->name('logout');

    Route::middleware(['supervisor'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/suivi-temps-reel', fn() => view('supervisor.live-tracking'))->name('live.tracking');
        Route::get('/collecteur/{id}', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'collectorDetails'])->name('collector.details');
        Route::get('/collectes', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'collections'])->name('collections');
    });
});

// Routes globales pour les nouvelles fonctionnalités
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/clear-read', [\App\Http\Controllers\NotificationController::class, 'clearRead'])->name('clear-read');
        Route::delete('/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/preferences', [\App\Http\Controllers\NotificationController::class, 'preferences'])->name('preferences');
        Route::post('/preferences', [\App\Http\Controllers\NotificationController::class, 'updatePreferences'])->name('update-preferences');
        Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'getUnread'])->name('unread');
    });
    
    // Recherche
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SearchController::class, 'global'])->name('global');
        Route::get('/quick', [\App\Http\Controllers\SearchController::class, 'quickSearch'])->name('quick');
        Route::get('/suggestions', [\App\Http\Controllers\SearchController::class, 'suggestions'])->name('suggestions');
    });
    
    // Export
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/stocks', [\App\Http\Controllers\ExportController::class, 'exportStocks'])->name('stocks');
        Route::get('/reports', [\App\Http\Controllers\ExportController::class, 'exportReports'])->name('reports');
        Route::get('/template/{type}', [\App\Http\Controllers\ExportController::class, 'downloadTemplate'])->name('template');
    });
});


