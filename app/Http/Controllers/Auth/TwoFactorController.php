<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Gestion 2FA TOTP — Plateforme CSAR.
 *
 * Routes :
 *   GET  /admin/2fa/setup     -> formulaire activation (QR code)
 *   POST /admin/2fa/enable    -> valide le code et active
 *   GET  /admin/2fa/challenge -> formulaire challenge après login
 *   POST /admin/2fa/verify    -> valide le code de challenge
 *   POST /admin/2fa/disable   -> désactive (avec mot de passe + code)
 */
class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorAuthService $service) {}

    /* ------------------------------------------------------------------ */
    /* Setup (utilisateur déjà authentifié)                                */
    /* ------------------------------------------------------------------ */

    public function showSetup(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->two_factor_enabled) {
            return redirect()->route('admin.dashboard')
                ->with('info', 'L\'authentification à deux facteurs est déjà activée.');
        }

        // Génère un secret temporaire en session si pas déjà fait.
        $secret = $request->session()->get('2fa_setup_secret');
        if (!$secret) {
            $secret = $this->service->generateSecretKey();
            $request->session()->put('2fa_setup_secret', $secret);
        }

        $otpauthUrl = $this->service->getQRCodeUrl($user, $secret);
        $qrSvg = $this->renderQrSvg($otpauthUrl);

        return view('auth.two-factor.setup', [
            'secret'  => $secret,
            'qrSvg'   => $qrSvg,
            'qrUrl'   => $otpauthUrl,
        ]);
    }

    public function enable(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $secret = $request->session()->get('2fa_setup_secret');
        if (!$secret) {
            throw ValidationException::withMessages([
                'code' => 'Session expirée, veuillez recommencer la procédure.',
            ]);
        }

        $result = $this->service->enableTwoFactor($user->id, $secret, $request->input('code'));

        if (!$result['success']) {
            throw ValidationException::withMessages(['code' => $result['message']]);
        }

        // Génère les codes de récupération à montrer une seule fois.
        $codes = $this->service->generateRecoveryCodes($user->id);
        $request->session()->forget('2fa_setup_secret');
        $request->session()->flash('recovery_codes', $codes);

        return redirect()->route('admin.2fa.recovery')
            ->with('success', 'Authentification à deux facteurs activée.');
    }

    public function showRecoveryCodes(Request $request)
    {
        $codes = $request->session()->get('recovery_codes', []);
        if (empty($codes)) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.two-factor.recovery', ['codes' => $codes]);
    }

    /* ------------------------------------------------------------------ */
    /* Challenge (post-login)                                              */
    /* ------------------------------------------------------------------ */

    public function showChallenge(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('admin.login');
        }
        return view('auth.two-factor.challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        $guard  = $request->session()->get('2fa_guard', 'admin');

        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail($userId);
        $code = trim($request->input('code'));

        $isValid = false;

        // 1. Code TOTP standard (6 chiffres)
        if (preg_match('/^\d{6}$/', $code) && $user->two_factor_secret) {
            $isValid = $this->service->verifyCode(decrypt($user->two_factor_secret), $code);
        }

        // 2. Code de récupération (8 caractères alphanumériques)
        if (!$isValid && preg_match('/^[A-Z0-9]{8}$/i', $code)) {
            $isValid = $this->service->verifyRecoveryCode($user->id, $code);
        }

        if (!$isValid) {
            Log::channel('security')->warning('Échec challenge 2FA', [
                'user_id' => $user->id,
                'ip'      => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'code' => 'Code invalide. Réessayez avec un code de votre application ou un code de récupération.',
            ]);
        }

        // Authentification définitive
        Auth::guard($guard)->login($user, $request->session()->get('2fa_remember', false));
        $request->session()->forget(['2fa_user_id', '2fa_guard', '2fa_remember']);
        $request->session()->put('2fa_passed_at', now()->timestamp);
        $request->session()->regenerate();

        Log::channel('security')->info('Challenge 2FA réussi', [
            'user_id' => $user->id,
            'ip'      => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Authentification réussie.');
    }

    /* ------------------------------------------------------------------ */
    /* Désactivation                                                       */
    /* ------------------------------------------------------------------ */

    public function disable(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $request->validate([
            'password' => 'required|string',
            'code'     => 'required|string|size:6',
        ]);

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages(['password' => 'Mot de passe incorrect.']);
        }

        $result = $this->service->disableTwoFactor($user->id, $request->input('code'));

        if (!$result['success']) {
            throw ValidationException::withMessages(['code' => $result['message']]);
        }

        return redirect()->back()->with('success', '2FA désactivée.');
    }

    /* ------------------------------------------------------------------ */
    /* QR code SVG (sans dépendance imagick)                               */
    /* ------------------------------------------------------------------ */

    protected function renderQrSvg(string $url): string
    {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        return $writer->writeString($url);
    }
}
