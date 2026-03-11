<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicContent;
use App\Models\News;
use App\Models\PublicRequest;
use App\Models\Newsletter;
use App\Models\ContactMessage;
use App\Models\Warehouse;
use App\Models\Speech;
use App\Models\HomeBackground;
use App\Models\TechnicalPartner;
use App\Models\GalleryImage;
use Illuminate\Support\Facades\Schema;
use App\Models\SimReport;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\EmailService;
use App\Services\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        // Récupération de toutes les images de fond actives, triées par ordre d'affichage
        try {
            $backgrounds = HomeBackground::active()
                ->ordered()
                ->get();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion MySQL, utiliser des valeurs par défaut
            \Log::warning('Erreur lors de la récupération des backgrounds', ['error' => $e->getMessage()]);
            $backgrounds = collect([]);
        }
            
        // Si aucune image de fond n'est définie, utiliser une valeur par défaut
        $backgroundImage = $backgrounds->isNotEmpty() ? $backgrounds->first()->image_url : asset('img/1.jpg');
        
        // Préparer les données pour le slider d'arrière-plan
        $backgroundSlider = [];
        foreach ($backgrounds as $bg) {
            $backgroundSlider[] = [
                'image' => $bg->image_url,
                'title' => $bg->title,
                'description' => $bg->description
            ];
        }
        
        // Récupérer les chiffres clés depuis la nouvelle table
        try {
            // Vérifier si la table existe avant de l'utiliser
            if (!\Illuminate\Support\Facades\Schema::hasTable('chiffres_cles')) {
                $stats = [
                    'agents' => '0',
                    'warehouses' => '0', 
                    'capacity' => '0',
                    'experience' => '0'
                ];
            } else {
                // Essayer de récupérer les chiffres clés, avec fallback en cas d'erreur
                try {
                    $chiffresCles = \App\Models\ChiffreCle::safeGetActifs()->keyBy('titre');
                    
                    $stats = [
                        'agents' => $chiffresCles->get('Agents recensés', (object)['valeur' => '137'])->valeur ?? '137',
                        'warehouses' => $chiffresCles->get('Magasins de stockage', (object)['valeur' => '71'])->valeur ?? '71',
                        'capacity' => $chiffresCles->get('Capacité de stockage', (object)['valeur' => '79 000 tonnes'])->valeur ?? '79 000 tonnes',
                        'experience' => $chiffresCles->get('Années d\'expérience', (object)['valeur' => '50+'])->valeur ?? '50+'
                    ];
                } catch (\Exception $e) {
                    // En cas d'erreur lors de la récupération, utiliser les valeurs par défaut
                    $stats = [
                        'agents' => '0',
                        'warehouses' => '0', 
                        'capacity' => '0',
                        'experience' => '0'
                    ];
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, utiliser les valeurs par défaut
            $stats = [
                'agents' => '0',
                'warehouses' => '0', 
                'capacity' => '0',
                'experience' => '0'
            ];
        }
        
        // Récupérer les actualités publiées
        try {
            $latestNews = News::where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->take(4)
                ->get();
        } catch (\Exception $e) {
            $latestNews = collect([]); // Collection vide si erreur
        }
        
        // Discours institutionnels depuis la base de données
        try {
            $latestSpeeches = Speech::orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            $latestSpeeches = collect([]);
        }
        
        // Récupération des entrepôts actifs (avec gestion d'erreur)
        try {
            $warehouses = Warehouse::where('is_active', true)->get();
        } catch (\Exception $e) {
            $warehouses = collect([]);
        }
        
        // Discours institutionnels (par défaut null en attendant la table public_contents)
        $ministerSpeech = null;
        $dgSpeech = null;
        
        // Récupération des partenaires techniques actifs (avec gestion d'erreur)
        try {
            $partners = TechnicalPartner::where('status', 'active')
                ->orderByRaw('position IS NULL, position ASC')
                ->orderBy('name')
                ->take(12)
                ->get();
        } catch (\Exception $e) {
            $partners = collect([]);
        }
        
        // Récupération des images de la galerie pour l'accueil (9 images récentes)
        try {
            $galleryImages = GalleryImage::where('status', 'active')
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(9)
                ->get();
        } catch (\Exception $e) {
            $galleryImages = collect([]);
        }
        
        // Récupération des rapports SIM publics récents (avec ou sans images)
        try {
            $simReports = SimReport::public()
                ->orderBy('published_at', 'desc')
                ->take(4)
                ->get()
                ->map(function($report) {
                    $report->excerpt = Str::limit(strip_tags($report->summary ?? $report->description), 100);
                    return $report;
                });
        } catch (\Exception $e) {
            // Si la table n'existe pas, utiliser une collection vide
            $simReports = collect([]);
        }
        
        // Récupération des publications (actualités avec documents)
        try {
            $publications = News::publications()
                ->orderBy('published_at', 'desc')
                ->limit(4)
                ->get();
        } catch (\Exception $e) {
            $publications = collect([]);
        }
            
        // Préparation des données pour la vue
        $viewData = [
            'backgroundImage' => $backgroundImage,
            'backgroundSlider' => $backgroundSlider,
            'stats' => $stats,
            'latestNews' => $latestNews,
            'warehouses' => $warehouses,
            'speeches' => $latestSpeeches,
            'ministerSpeech' => $ministerSpeech,
            'dgSpeech' => $dgSpeech,
            'partners' => $partners,
            'galleryImages' => $galleryImages,
            'simReports' => $simReports,
            'publications' => $publications,
            'requests' => []
        ];
        
        return view('public.home', $viewData);
    }

    public function about()
    {
        // Contenu par défaut en attendant la table public_contents
        $aboutContent = collect([
            'title' => (object)['value' => 'À propos du CSAR'],
            'description' => (object)['value' => 'Le Centre de Services d\'Appui au Réseau (CSAR) est une institution dédiée à l\'amélioration des services publics.'],
            'mission' => (object)['value' => 'Notre mission est de fournir un appui technique et logistique aux services publics.'],
            'vision' => (object)['value' => 'Notre vision est de devenir un partenaire de référence pour l\'amélioration des services publics.']
        ]);
        return view('public.about', compact('aboutContent'));
    }

    public function institution()
    {
        // Contenu par défaut en attendant la table public_contents
        $institutionContent = collect([
            'title' => (object)['value' => 'Institution CSAR'],
            'description' => (object)['value' => 'Le CSAR est une institution publique dédiée au développement des services publics.'],
            'organization' => (object)['value' => 'Organisation et structure institutionnelle du CSAR.'],
            'governance' => (object)['value' => 'Gouvernance et direction de l\'institution.']
        ]);
        return view('public.institution', compact('institutionContent'));
    }

    public function news()
    {
        try {
            $news = News::where('is_published', true)->orderBy('published_at', 'desc')->paginate(10);
        } catch (\Exception $e) {
            $news = collect([]);
        }
        return view('public.news', compact('news'));
    }

    public function newsShow($id)
    {
        $news = News::where('is_published', true)->findOrFail($id);
        $relatedNews = News::where('is_published', true)
            ->where('id', '!=', $id)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();
        
        return view('public.news.show', compact('news', 'relatedNews'));
    }

    public function reports()
    {
        return view('public.reports');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function action()
    {
        return view('public.request-form');
    }

    public function submitRequest(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'type' => 'required|in:aide,partenariat,audience,autre',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'region' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'urgency' => 'required|in:low,medium,high',
            'preferred_contact' => 'required|in:email,phone,sms'
        ]);

        // Vérifier les doublons de demande
        if (SecurityService::checkDuplicateRequest($request->email, $request->type, $request->description)) {
            return response()->json([
                'success' => false,
                'message' => 'Une demande similaire a déjà été soumise récemment. Veuillez patienter avant de soumettre une nouvelle demande.'
            ], 422);
        }

        // Vérifier le rate limiting
        if (!SecurityService::checkRateLimit($request->ip() . '_request', 5, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de demandes. Veuillez patienter avant de soumettre une nouvelle demande.'
            ], 429);
        }

        // Nettoyer les données
        $cleanData = SecurityService::sanitizeInput($validated);
        
        // Générer le code de suivi et le hash de doublon
        $trackingCode = SecurityService::generateTrackingCode();
        $duplicateHash = SecurityService::generateDuplicateHash($cleanData['email'], $cleanData['type'], $cleanData['description']);

        try {
            // Créer la demande
            $publicRequest = PublicRequest::create([
                'type' => $cleanData['type'],
                'full_name' => $cleanData['full_name'],
                'email' => $cleanData['email'],
                'phone' => SecurityService::cleanPhoneNumber($cleanData['phone']),
                'region' => $cleanData['region'],
                'description' => $cleanData['description'],
                'urgency' => $cleanData['urgency'],
                'preferred_contact' => $cleanData['preferred_contact'],
                'tracking_code' => $trackingCode,
                'duplicate_hash' => $duplicateHash,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Enregistrer dans le journal d'audit
            SecurityService::logAudit('request_created', 'PublicRequest', $publicRequest->id, [
                'type' => $cleanData['type'],
                'region' => $cleanData['region'],
                'urgency' => $cleanData['urgency']
            ]);

            // Créer une notification pour l'admin
            $notification = NotificationService::notifyNewRequest($publicRequest);
            
            // Diffuser la notification en temps réel
            if ($notification) {
                broadcast(new \App\Events\NotificationSent($notification, null))->toOthers();
            }

            // Envoyer les emails automatiques
            $emailService = new EmailService();
            
            // Email de confirmation au demandeur
            $emailService->sendRequestConfirmation([
                'name' => $cleanData['full_name'],
                'email' => $cleanData['email'],
                'type' => $cleanData['type'],
                'tracking_code' => $trackingCode
            ]);
            
            // Notification interne à l'admin
            $emailService->sendRequestNotification([
            'name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'tracking_code' => $trackingCode,
            'message' => $request->description
        ]);

        // Envoyer un SMS de confirmation si le numéro est fourni
        if ($request->phone) {
            try {
                $message = "Votre demande CSAR a été reçue. Code de suivi: {$trackingCode}. Nous vous contacterons bientôt.";
                // Ici vous pouvez intégrer votre service SMS
                // SmsService::send($request->phone, $message);
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la soumission
                \Log::error('Erreur SMS: ' . $e->getMessage());
            }
        }

            // Envoyer SMS si demandé
            if ($cleanData['preferred_contact'] === 'sms' && $publicRequest->phone) {
                $smsService = new \App\Services\SmsService();
                $smsService->sendRequestConfirmation($publicRequest);
            }

            return response()->json([
                'success' => true,
                'message' => 'Votre demande a été envoyée avec succès ! Nous vous contacterons dans les plus brefs délais.',
                'tracking_code' => $trackingCode,
                'redirect' => route('request.success', ['code' => $trackingCode])
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la demande', [
                'error' => $e->getMessage(),
                'data' => $cleanData
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre demande. Veuillez réessayer.'
            ], 500);
        }
    }

    public function track()
    {
        return view('public.track');
    }

    public function trackRequest(Request $request)
    {
        $request->validate([
            'tracking_code' => 'required|string|max:20',
            'phone' => 'nullable|string|max:20'
        ]);

        $publicRequest = PublicRequest::where('tracking_code', $request->tracking_code)->first();

        if (!$publicRequest) {
            return back()->withErrors(['tracking_code' => 'Code de suivi non trouvé.']);
        }

        // Vérifier le numéro de téléphone si fourni
        if ($request->phone && $publicRequest->phone !== $request->phone) {
            return back()->withErrors(['phone' => 'Le numéro de téléphone ne correspond pas à celui de la demande.']);
        }

        return view('public.track-result', compact('publicRequest'));
    }

    public function downloadPdf($code)
    {
        $publicRequest = PublicRequest::where('tracking_code', $code)->firstOrFail();
        
        $pdf = \PDF::loadView('public.request-pdf', compact('publicRequest'));
        
        return $pdf->download("demande-{$code}.pdf");
    }

    public function success()
    {
        $trackingCode = session('tracking_code');
        return view('public.success', compact('trackingCode'));
    }

    public function requestSuccess(Request $request)
    {
        $code = $request->query('code');
        $publicRequest = null;
        
        if ($code) {
            $publicRequest = PublicRequest::where('tracking_code', $code)->first();
        }
        
        return view('public.request-success-basic', compact('publicRequest', 'code'));
    }

    public function subscribeNewsletter(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ]);

        // Vérifier les doublons d'inscription
        if (SecurityService::checkDuplicateNewsletter($request->email)) {
            return back()->with('error', 'Vous êtes déjà inscrit à notre newsletter. Vérifiez votre boîte email.');
        }

        \App\Models\NewsletterSubscriber::create([
            'email' => $request->email,
            'is_active' => true,
        ]);

        // Créer une notification pour l'admin
        NotificationService::notifyNewsletterSubscription($request->email);

        // Envoyer l'email de bienvenue
        $emailService = new EmailService();
        $emailService->sendNewsletterWelcome([
            'email' => $request->email
        ]);

        return back()->with('success', 'Inscription à la newsletter réussie !');
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $fullName = $request->first_name . ' ' . $request->last_name;

        $contact = ContactMessage::create([
            'full_name' => $fullName,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message
        ]);

        // Créer une notification pour l'admin
        NotificationService::notifyNewContactMessage($contact);

        // Envoyer les emails automatiques
        $emailService = new EmailService();
        
        // Email de confirmation au visiteur
        $emailService->sendContactConfirmation([
            'name' => $fullName,
            'email' => $request->email,
            'message' => $request->message
        ]);
        
        // Notification interne à l'admin
        $emailService->sendContactNotification([
            'name' => $fullName,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message
        ]);

        return back()->with('success', 'Message envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
    }

    public function speeches()
    {
        $speeches = \App\Models\Speech::orderByDesc('date')->get();
        return view('public.speeches.index', compact('speeches'));
    }

    public function speech($id)
    {
        $speech = \App\Models\Speech::findOrFail($id);
        return view('public.speeches.show', compact('speech'));
    }

    public function gallery()
    {
        $images = \App\Models\GalleryImage::orderByDesc('created_at')->get();
        return view('public.gallery.index', compact('images'));
    }

    public function map()
    {
        // Entrepôts actifs
        $warehouses = \App\Models\Warehouse::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                    'lat' => $warehouse->latitude,
                    'lng' => $warehouse->longitude,
                    'capacity' => $warehouse->capacity,
                    'type' => 'warehouse'
                ];
            });

        // Si aucun entrepôt en base, retourner une collection vide
        if ($warehouses->isEmpty()) {
            $warehouses = collect([]);
        }

        // Statistiques générales
        $stats = [
            'total_warehouses' => $warehouses->count(),
            'regions' => ['Dakar', 'Thiès', 'Diourbel', 'Fatick', 'Kaolack', 'Kolda', 'Louga', 'Matam', 'Saint-Louis', 'Tambacounda', 'Ziguinchor', 'Kaffrine', 'Kédougou', 'Sédhiou']
        ];

        return view('public.map', compact('warehouses', 'stats'));
    }

    private function generateTrackingCode()
    {
        do {
            $code = 'CSAR' . date('Y') . strtoupper(Str::random(6));
        } while (PublicRequest::where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * Récupérer les dernières actualités (synchronisées avec l'admin)
     */
    private function getLatestNews()
    {
        try {
            return \App\Models\News::where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit(4)
                ->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération des actualités', ['error' => $e->getMessage()]);
            return collect();
        }
    }
} 