<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\PayDunyaService;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationConfirmation;

class DonationController extends Controller
{
    protected $paydunyaService;
    protected $paypalService;

    public function __construct(PayDunyaService $paydunyaService, PayPalService $paypalService)
    {
        $this->paydunyaService = $paydunyaService;
        $this->paypalService = $paypalService;
    }

    /**
     * Display the donation page
     */
    public function index()
    {
        $paymentMethods = $this->paydunyaService->getPaymentMethods();
        $suggestedAmounts = $this->paydunyaService->getSuggestedAmounts();
        
        // PayPal methods
        $paypalMethods = $this->paypalService->getPaymentMethods();
        $paypalAmounts = $this->paypalService->getSuggestedAmounts();
        
        return view('public.donations.index', compact('paymentMethods', 'suggestedAmounts', 'paypalMethods', 'paypalAmounts'));
    }

    /**
     * Process donation request
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:500|max:1000000',
            'payment_method' => 'required|in:wave,orange_money,credit_card,paypal_balance,paypal_card',
            'payment_provider' => 'required|in:paydunya,paypal',
            'donation_type' => 'required|in:single,monthly',
            'frequency' => 'nullable|required_if:donation_type,monthly|in:monthly,quarterly,yearly',
            'message' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ], [
            'amount.min' => __('donations.validation.amount_min', ['min' => 500]),
            'amount.max' => __('donations.validation.amount_max', ['max' => 1000000]),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate amount based on provider
        $provider = $request->payment_provider;
        if ($provider === 'paypal') {
            $amountValidation = $this->paypalService->validateAmount($request->amount);
            $currency = 'USD';
        } else {
            $amountValidation = $this->paydunyaService->validateAmount($request->amount);
            $currency = 'XOF';
        }
        
        if (!$amountValidation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => ['amount' => $amountValidation['error']]
            ], 422);
        }

        try {
            // Create donation record
            $donation = Donation::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_provider' => $provider,
                'donation_type' => $request->donation_type,
                'frequency' => $request->frequency,
                'message' => $request->message,
                'is_anonymous' => $request->boolean('is_anonymous', false),
                'payment_status' => 'pending',
                'currency' => $currency,
            ]);

            // Create payment based on provider
            if ($provider === 'paypal') {
                $paymentResult = $this->paypalService->createOrder($donation);
                
                if (!$paymentResult['success']) {
                    $donation->update([
                        'payment_status' => 'failed',
                        'failed_at' => now(),
                        'failure_reason' => $paymentResult['error']
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => __('donations.errors.payment_creation_failed'),
                        'details' => $paymentResult['details'] ?? null
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'donation_id' => $donation->id,
                    'payment_provider' => 'paypal',
                    'paypal_order_id' => $paymentResult['order_id'],
                    'approve_url' => $paymentResult['approve_url'],
                    'message' => __('donations.success.payment_initiated')
                ]);
            } else {
                // PayDunya
                $paymentResult = $this->paydunyaService->createDonationPayment($donation);

                if (!$paymentResult['success']) {
                    $donation->update([
                        'payment_status' => 'failed',
                        'failed_at' => now(),
                        'failure_reason' => $paymentResult['error']
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => __('donations.errors.payment_creation_failed'),
                        'details' => $paymentResult['details'] ?? null
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'donation_id' => $donation->id,
                    'payment_provider' => 'paydunya',
                    'payment_url' => $paymentResult['payment_url'],
                    'message' => __('donations.success.payment_initiated')
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Donation processing error', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('donations.errors.processing_error')
            ], 500);
        }
    }

    /**
     * Handle payment success
     */
    public function success(Donation $donation)
    {
        // Verify payment status
        if ($donation->transaction_id) {
            $verification = $this->paydunyaService->verifyPayment($donation->transaction_id);
            
            if ($verification['success']) {
                $status = $verification['data']['status'] ?? 'unknown';
                $paymentStatus = match($status) {
                    'completed' => 'success',
                    'pending' => 'pending',
                    default => 'failed'
                };

                $wasNotSuccess = $donation->payment_status !== 'success';
                $donation->update([
                    'payment_status' => $paymentStatus,
                    'processed_at' => $paymentStatus === 'success' ? now() : null,
                    'failed_at' => $paymentStatus === 'failed' ? now() : null,
                ]);

                if ($paymentStatus === 'success' && $wasNotSuccess) {
                    try {
                        Mail::to($donation->email)->send(new DonationConfirmation($donation->fresh()));
                    } catch (\Exception $e) {
                        Log::warning('Donation confirmation email failed', ['id' => $donation->id, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        return view('public.donations.success', compact('donation'));
    }

    /**
     * Handle payment cancellation
     */
    public function cancel()
    {
        return view('public.donations.cancel');
    }

    /**
     * Handle PayDunya callback
     */
    public function callback(Request $request)
    {
        try {
            Log::info('PayDunya callback received', ['data' => $request->all()]);

            $result = $this->paydunyaService->processCallback($request->all());

            if (!$result['success']) {
                Log::error('PayDunya callback processing failed', [
                    'data' => $request->all(),
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            } elseif (!empty($result['donation'])) {
                $cbDonation = $result['donation'];
                if ($cbDonation->payment_status === 'success') {
                    try {
                        Mail::to($cbDonation->email)->send(new DonationConfirmation($cbDonation));
                    } catch (\Exception $e) {
                        Log::warning('Donation confirmation email failed (callback)', ['id' => $cbDonation->id, 'error' => $e->getMessage()]);
                    }
                }
            }

            // Return response to PayDunya
            return response()->json(['status' => 'received']);

        } catch (\Exception $e) {
            Log::error('PayDunya callback error', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Display donation tracking page
     */
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->route('donations.index')
                ->withErrors($validator)
                ->withInput();
        }

        $donations = Donation::where('email', $request->email)
            ->when($request->transaction_id, function ($query, $transactionId) {
                return $query->where('transaction_id', $transactionId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('public.donations.track', compact('donations'));
    }

    /**
     * API: Get donation statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_amount' => Donation::successful()->sum('amount'),
                'total_donations' => Donation::successful()->count(),
                'monthly_amount' => Donation::successful()
                    ->where('donation_type', 'monthly')
                    ->sum('amount'),
                'monthly_donors' => Donation::successful()
                    ->where('donation_type', 'monthly')
                    ->count(),
                'recent_donations' => Donation::successful()
                    ->with(['donor'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Donation statistics error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch statistics'
            ], 500);
        }
    }

    /**
     * API: Get donation history
     */
    public function history(Request $request)
    {
        try {
            $donations = Donation::where('email', $request->email)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $donations
            ]);

        } catch (\Exception $e) {
            Log::error('Donation history error', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch donation history'
            ], 500);
        }
    }

    /**
     * Handle PayPal payment success
     */
    public function paypalSuccess(Request $request, Donation $donation)
    {
        try {
            $token = $request->get('token'); // PayPal order ID
            
            if (!$token) {
                return redirect()->route('donations.cancel')
                    ->with('error', 'Invalid PayPal response');
            }

            // Capture the payment
            $capture = $this->paypalService->captureOrder($token);

            if ($capture['success'] && $capture['status'] === 'COMPLETED') {
                $donation->update([
                    'payment_status' => 'success',
                    'processed_at' => now(),
                    'metadata' => array_merge($donation->metadata ?? [], [
                        'paypal_capture' => $capture['data'] ?? []
                    ])
                ]);

                return view('public.donations.success', compact('donation'))
                    ->with('success', 'Votre don a été traité avec succès !');
            }

            // Payment not completed
            $donation->update([
                'payment_status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $capture['error'] ?? 'PayPal capture failed'
            ]);

            return view('public.donations.cancel')
                ->with('error', 'Le paiement n\'a pas pu être complété.');

        } catch (\Exception $e) {
            Log::error('PayPal success error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('donations.cancel')
                ->with('error', 'Une erreur est survenue lors du traitement du paiement.');
        }
    }

    /**
     * Handle PayPal payment cancellation
     */
    public function paypalCancel()
    {
        return view('public.donations.cancel')
            ->with('info', 'Vous avez annulé le paiement PayPal.');
    }

    /**
     * Handle PayPal webhooks
     */
    public function paypalWebhook(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('PayPal webhook received', ['event' => $payload['event_type'] ?? 'unknown']);

            $result = $this->paypalService->processWebhook($payload);

            return response()->json(['status' => 'received'], 200);

        } catch (\Exception $e) {
            Log::error('PayPal webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }
}
