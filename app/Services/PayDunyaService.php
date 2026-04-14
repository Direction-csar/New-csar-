<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;

class PayDunyaService
{
    private $apiKey;
    private $privateKey;
    private $masterKey;
    private $token;
    private $baseUrl;
    private $mode;

    public function __construct()
    {
        $this->mode = config('services.paydunya.mode', 'test');
        $this->apiKey = config('services.paydunya.api_key');
        $this->privateKey = config('services.paydunya.private_key');
        $this->masterKey = config('services.paydunya.master_key');
        $this->token = config('services.paydunya.token');
        $this->baseUrl = $this->mode === 'live' 
            ? 'https://app.paydunya.com/api/v1' 
            : 'https://app.paydunya.com/sandbox-api/v1';
    }

    /**
     * Create a payment invoice for donation
     */
    public function createDonationPayment(Donation $donation)
    {
        try {
            $invoiceData = [
                'invoice' => [
                    'total_amount' => $donation->amount,
                    'description' => __('donations.payment_description', [
                        'amount' => $donation->amount,
                        'currency' => $donation->currency
                    ]),
                    'callback_url' => route('donations.callback'),
                    'return_url' => route('donations.success', ['donation' => $donation->id]),
                    'cancel_url' => route('donations.cancel'),
                    'custom_data' => [
                        'donation_id' => $donation->id,
                        'donor_name' => $donation->full_name,
                        'donor_email' => $donation->email,
                        'is_anonymous' => $donation->is_anonymous,
                    ]
                ],
                'store' => [
                    'name' => config('app.name'),
                    'website_url' => config('app.url'),
                    'logo_url' => asset('images/logos/LOGO CSAR vectoriel-01.png'),
                ],
                'customer' => [
                    'name' => $donation->full_name,
                    'email' => $donation->email,
                    'phone' => $donation->phone,
                ]
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'PAYDUNYA-MASTER-KEY' => $this->masterKey,
                'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
                'PAYDUNYA-TOKEN' => $this->token,
            ])->post($this->baseUrl . '/checkout-invoice/create', $invoiceData);

            if ($response->successful()) {
                $data = $response->json();
                
                // Update donation with transaction info
                $donation->update([
                    'transaction_id' => $data['invoice_token'] ?? null,
                    'metadata' => array_merge($donation->metadata ?? [], [
                        'paydunya_response' => $data
                    ])
                ]);

                return [
                    'success' => true,
                    'token' => $data['invoice_token'] ?? null,
                    'payment_url' => $data['response_text'] ?? null,
                    'data' => $data
                ];
            }

            Log::error('PayDunya payment creation failed', [
                'donation_id' => $donation->id,
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            return [
                'success' => false,
                'error' => 'Payment creation failed',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('PayDunya service error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment($invoiceToken)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'PAYDUNYA-MASTER-KEY' => $this->privateKey,
                'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
                'PAYDUNYA-TOKEN' => $this->token,
            ])->get($this->baseUrl . '/checkout-invoice/confirm/' . $invoiceToken);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment verification failed',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('PayDunya verification error', [
                'invoice_token' => $invoiceToken,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Verification service unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Process payment callback
     */
    public function processCallback($data)
    {
        try {
            $invoiceToken = $data['invoice_token'] ?? null;
            
            if (!$invoiceToken) {
                return [
                    'success' => false,
                    'error' => 'Invalid invoice token'
                ];
            }

            $verification = $this->verifyPayment($invoiceToken);
            
            if (!$verification['success']) {
                return $verification;
            }

            $status = $verification['data']['status'] ?? 'unknown';
            
            // Find donation by transaction ID
            $donation = Donation::where('transaction_id', $invoiceToken)->first();
            
            if (!$donation) {
                return [
                    'success' => false,
                    'error' => 'Donation not found'
                ];
            }

            // Update donation status
            $paymentStatus = match($status) {
                'completed' => 'success',
                'pending' => 'pending',
                'cancelled', 'failed' => 'failed',
                default => 'pending'
            };

            $donation->update([
                'payment_status' => $paymentStatus,
                'processed_at' => $paymentStatus === 'success' ? now() : null,
                'failed_at' => $paymentStatus === 'failed' ? now() : null,
                'failure_reason' => $paymentStatus === 'failed' ? ($data['reason'] ?? 'Payment failed') : null,
                'metadata' => array_merge($donation->metadata ?? [], [
                    'callback_data' => $data,
                    'verification_data' => $verification['data']
                ])
            ]);

            return [
                'success' => true,
                'donation' => $donation,
                'status' => $paymentStatus
            ];

        } catch (\Exception $e) {
            Log::error('PayDunya callback processing error', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Callback processing failed',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payment methods available
     */
    public function getPaymentMethods()
    {
        return [
            'wave' => [
                'name' => 'Wave',
                'description' => 'Paiement mobile avec Wave',
                'icon' => 'fas fa-mobile-alt',
                'color' => '#00D4AA'
            ],
            'orange_money' => [
                'name' => 'Orange Money',
                'description' => 'Paiement mobile avec Orange Money',
                'icon' => 'fas fa-mobile-alt',
                'color' => '#FF6600'
            ],
            'credit_card' => [
                'name' => 'Carte bancaire',
                'description' => 'Visa, Mastercard',
                'icon' => 'fas fa-credit-card',
                'color' => '#4A90E2'
            ]
        ];
    }

    /**
     * Get suggested donation amounts
     */
    public function getSuggestedAmounts()
    {
        return [
            1000, 2500, 5000, 10000, 25000, 50000
        ];
    }

    /**
     * Validate donation amount
     */
    public function validateAmount($amount)
    {
        $minAmount = config('donations.min_amount', 500);
        $maxAmount = config('donations.max_amount', 1000000);
        
        if (!is_numeric($amount) || $amount < $minAmount || $amount > $maxAmount) {
            return [
                'valid' => false,
                'error' => __('donations.invalid_amount', [
                    'min' => number_format($minAmount, 0, ',', ' '),
                    'max' => number_format($maxAmount, 0, ',', ' ')
                ])
            ];
        }

        return ['valid' => true];
    }
}
