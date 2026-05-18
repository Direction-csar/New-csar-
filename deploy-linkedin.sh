#!/bin/bash
# Script de déploiement LinkedIn CSAR
# Exécuter sur la VM Ubuntu: bash deploy-linkedin.sh

set -e
APP=/var/www/csar

echo "=== Déploiement LinkedIn CSAR ==="

# 1. LinkedInController
cat > "$APP/app/Http/Controllers/LinkedInController.php" << 'PHPEOF'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInController extends Controller
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private ?string $orgId;

    public function __construct()
    {
        $this->clientId     = config('services.linkedin.client_id', '');
        $this->clientSecret = config('services.linkedin.client_secret', '');
        $this->redirectUri  = config('services.linkedin.redirect', '');
        $this->orgId        = config('services.linkedin.org_id');
    }

    public function redirect()
    {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'scope'         => 'openid profile email w_member_social',
            'state'         => csrf_token(),
        ]);

        return redirect('https://www.linkedin.com/oauth/v2/authorization?' . $params);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            Log::error('LinkedIn OAuth error: ' . $request->get('error_description'));
            return redirect('/')->with('error', 'Connexion LinkedIn échouée.');
        }

        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type'    => 'authorization_code',
            'code'          => $request->get('code'),
            'redirect_uri'  => $this->redirectUri,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            Log::error('LinkedIn token exchange failed: ' . $response->body());
            return redirect('/')->with('error', 'Échange du token LinkedIn échoué.');
        }

        $data        = $response->json();
        $accessToken = $data['access_token'];
        $expiresIn   = $data['expires_in'] ?? 5184000;

        Cache::put('linkedin_access_token', $accessToken, now()->addSeconds($expiresIn - 60));

        $this->fetchAndCachePosts($accessToken);

        return redirect('/')->with('success', 'LinkedIn connecté avec succès !');
    }

    public function posts(): \Illuminate\Http\JsonResponse
    {
        $posts = $this->getCachedPosts();
        return response()->json($posts);
    }

    public function fetchAndCachePosts(string $accessToken): array
    {
        try {
            $meResponse = Http::withToken($accessToken)
                ->get('https://api.linkedin.com/v2/userinfo');

            if (!$meResponse->successful()) {
                Log::error('LinkedIn userinfo failed: ' . $meResponse->body());
                return [];
            }

            $me  = $meResponse->json();
            $sub = $me['sub'] ?? null;

            if (!$sub) {
                return [];
            }

            $authorUrn = $this->orgId
                ? 'urn:li:organization:' . $this->orgId
                : 'urn:li:person:' . $sub;

            $feed = Http::withToken($accessToken)
                ->withHeaders(['LinkedIn-Version' => '202401', 'X-Restli-Protocol-Version' => '2.0.0'])
                ->get('https://api.linkedin.com/rest/posts', [
                    'author' => $authorUrn,
                    'q'      => 'author',
                    'count'  => 10,
                    'sortBy' => 'LAST_MODIFIED',
                ]);

            if (!$feed->successful()) {
                Log::error('LinkedIn posts fetch failed: ' . $feed->body());
                return [];
            }

            $raw   = $feed->json();
            $items = $raw['elements'] ?? [];
            $posts = array_map(fn($item) => $this->normalizePost($item), $items);
            $posts = array_filter($posts);
            $posts = array_values($posts);

            $cacheMins = config('services.linkedin.cache_minutes', 60);
            Cache::put('linkedin_posts', $posts, now()->addMinutes($cacheMins));

            return $posts;
        } catch (\Throwable $e) {
            Log::error('LinkedIn fetchPosts exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getCachedPosts(): array
    {
        return Cache::get('linkedin_posts', []);
    }

    public function refresh(): \Illuminate\Http\JsonResponse
    {
        $token = Cache::get('linkedin_access_token');
        if (!$token) {
            return response()->json(['error' => 'No token. Please re-authenticate.'], 401);
        }
        $posts = $this->fetchAndCachePosts($token);
        return response()->json($posts);
    }

    private function normalizePost(array $item): ?array
    {
        $content    = $item['commentary'] ?? ($item['content']['article']['title'] ?? '');
        $created    = isset($item['publishedAt'])
            ? \Carbon\Carbon::createFromTimestampMs($item['publishedAt'])->locale('fr_FR')->isoFormat('D MMMM YYYY')
            : '';
        $url        = $item['content']['article']['source'] ?? null;
        $imgUrl     = $item['content']['article']['thumbnail'] ?? null;

        if (empty($content)) {
            return null;
        }

        return [
            'id'      => $item['id'] ?? uniqid(),
            'date'    => $created,
            'title'   => \Str::limit($content, 60),
            'desc'    => \Str::limit($content, 140),
            'img'     => $imgUrl,
            'url'     => $url ?? config('services.linkedin.company_url'),
        ];
    }
}
PHPEOF

echo "✅ LinkedInController créé"

# 2. Patch config/services.php
python3 - << 'PYEOF'
import re, sys

path = '/var/www/csar/config/services.php'
with open(path, 'r') as f:
    content = f.read()

old = """    'linkedin' => [
        'company_url' => env('LINKEDIN_COMPANY_URL', 'https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/posts'),
        'feed_embed_code' => env('LINKEDIN_FEED_EMBED', '<script src="https://elfsightcdn.com/platform.js" async></script><div class="elfsight-app-b8e60e2e-9795-4930-974e-fc3bb6e9c79b" data-elfsight-app-lazy></div>'),
    ],"""

new = """    'linkedin' => [
        'company_url'      => env('LINKEDIN_COMPANY_URL', 'https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/posts'),
        'client_id'        => env('LINKEDIN_CLIENT_ID'),
        'client_secret'    => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'         => env('LINKEDIN_REDIRECT_URI', env('APP_URL') . '/linkedin/callback'),
        'org_id'           => env('LINKEDIN_ORG_ID'),
        'cache_minutes'    => env('LINKEDIN_CACHE_MINUTES', 60),
    ],"""

if old in content:
    content = content.replace(old, new)
    with open(path, 'w') as f:
        f.write(content)
    print('✅ services.php patché')
else:
    print('⚠️  services.php déjà patché ou différent')
PYEOF

# 3. Patch routes/web.php — ajouter import + routes LinkedIn
python3 - << 'PYEOF'
path = '/var/www/csar/routes/web.php'
with open(path, 'r') as f:
    content = f.read()

# Import
old_use = "use Illuminate\Support\Facades\Route;"
new_use = "use Illuminate\Support\Facades\Route;\nuse App\Http\Controllers\LinkedInController;"
if "LinkedInController" not in content:
    content = content.replace(old_use, new_use, 1)

# Routes LinkedIn
linkedin_routes = """
// LinkedIn OAuth routes
Route::prefix('linkedin')->name('linkedin.')->group(function () {
    Route::get('/auth',     [LinkedInController::class, 'redirect'])->name('auth');
    Route::get('/callback', [LinkedInController::class, 'callback'])->name('callback');
    Route::get('/posts',    [LinkedInController::class, 'posts'])->name('posts');
    Route::post('/refresh', [LinkedInController::class, 'refresh'])->name('refresh');
});
"""

if "linkedin.auth" not in content:
    content = content.rstrip() + "\n" + linkedin_routes

with open(path, 'w') as f:
    f.write(content)
print("✅ routes/web.php patché")
PYEOF

# 4. Ajouter variables .env
ENV_FILE="$APP/.env"
add_env() {
    local key=$1
    local val=$2
    if grep -q "^$key=" "$ENV_FILE"; then
        echo "  ↩ $key déjà présent"
    else
        echo "$key=$val" >> "$ENV_FILE"
        echo "  ✅ $key ajouté"
    fi
}

echo ""
echo "=== Mise à jour .env ==="
add_env "LINKEDIN_CLIENT_ID" "789nxigt5lm3ob"
add_env "LINKEDIN_CLIENT_SECRET" "REMPLACE_PAR_TON_NOUVEAU_SECRET"
add_env "LINKEDIN_REDIRECT_URI" "https://csar.sn/linkedin/callback"
add_env "LINKEDIN_ORG_ID" ""
add_env "LINKEDIN_CACHE_MINUTES" "60"

# 5. Vider cache Laravel
cd "$APP"
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo ""
echo "========================================="
echo "✅ Déploiement terminé !"
echo ""
echo "👉 Prochaines étapes :"
echo "   1. Édite .env et remplace LINKEDIN_CLIENT_SECRET"
echo "   2. Ajoute https://csar.sn/linkedin/callback sur LinkedIn Developers"
echo "   3. Visite https://csar.sn/linkedin/auth pour connecter"
echo "========================================="
