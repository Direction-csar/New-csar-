<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Donation;

class BictorysService
{
    private string $apiKey;
    private string $baseUrl;
    private string $mode;

    public function __construct()
    {
        $this->mode = config('services.bictorys.mode', 'test');
        $this->apiKey = config('services.bictorys.api_key', '');
        $this->baseUrl = $this->mode === 'live'
            ? config('services.bictorys.base_url_live', 'https://api.bictorys.com')
            : config('services.bictorys.base_url_test', 'https://api.test.bictorys.com');
    }

    /**
     * Build Bictorys API headers
     */
    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create a payment charge for a donation
     */
    public function createDonationPayment(Donation $donation, string $paymentType = 'orange_money'): array
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'Bictorys API key is not configured.',
                ];
            }

            $callbackUrl = route('donations.bictorys.callback', ['donation' => $donation->id]);

            $payload = [
                'amount' => (int) round($donation->amount),
                'currency' => $donation->currency ?? 'XOF',
                'country' => config('services.bictorys.country', 'SN'),
                'paymentReference' => 'CSAR-DON-' . $donation->id,
                'successRedirectUrl' => $callbackUrl,
                'ErrorRedirectUrl' => $callbackUrl,
                'customerObject' => [
                    'name' => $donation->full_name,
                    'phone' => $this->formatPhone($donation->phone),
                    'email' => $donation->email,
                    'country' => config('services.bictorys.country', 'SN'),
                ],
            ];

            $url = $this->baseUrl . '/pay/v1/charges?payment_type=' . urlencode($paymentType);

            Log::info('Bictorys payment request', [
                'donation_id' => $donation->id,
                'payment_type' => $paymentType,
                'url' => $url,
            ]);

            $response = Http::withHeaders($this->getHeaders())
                ->post($url, $payload);

            $body = $response->json() ?? [];

            Log::info('Bictorys payment response', [
                'donation_id' => $donation->id,
                'status' => $response->status(),
                'body' => $body,
            ]);

            if ($response->successful() && in_array($response->status(), [201, 202])) {
                $transactionId = $body['transactionId']
                    ?? $body['transaction_id']
                    ?? $body['id']
                    ?? null;
                $paymentUrl = $body['confirmationLink']
                    ?? $body['confirmation_link']
                    ?? $body['checkoutLink']
                    ?? $body['checkout_link']
                    ?? $body['paymentUrl']
                    ?? $body['payment_url']
                    ?? $body['redirectUrl']
                    ?? $body['redirect_url']
                    ?? null;

                $donation->update([
                    'transaction_id' => $transactionId,
                    'metadata' => array_merge($donation->metadata ?? [], [
                        'bictorys_response' => $body,
                        'bictorys_payment_type' => $paymentType,
                    ])
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'payment_url' => $paymentUrl,
                    'data' => $body,
                ];
            }

            $error = $body['message']
                ?? $body['errorReason']
                ?? $body['error']
                ?? 'Bictorys payment creation failed';

            return [
                'success' => false,
                'error' => $error,
                'details' => $body,
                'status' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('Bictorys service error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Service temporarily unavailable',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment status by transaction ID
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'Bictorys API key is not configured.',
                ];
            }

            $url = $this->baseUrl . '/pay/v1/charges/' . urlencode($transactionId);

            $response = Http::withHeaders($this->getHeaders())->get($url);
            $body = $response->json() ?? [];

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $body['status'] ?? $body['transactionStatus'] ?? 'unknown',
                    'data' => $body,
                ];
            }

            return [
                'success' => false,
                'error' => 'Bictorys verification failed',
                'details' => $body,
            ];

        } catch (\Exception $e) {
            Log::error('Bictorys verification error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Verification service unavailable',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process Bictorys callback / redirect
     */
    public function processCallback(Donation $donation, array $data): array
    {
        try {
            $transactionId = $data['transactionId']
                ?? $data['transaction_id']
                ?? $data['paymentReference']
                ?? $donation->transaction_id;

            if ($transactionId) {
                $verification = $this->verifyPayment($transactionId);
                $status = $verification['success']
                    ? ($verification['status'] ?? 'unknown')
                    : ($data['status'] ?? 'unknown');
            } else {
                $status = $data['status'] ?? 'unknown';
            }

            $paymentStatus = match (strtolower($status)) {
                'success', 'completed', 'successful', 'succeeded' => 'success',
                'pending', 'processing', 'initiated' => 'pending',
                'cancelled', 'failed', 'canceled', 'error' => 'failed',
                default => 'pending',
            };

            $donation->update([
                'payment_status' => $paymentStatus,
                'processed_at' => $paymentStatus === 'success' ? now() : null,
                'failed_at' => $paymentStatus === 'failed' ? now() : null,
                'failure_reason' => $paymentStatus === 'failed' ? ($data['reason'] ?? 'Payment failed') : null,
                'metadata' => array_merge($donation->metadata ?? [], [
                    'bictorys_callback' => $data,
                    'bictorys_verification' => $verification ?? [],
                ])
            ]);

            return [
                'success' => true,
                'donation' => $donation,
                'status' => $paymentStatus,
            ];

        } catch (\Exception $e) {
            Log::error('Bictorys callback processing error', [
                'donation_id' => $donation->id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Callback processing failed',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number for Bictorys
     */
    private function formatPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '+221';
        }

        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (!str_starts_with($phone, '+')) {
            $phone = '+221' . ltrim($phone, '0');
        }

        return $phone;
    }

    /**
     * Get available Bictorys payment methods
     */
    public function getPaymentMethods(): array
    {
        return [
            'bictorys_orange_money' => [
                'name' => 'Orange Money',
                'description' => 'Paiement via Orange Money (Bictorys)',
                'icon' => 'fas fa-mobile-alt',
                'color' => '#FF6600',
                'api_type' => 'orange_money',
            ],
            'bictorys_wave' => [
                'name' => 'Wave',
                'description' => 'Paiement via Wave (Bictorys)',
                'icon' => 'fas fa-mobile-alt',
                'color' => '#00D4AA',
                'api_type' => 'wave_money',
            ],
            'bictorys_credit_card' => [
                'name' => 'Carte bancaire',
                'description' => 'Visa / Mastercard (Bictorys)',
                'icon' => 'fas fa-credit-card',
                'color' => '#4A90E2',
                'api_type' => 'card',
            ],
        ];
    }

    /**
     * Get suggested donation amounts
     */
    public function getSuggestedAmounts(): array
    {
        return [1000, 2500, 5000, 10000, 25000, 50000];
    }

    /**
     * Validate donation amount
     */
    public function validateAmount($amount): array
    {
        $minAmount = config('services.bictorys.min_amount', 500);
        $maxAmount = config('services.bictorys.max_amount', 10000000);

        if (!is_numeric($amount) || $amount < $minAmount || $amount > $maxAmount) {
            return [
                'valid' => false,
                'error' => __('donations.invalid_amount', [
                    'min' => number_format($minAmount, 0, ',', ' '),
                    'max' => number_format($maxAmount, 0, ',', ' '),
                ])
            ];
        }

        return ['valid' => true];
    }
}
