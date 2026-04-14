<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;

class PayPalService
{
    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $mode;

    public function __construct()
    {
        $this->mode = config('services.paypal.mode', 'sandbox');
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->baseUrl = $this->mode === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Obtenir le token d'accès OAuth2
     */
    private function getAccessToken()
    {
        $cacheKey = 'paypal_access_token_' . $this->mode;
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post($this->baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if (!$response->successful()) {
            throw new \Exception('PayPal OAuth failed: ' . $response->body());
        }

        $data = $response->json();
        $token = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 32400; // 9h par défaut

        \Illuminate\Support\Facades\Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 300));

        return $token;
    }

    /**
     * Créer un ordre de paiement PayPal
     */
    public function createOrder(Donation $donation, $returnUrl = null, $cancelUrl = null)
    {
        try {
            $token = $this->getAccessToken();
            
            $returnUrl = $returnUrl ?? route('donations.paypal.success', ['donation' => $donation->id]);
            $cancelUrl = $cancelUrl ?? route('donations.paypal.cancel');

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => 'DONATION_' . $donation->id,
                        'description' => 'Donation CSAR #' . $donation->id,
                        'custom_id' => (string) $donation->id,
                        'amount' => [
                            'currency_code' => $donation->currency ?? 'USD',
                            'value' => number_format($donation->amount / 600, 2, '.', '') // Conversion FCFA -> USD approximative
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ]
            ];

            $response = Http::withToken($token)
                ->withHeader('Content-Type', 'application/json')
                ->post($this->baseUrl . '/v2/checkout/orders', $orderData);

            if ($response->successful()) {
                $data = $response->json();

                // Sauvegarder l'ID de commande
                $donation->update([
                    'transaction_id' => $data['id'],
                    'metadata' => array_merge($donation->metadata ?? [], [
                        'paypal_order_id' => $data['id'],
                        'paypal_status' => $data['status']
                    ])
                ]);

                // Trouver l'URL d'approbation
                $approveUrl = null;
                foreach ($data['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approveUrl = $link['href'];
                        break;
                    }
                }

                return [
                    'success' => true,
                    'order_id' => $data['id'],
                    'status' => $data['status'],
                    'approve_url' => $approveUrl,
                    'data' => $data
                ];
            }

            Log::error('PayPal order creation failed', [
                'donation_id' => $donation->id,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'error' => 'PayPal order creation failed',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('PayPal service error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Capturer un paiement (après approbation utilisateur)
     */
    public function captureOrder($orderId)
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->withHeader('Content-Type', 'application/json')
                ->post($this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'],
                    'capture_id' => $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                    'amount' => $data['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Capture failed',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('PayPal capture error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un ordre
     */
    public function getOrderDetails($orderId)
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->get($this->baseUrl . '/v2/checkout/orders/' . $orderId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get order details'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Traiter un webhook PayPal
     */
    public function processWebhook($payload)
    {
        try {
            $eventType = $payload['event_type'] ?? null;
            $resource = $payload['resource'] ?? [];

            Log::info('PayPal webhook received', ['event' => $eventType]);

            switch ($eventType) {
                case 'CHECKOUT.ORDER.APPROVED':
                case 'CHECKOUT.ORDER.COMPLETED':
                    $orderId = $resource['id'] ?? null;
                    $donation = Donation::where('transaction_id', $orderId)->first();
                    
                    if ($donation) {
                        // Capturer le paiement
                        $capture = $this->captureOrder($orderId);
                        
                        if ($capture['success']) {
                            $donation->update([
                                'payment_status' => 'success',
                                'processed_at' => now(),
                                'metadata' => array_merge($donation->metadata ?? [], [
                                    'paypal_capture' => $capture['data']
                                ])
                            ]);
                        }
                    }
                    break;

                case 'PAYMENT.CAPTURE.COMPLETED':
                    $customId = $resource['custom_id'] ?? null;
                    if ($customId) {
                        $donation = Donation::find($customId);
                        if ($donation) {
                            $donation->update([
                                'payment_status' => 'success',
                                'processed_at' => now()
                            ]);
                        }
                    }
                    break;

                case 'CHECKOUT.ORDER.CANCELLED':
                    $orderId = $resource['id'] ?? null;
                    $donation = Donation::where('transaction_id', $orderId)->first();
                    if ($donation) {
                        $donation->update([
                            'payment_status' => 'cancelled',
                            'failed_at' => now()
                        ]);
                    }
                    break;
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('PayPal webhook error', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getPaymentMethods()
    {
        return [
            'paypal_balance' => [
                'name' => 'PayPal',
                'description' => 'Payer avec votre solde PayPal',
                'icon' => 'fab fa-paypal',
                'color' => '#003087'
            ],
            'paypal_card' => [
                'name' => 'Carte bancaire',
                'description' => 'Visa, Mastercard, American Express',
                'icon' => 'fas fa-credit-card',
                'color' => '#CC0000'
            ]
        ];
    }

    /**
     * Valider un montant
     */
    public function validateAmount($amount)
    {
        $minAmount = config('services.paypal.min_amount', 1); // 1 USD min
        $maxAmount = config('services.paypal.max_amount', 10000); // 10000 USD max
        
        // Conversion approximative FCFA -> USD pour validation
        $amountUsd = $amount / 600;
        
        if (!is_numeric($amountUsd) || $amountUsd < $minAmount || $amountUsd > $maxAmount) {
            return [
                'valid' => false,
                'error' => 'Montant PayPal doit être entre ' . $minAmount . ' USD et ' . $maxAmount . ' USD'
            ];
        }

        return ['valid' => true];
    }

    /**
     * Montants suggérés en USD
     */
    public function getSuggestedAmounts()
    {
        // En FCFA (seront convertis)
        return [
            3000,   // ~5 USD
            6000,   // ~10 USD
            15000,  // ~25 USD
            30000,  // ~50 USD
            60000,  // ~100 USD
            150000  // ~250 USD
        ];
    }
}
