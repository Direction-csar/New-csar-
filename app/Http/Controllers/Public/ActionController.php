<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicRequest;
use App\Services\SmsService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActionController extends Controller
{
    public function index()
    {
        return view('public.action');
    }
    
    public function submit(Request $request)
    {
        $rules = [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string',
            'type' => 'required|in:aide,partenariat,audience,autre',
            'region' => 'required|string|max:255',
            'description' => 'required|string',
        ];
        
        // Géolocalisation optionnelle (recommandée mais pas obligatoire)
        $rules['latitude'] = 'nullable|numeric';
        $rules['longitude'] = 'nullable|numeric';
        
        $request->validate($rules);
        
        // Generate unique tracking code
        $trackingCode = 'CSAR' . str_pad(PublicRequest::count() + 1, 6, '0', STR_PAD_LEFT);
        
        // Create the request
        $publicRequest = PublicRequest::create([
            'name' => $request->full_name, // Champ obligatoire
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'type' => $request->type,
            'region' => $request->region,
            'description' => $request->description,
            'latitude' => $request->latitude ?: null,
            'longitude' => $request->longitude ?: null,
            'tracking_code' => $trackingCode,
            'status' => 'pending',
            'request_date' => now()->toDateString(),
            'sms_sent' => false,
            'urgency' => 'medium',
            'preferred_contact' => 'email'
        ]);
        
        // Send SMS notification using SmsService
        $smsSent = false;
        try {
            $smsService = app(SmsService::class);
            $smsService->sendRequestConfirmation($publicRequest->phone, $publicRequest->tracking_code, $publicRequest->type);
            $smsSent = true;
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('SMS sending failed: ' . $e->getMessage());
        }
        
        $publicRequest->update(['sms_sent' => $smsSent]);
        
        // Créer une notification automatique pour l'admin
        NotificationService::notifyNewRequest($publicRequest);
        
        return redirect()->route('request.success', ['code' => $publicRequest->tracking_code])
            ->with('success', '✅ Votre demande a été envoyée avec succès ! Un SMS de confirmation vous a été envoyé.');
    }
}
