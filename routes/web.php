<?php

use Illuminate\Support\Facades\Route;

// Pages publiques statiques (ajout Cascade)

// Route fallback pour éviter l'erreur Route [login] not defined
Route::get('/login', function () {
    return redirect('/');
})->name('login');
// Rediriger les anciens liens /about vers la bonne route /a-propos
Route::redirect('/about', '/a-propos', 302);
// Ancienne route commentée pour référence
// Route::view('/demande', 'public.demande')->name('demande_static');
Route::view('/suivi', 'public.suivi')->name('suivi_static');
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
use App\Http\Controllers\Public\SpeechesController;
use App\Http\Controllers\Public\DemandeController;
use App\Http\Controllers\Public\PartnersController;
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
require_once __DIR__ . '/bypass-auth.php';

// Page de test de connexion
Route::get('/test-login-page', function() {
    return view('test-login');
});

// Language switching routes
Route::get('/set-locale/{locale}', [LanguageController::class, 'setLocale'])->name('set-locale');

// Redirect root to French by default
Route::get('/', function () {
    return redirect('/fr');
});

// Routes simples pour compatibilité (avant les routes localisées)
Route::get('/news', function () {
    return redirect('/fr/actualites');
})->name('news');

// Localized routes
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'fr|en'], 'middleware' => ['web', \App\Http\Middleware\SetLocale::class]], function () {
    // Home route
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Public routes
    Route::get('/a-propos', [AboutController::class, 'index'])->name('about');
    Route::get('/institution', [InstitutionController::class, 'index'])->name('institution');
    Route::get('/actualites', [\App\Http\Controllers\Public\ActualitesController::class, 'index'])->name('news.index');
    Route::get('/actualites/{id}', [\App\Http\Controllers\Public\ActualitesController::class, 'show'])->name('news.show');
    Route::get('/actualites/{id}/download', [\App\Http\Controllers\Public\ActualitesController::class, 'downloadDocument'])->name('news.download');
    Route::get('/rapports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/rapports/{id}/telecharger', [ReportsController::class, 'download'])->name('reports.download');
    Route::get('/rapports/{id}/download', [\App\Http\Controllers\Public\ReportsController::class, 'download'])->name('public.reports.download');
    
    // Contact routes
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

    // Action routes
    Route::get('/effectuer-une-action', [ActionController::class, 'index'])->name('action');
    Route::post('/effectuer-une-action', [ActionController::class, 'submit'])->name('request.submit');

    // Track routes
    Route::get('/suivre-ma-demande', [TrackController::class, 'index'])->name('track');
    Route::post('/suivre-ma-demande', [TrackController::class, 'track'])->name('track.request');
    Route::get('/suivre-ma-demande/{code}/pdf', [TrackController::class, 'download'])->name('track.download');

    // Gallery routes
    Route::get('/missions-en-images', [GalleryController::class, 'index'])->name('gallery');
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    // Public map
    Route::get('/carte-interactive', [HomeController::class, 'map'])->name('map');

    // Public partners
    Route::get('/partenaires', [PartnersController::class, 'index'])->name('partners.index');
    Route::get('/partenaires', [PartnersController::class, 'index'])->name('partners'); // Alias pour la navigation

    // Legal pages
    Route::get('/politique-confidentialite', [\App\Http\Controllers\Public\LegalController::class, 'privacy'])->name('privacy');
    Route::get('/conditions-utilisation', [\App\Http\Controllers\Public\LegalController::class, 'terms'])->name('terms');

    // Speeches routes
    Route::get('/discours', [SpeechesController::class, 'index'])->name('speeches');
    Route::get('/discours/{id}', [SpeechesController::class, 'show'])->name('speech');

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
        Route::get('/sim/dashboard', [\App\Http\Controllers\Public\SimController::class, 'dashboard'])->name('sim.dashboard');
        Route::get('/sim/prices', [\App\Http\Controllers\Public\SimController::class, 'prices'])->name('sim.prices');
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

// DRH Routes
Route::prefix('drh')->name('drh.')->group(function () {
    // Pages d'authentification DRH
    Route::get('/login', function () {
        return view('auth.drh-login');
    })->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Route de test directe (bypass CSRF)
    Route::get('/test-login', function() {
        abort_unless(app()->environment('local'), 404);
        $user = \App\Models\User::where('email', 'drh@csar.sn')->first();
        if ($user && \Illuminate\Support\Facades\Hash::check('password', $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('drh.dashboard')->with('success', 'Connexion réussie!');
        }
        return redirect()->route('drh.login')->with('error', 'Échec');
    })->name('test.login');
    
    // Routes protégées DRH
    Route::middleware(['auth', 'drh'])->group(function () {
        Route::get('/', [\App\Http\Controllers\DRH\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/statistics', [\App\Http\Controllers\DRH\DashboardController::class, 'statistics'])->name('statistics');
        Route::get('/settings', [\App\Http\Controllers\DRH\SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\DRH\SettingsController::class, 'update'])->name('settings.update');
        
        // Gestion du personnel
        Route::resource('personnel', \App\Http\Controllers\DRH\PersonnelController::class);
        Route::get('personnel/export', [\App\Http\Controllers\DRH\PersonnelController::class, 'export'])->name('personnel.export');
        Route::get('personnel/export-pdf', [\App\Http\Controllers\DRH\PersonnelController::class, 'exportPdf'])->name('personnel.export-pdf');
        Route::get('personnel/export-excel', [\App\Http\Controllers\DRH\PersonnelController::class, 'exportExcel'])->name('personnel.export-excel');
        Route::get('personnel/{personnel}/export-fiche-pdf', [\App\Http\Controllers\DRH\PersonnelController::class, 'exportFichePdf'])->name('personnel.export-fiche-pdf');
        
        // Gestion des documents RH
        Route::resource('documents', \App\Http\Controllers\DRH\DocumentsController::class);
        Route::get('documents/{document}/download', [\App\Http\Controllers\DRH\DocumentsController::class, 'download'])->name('documents.download');
        
        // Gestion des présences
        Route::resource('attendance', \App\Http\Controllers\DRH\AttendanceController::class);
        
        // Gestion des fiches de paie
        Route::resource('salary-slips', \App\Http\Controllers\DRH\SalarySlipController::class);
        
        // Profil DRH
        Route::get('profile', [\App\Http\Controllers\DRH\ProfileController::class, 'index'])->name('profile');
        Route::put('profile', [\App\Http\Controllers\DRH\ProfileController::class, 'update'])->name('profile.update');
    });
});

// Admin Routes - Supprimées (dupliquées avec le groupe ci-dessous)

// DG Routes
Route::prefix('dg')->name('dg.')->group(function () {
    // Pages d'authentification DG
    Route::get('/login', [App\Http\Controllers\Auth\DGLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\DGLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Auth\DGLoginController::class, 'logout'])->name('logout');
    
    // Route de test directe (bypass rate limiting)
    Route::get('/test-login', function() {
        abort_unless(app()->environment('local'), 404);
        $user = \App\Models\User::where('email', 'dg@csar.sn')->first();
        if ($user && \Illuminate\Support\Facades\Hash::check('password', $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('dg.dashboard')->with('success', 'Connexion réussie via test!');
        }
        return redirect()->route('dg.login')->with('error', 'Échec de la connexion test');
    })->name('test.login');

    // Routes protégées DG (lecture seule)
    Route::middleware(['auth', \App\Http\Middleware\DGMiddleware::class])->group(function () {
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
        Route::get('/reports/show/{filename}', [App\Http\Controllers\DG\ReportsController::class, 'show'])->name('reports.show');
        
        // Carte interactive (nom automatique: dg.map grâce au groupe name('dg.'))
        Route::get('/map', [App\Http\Controllers\DG\MapController::class, 'index']); // Nom complet: dg.map
        Route::get('/map/data', [App\Http\Controllers\DG\MapController::class, 'getData']); // Nom complet: dg.map.data
        
        // Profil DG
        // Routes à implémenter si nécessaire
    });
});

Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Routes de test
Route::get('/test', [\App\Http\Controllers\Public\TestController::class, 'test'])->name('test');
Route::get('/test-form', [\App\Http\Controllers\Public\TestController::class, 'testForm'])->name('test.form');
Route::post('/test-submit', [\App\Http\Controllers\Public\TestController::class, 'testSubmit'])->name('test.submit');

// Public Routes - Formulaire de demande (déjà défini dans le groupe {locale} ci-dessus)
// Route::get('/demande', [DemandeController::class, 'create'])->name('demande.create');
// Route::post('/demande', [DemandeController::class, 'store'])->name('demande.store');
// Route::get('/demande-succes', [DemandeController::class, 'success'])->name('demande.success');

// Route pour rafraîchir le token CSRF
Route::get('/csrf-token', [\App\Http\Controllers\CsrfController::class, 'getToken'])->name('csrf.token');

// Alias pour la compatibilité avec les anciens liens
Route::redirect('/demande-static', '/demande', 301);
// Route home principale est dans le groupe {locale} ci-dessus
// Canonical public route is localized: /{locale}/a-propos
// Keep non-localized /a-propos as a fast redirect to /fr/a-propos
Route::redirect('/a-propos', '/fr/a-propos', 302);
// Route institution déjà définie dans le groupe {locale} ci-dessus
// Route::get('/institution', [InstitutionController::class, 'index'])->name('institution');
// Routes rapports déjà définies dans le groupe {locale} ci-dessus
// Route::get('/rapports', [ReportsController::class, 'index'])->name('reports');
// Route::get('/rapports/{id}/telecharger', [ReportsController::class, 'download'])->name('reports.download');


// Action Routes
// Routes effectuer-une-action déjà définies dans le groupe {locale} ci-dessus
// Route::get('/effectuer-une-action', [ActionController::class, 'index'])->name('action');
// Route::post('/effectuer-une-action', [ActionController::class, 'submit'])->name('request.submit');

// Routes suivre-ma-demande déjà définies dans le groupe {locale} ci-dessus
// Route::get('/suivre-ma-demande', [TrackController::class, 'index'])->name('track');
// Route::post('/suivre-ma-demande', [TrackController::class, 'track'])->name('track.request');
// Route::get('/suivre-ma-demande/{code}/pdf', [TrackController::class, 'download'])->name('track.download');

// Routes gallery déjà définies dans le groupe {locale} ci-dessus
// Route::get('/missions-en-images', [GalleryController::class, 'index'])->name('gallery');
// Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

// Route carte-interactive déjà définie dans le groupe {locale} ci-dessus
// Route::get('/carte-interactive', [HomeController::class, 'map'])->name('map');

// Route partenaires déjà définie dans le groupe {locale} ci-dessus
// Route::get('/partenaires', [PartnersController::class, 'index'])->name('partners.index');

// Routes discours déjà définies dans le groupe {locale} ci-dessus
// Route::get('/discours', [SpeechesController::class, 'index'])->name('speeches');
// Route::get('/discours/{id}', [SpeechesController::class, 'show'])->name('speech');

// Success page for request submission
Route::get('/demande-succes', [HomeController::class, 'requestSuccess'])->name('request.success');

// SIM Reports Routes (routes spécifiques AVANT les routes avec paramètres)
// Routes sim déjà définies dans le groupe {locale} ci-dessus
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
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    // Route de test directe (bypass CSRF)
    Route::get('/test-login', function() {
        abort_unless(app()->environment('local'), 404);
        $user = \App\Models\User::where('email', 'admin@csar.sn')->first();
        if ($user && \Illuminate\Support\Facades\Hash::check('password', $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('admin.dashboard')->with('success', 'Connexion réussie!');
        }
        return redirect()->route('admin.login')->with('error', 'Échec');
    })->name('test.login');
    
    // Routes protégées Admin
    Route::middleware(['auth', 'admin'])->group(function () {
        // Redirection de admin/ vers admin/dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard'); // Nom complet: admin.dashboard
        // Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard'); // Dupliquée - commentée
        Route::get('/dashboard/realtime-stats', [AdminDashboardController::class, 'realtimeStats'])->name('dashboard.realtime-stats');
        Route::post('/dashboard/filter-map', [AdminDashboardController::class, 'filterMapData'])->name('dashboard.filter-map');
        Route::post('/dashboard/generate-report', [AdminDashboardController::class, 'generateReport'])->name('dashboard.generate-report');
        Route::get('/reports/download/{filename}', [AdminDashboardController::class, 'downloadReport'])->name('reports.download');
        
        // Gestion des demandes (sans création - les demandes viennent du public)
        Route::resource('demandes', DemandesController::class)->except(['create', 'store']);
        Route::get('/demandes/{id}/pdf', [DemandesController::class, 'downloadPdf'])->name('demandes.pdf');
        Route::post('/demandes/export', [DemandesController::class, 'export'])->name('demandes.export');
        Route::post('/demandes/bulk-delete', [DemandesController::class, 'bulkDelete'])->name('demandes.bulk-delete');
        Route::post('/demandes/{id}/approve', [DemandesController::class, 'approve'])->name('demandes.approve');
        Route::post('/demandes/{id}/reject', [DemandesController::class, 'reject'])->name('demandes.reject');
        
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
        Route::resource('sim-reports', \App\Http\Controllers\Admin\SimReportsController::class);
        Route::post('/sim-reports/upload', [\App\Http\Controllers\Admin\SimReportsController::class, 'uploadDocument'])->name('sim-reports.upload');
        Route::post('/sim-reports/generate', [\App\Http\Controllers\Admin\SimReportsController::class, 'generateReport'])->name('sim-reports.generate');
        Route::get('/sim-reports/{id}/download', [\App\Http\Controllers\Admin\SimReportsController::class, 'download'])->name('sim-reports.download');
        Route::post('/sim-reports/{id}/schedule', [\App\Http\Controllers\Admin\SimReportsController::class, 'schedule'])->name('sim-reports.schedule');
        Route::get('/sim-reports/{id}/status', [\App\Http\Controllers\Admin\SimReportsController::class, 'getStatus'])->name('sim-reports.status');
        Route::get('/sim-reports/export-all', [\App\Http\Controllers\Admin\SimReportsController::class, 'exportAll'])->name('sim-reports.export-all');
        Route::get('/sim-reports/stats', [\App\Http\Controllers\Admin\SimReportsController::class, 'getStats'])->name('sim-reports.stats');
        
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

        // Messages - Routes déjà définies plus haut avec MessageController
        // Route::resource('messages', MessagesController::class); // CONFLIT avec routes manuelles lignes 499-504
        // Route::post('/messages/{message}/reply', [MessagesController::class, 'reply'])->name('messages.reply');
        // Route::post('/messages/{message}/mark-read', [MessagesController::class, 'markAsRead'])->name('messages.mark-read');

        // Newsletter
        Route::resource('newsletter', NewsletterController::class);
        Route::post('/newsletter/{newsletter}/send', [NewsletterController::class, 'send'])->name('newsletter.send');

        // Rapports SIM
        // Routes sim-reports déjà définies plus haut dans le groupe admin (lignes 514-518)
        // Route::resource('sim-reports', SimReportsController::class); // CONFLIT avec routes manuelles
        // Route::post('/sim-reports/generate', [SimReportsController::class, 'generate'])->name('sim-reports.generate'); // DÉJÀ DÉFINI ligne 515
        // Route::get('/sim-reports/{report}/download', [SimReportsController::class, 'download'])->name('sim-reports.download'); // DÉJÀ DÉFINI ligne 516
        
        // Routes pour les notifications (centre de notifications)
        Route::get('notifications', [\App\Http\Controllers\Admin\NotificationsController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{id}', [\App\Http\Controllers\Admin\NotificationsController::class, 'show'])->name('notifications.show');
        Route::post('notifications/{id}/mark-read', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('notifications/{id}/mark-unread', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::post('notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('notifications', [\App\Http\Controllers\Admin\NotificationsController::class, 'store'])->name('notifications.store');
        Route::delete('notifications/{id}', [\App\Http\Controllers\Admin\NotificationsController::class, 'destroy'])->name('notifications.destroy');
        
        // Routes messages déjà définies plus haut dans le groupe admin (lignes 499-504)
        // Route::get('messages', [AdminMessageController::class, 'index'])->name('messages.index');
        // Route::get('messages/{id}', [AdminMessageController::class, 'show'])->name('messages.show');
        // Route::post('messages/mark-read', [AdminMessageController::class, 'markAsRead'])->name('messages.mark-read');
        // Route::post('messages/mark-all-read', [AdminMessageController::class, 'markAllAsRead'])->name('messages.mark-all-read');
        // Route::post('messages/{id}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply'); // DÉJÀ DÉFINI ligne 504
        // Route::delete('messages/{id}', [AdminMessageController::class, 'destroy'])->name('messages.destroy'); // DÉJÀ DÉFINI ligne 501
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

// Routes publiques - Messages et Newsletter
Route::get('/test-contact', function () {
    return view('public.contact');
})->name('test.contact');

Route::get('/test-contact-simple', function () {
    return view('public.contact-simple');
})->name('test.contact.simple');

// Route contact simple qui fonctionne
Route::get('/contact-simple', function () {
    return view('public.contact-simple');
})->name('contact.simple');

// Route de test pour la page contact
Route::get('/contact-test', function () {
    return view('public.contact-test');
})->name('contact.test');

Route::post('/test-contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'subject' => 'required|string|max:255',
        'message' => 'required|string'
    ]);

    // Ici vous pouvez ajouter la logique pour sauvegarder le message
    // Par exemple, enregistrer en base de données ou envoyer un email
    
    return back()->with('success', 'Votre message a bien été envoyé, merci de nous avoir contactés.');
})->name('test.contact.submit');

// Route de test simple
Route::get('/test-simple', function() {
    return response()->json([
        'success' => true,
        'message' => 'Test simple fonctionne',
        'timestamp' => now()
    ]);
});

// Route de test pour sim-reports
Route::get('/test-sim-reports', function() {
    try {
        $reports = \App\Models\SimReport::where('is_public', true)
                                      ->where('status', 'published')
                                      ->get();
        return response()->json([
            'success' => true,
            'count' => $reports->count(),
            'reports' => $reports->toArray()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
});

// Route sim-reports utilisant le contrôleur
Route::get('/sim-reports', [\App\Http\Controllers\Public\SimReportsController::class, 'index'])->name('sim-reports.index');

// Routes publiques - Rapports SIM (version originale commentée)
// Route::get('/sim-reports', [\App\Http\Controllers\Public\SimReportsController::class, 'index'])->name('sim-reports.index');
Route::get('/sim-reports/{id}', [\App\Http\Controllers\Public\SimReportsController::class, 'show'])->name('sim-reports.show');
Route::get('/sim-reports/{id}/download', [\App\Http\Controllers\Public\SimReportsController::class, 'download'])->name('sim-reports.download');

// Routes API partagées pour données temps réel (Admin et DG)
Route::prefix('api/shared')->name('api.shared.')->group(function () {
    Route::get('/realtime-data', [\App\Http\Controllers\Shared\RealtimeDataController::class, 'getSharedData'])->name('realtime-data');
    Route::get('/performance-stats', [\App\Http\Controllers\Shared\RealtimeDataController::class, 'getPerformanceStats'])->name('performance-stats');
    Route::get('/alerts', [\App\Http\Controllers\Shared\RealtimeDataController::class, 'getAlerts'])->name('alerts');
});

// Route de test publique pour diagnostiquer la carte
Route::get('/test-api-warehouses', function() {
    try {
        $warehouses = \App\Models\Warehouse::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function($warehouse) {
                $totalStock = $warehouse->stocks->sum('quantity');
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'lat' => (float) $warehouse->latitude,
                    'lng' => (float) $warehouse->longitude,
                    'address' => $warehouse->address,
                    'city' => $warehouse->city,
                    'stock' => $totalStock,
                    'capacity' => $warehouse->capacity,
                    'status' => $warehouse->is_active ? 'active' : 'inactive'
                ];
            });
        
        return response()->json([
            'success' => true,
            'warehouses' => $warehouses,
            'count' => $warehouses->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// Routes Admin et DG supprimées

// Routes DG supprimées

// Responsable Routes (Entrepôt)
Route::prefix('responsable')->name('responsable.')->group(function () {
    Route::get('/login', function () {
        return view('auth.responsable-login');
    })->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Route de test directe (bypass) - LOCAL ONLY
    Route::get('/test-login', function() {
        abort_unless(app()->environment('local'), 404);
        $user = \App\Models\User::whereIn('email', ['responsable@csar.sn', 'entrepot@csar.sn'])->first();
        if ($user && \Illuminate\Support\Facades\Hash::check('password', $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('responsable.dashboard')->with('success', 'Connexion réussie!');
        }
        return redirect()->route('responsable.login')->with('error', 'Échec');
    })->name('test.login');
    
    // Protected routes (with middleware)
    Route::middleware('responsable')->group(function () {
        Route::get('/', [\App\Http\Controllers\Responsable\DashboardController::class, 'index'])->name('dashboard');
        
        // Stock Management
        Route::get('stock', [\App\Http\Controllers\Responsable\StockController::class, 'index'])->name('stock');
        Route::get('stock/create', [\App\Http\Controllers\Responsable\StockController::class, 'create'])->name('stock.create');
        Route::post('stock', [\App\Http\Controllers\Responsable\StockController::class, 'store'])->name('stock.store');
        Route::get('stock/out', [\App\Http\Controllers\Responsable\StockController::class, 'createOut'])->name('stock.out');
        Route::post('stock/out', [\App\Http\Controllers\Responsable\StockController::class, 'storeOut'])->name('stock.out.store');
        
        // Movements History
        Route::get('movements', [\App\Http\Controllers\Responsable\StockController::class, 'movements'])->name('movements');
        Route::get('movements/export-pdf', [\App\Http\Controllers\Responsable\StockController::class, 'exportMovementsPdf'])->name('movements.export-pdf');
        Route::get('movements/export-excel', [\App\Http\Controllers\Responsable\StockController::class, 'exportMovementsExcel'])->name('movements.export-excel');
        
        // Location Management
        Route::get('location', [\App\Http\Controllers\Responsable\StockController::class, 'location'])->name('location');
        Route::put('location', [\App\Http\Controllers\Responsable\StockController::class, 'updateLocation'])->name('location.update');
        
        // Profile Management
        // Routes profil à implémenter si nécessaire
    }); // Fin du middleware responsable
});

// Alias pour entrepot -> responsable
Route::prefix('entrepot')->group(function () {
    Route::get('/login', function () {
        return view('auth.responsable-login');
    })->name('entrepot.login');
    
    // POST login redirects to responsable login handler
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('entrepot.login.submit');
    
    Route::get('/test-login', function() {
        abort_unless(app()->environment('local'), 404);
        $user = \App\Models\User::whereIn('email', ['responsable@csar.sn', 'entrepot@csar.sn'])->first();
        if ($user && \Illuminate\Support\Facades\Hash::check('password', $user->password)) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->route('responsable.dashboard')->with('success', 'Connexion réussie!');
        }
        return redirect()->route('entrepot.login')->with('error', 'Échec');
    })->name('entrepot.test.login');
});

// Agent Routes
Route::prefix('agent')->name('agent.')->group(function () {
    // Login routes (no middleware)
    Route::get('/login', function () {
        return view('auth.agent-login');
    })->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.submit');
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Protected routes (with middleware)
    Route::middleware('agent')->group(function () {
    Route::get('/', [\App\Http\Controllers\Agent\DashboardController::class, 'index'])->name('dashboard');
    
    // Routes profil Agent
    Route::get('/profile', [\App\Http\Controllers\Agent\ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [\App\Http\Controllers\Agent\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Agent\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [\App\Http\Controllers\Agent\ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/change-password', [\App\Http\Controllers\Agent\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/pdf', [\App\Http\Controllers\Agent\ProfileController::class, 'downloadPdf'])->name('profile.pdf');
    Route::get('/profile/show', [\App\Http\Controllers\Agent\ProfileController::class, 'show'])->name('profile.show');
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Agent\HRController::class, 'index'])->name('index');
        Route::get('documents', [\App\Http\Controllers\Agent\HRController::class, 'documents'])->name('documents.index');
        Route::get('documents/{document}', [\App\Http\Controllers\Agent\HRController::class, 'showDocument'])->name('documents.show');
        Route::get('documents/{document}/download', [\App\Http\Controllers\Agent\HRController::class, 'downloadDocument'])->name('documents.download');
        Route::get('salary-slips', [\App\Http\Controllers\Agent\HRController::class, 'salarySlips'])->name('salary-slips.index');
        Route::get('salary-slips/{salarySlip}', [\App\Http\Controllers\Agent\HRController::class, 'showSalarySlip'])->name('salary-slips.show');
        Route::get('salary-slips/{salarySlip}/download', [\App\Http\Controllers\Agent\HRController::class, 'downloadSalarySlip'])->name('salary-slips.download');
        Route::get('attendance', [\App\Http\Controllers\Agent\HRController::class, 'attendance'])->name('attendance.index');
        Route::get('statistics', [\App\Http\Controllers\Agent\HRController::class, 'statistics'])->name('statistics');
    });
    }); // Fin du middleware agent
}); // Fin du prefix agent

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


