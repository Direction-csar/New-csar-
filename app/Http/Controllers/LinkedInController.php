<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInController extends Controller
{
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $redirectUri;
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
