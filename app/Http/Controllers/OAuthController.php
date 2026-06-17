<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Services\TokenManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function redirect(Request $request, string $platform, ?string $client = null)
    {
        $provider = config("oauth.{$platform}");
        if (!$provider || !$provider['authorize_url']) {
            return back()->with('error', "OAuth not supported for {$platform}. Use manual token entry.");
        }

        $clientId = config("services.{$platform}.client_id");
        $redirectUri = route('oauth.callback', ['platform' => $platform]);

        if (!$clientId) {
            return back()->with('error', "Client ID not configured for {$platform}. Set it in config/services.php.");
        }

        if ($client) {
            session(['oauth_client' => $client]);
        }

        $state = Str::random(40);
        session()->put("oauth_state_{$platform}", $state);

        $scopes = implode(' ', $provider['scopes']);
        $url = str_replace('{instance}', $request->input('instance', 'mastodon.social'), $provider['authorize_url']);

        $params = array_merge($url === $provider['authorize_url'] ? [] : [], [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scopes,
            'state' => $state,
        ]);

        // Platform-specific adjustments
        if ($platform === 'youtube') {
            $params['access_type'] = 'offline';
            $params['prompt'] = 'consent';
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return redirect($url . $separator . http_build_query($params));
    }

    public function callback(Request $request, string $platform)
    {
        $provider = config("oauth.{$platform}");
        $clientId = config("services.{$platform}.client_id");
        $clientSecret = config("services.{$platform}.client_secret");

        if ($request->filled('error')) {
            return redirect()->route('accounts.index', session('oauth_client'))
                ->with('error', 'OAuth authorization denied: ' . $request->input('error_description', ''));
        }

        if (!$request->filled('code')) {
            return redirect()->route('accounts.index', session('oauth_client'))
                ->with('error', 'No authorization code returned.');
        }

        $expectedState = session()->pull("oauth_state_{$platform}");
        if ($expectedState && $request->state !== $expectedState) {
            return redirect()->route('accounts.index', session('oauth_client'))
                ->with('error', 'Invalid OAuth state. Possible CSRF.');
        }

        $redirectUri = route('oauth.callback', ['platform' => $platform]);
        $tokenUrl = str_replace('{instance}', $request->input('instance', 'mastodon.social'), $provider['token_url']);

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $request->code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->failed()) {
            return redirect()->route('accounts.index', session('oauth_client'))
                ->with('error', 'Token exchange failed: ' . $response->body());
        }

        $tokenData = $response->json();
        $credentials = [
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_at' => isset($tokenData['expires_in'])
                ? now()->addSeconds($tokenData['expires_in'])->toDateTimeString()
                : null,
            'scope' => $tokenData['scope'] ?? '',
        ];

        $clientId = session('oauth_client');
        $accountName = session('oauth_account_name', $platform);

        // Platform-specific post-token-exchange enrichment
        $accountId = $tokenData['username'] ?? $tokenData['sub'] ?? null;

        if (in_array($platform, ['pinterest', 'linkedin_profile', 'linkedin_company'])) {
            try {
                if ($platform === 'pinterest') {
                    $userInfo = Http::withToken($tokenData['access_token'])
                        ->get('https://api.pinterest.com/v5/user_account')->json();
                    $accountId = $userInfo['username'] ?? $accountId;
                    $accountName = $userInfo['full_name'] ?? $userInfo['username'] ?? $accountName;
                } else {
                    $userInfo = Http::withToken($tokenData['access_token'])
                        ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                        ->get('https://api.linkedin.com/v2/userinfo')->json();
                    $credentials['person_id'] = $userInfo['sub'] ?? '';
                    $accountId = $userInfo['sub'] ?? $accountId;
                    $accountName = $userInfo['name'] ?? $accountName;
                }
            } catch (\Exception $e) {
                // Non-fatal — use fallback values
            }
        }

        $account = SocialAccount::create([
            'client_id' => $clientId,
            'platform' => $platform,
            'account_name' => $accountName,
            'account_id' => $accountId ?? $platform . '_' . Str::random(6),
            'credentials' => $this->tokenManager->encrypt($credentials),
            'is_active' => true,
        ]);

        session()->forget(['oauth_client', 'oauth_account_name']);

        return redirect()->route('accounts.edit', [$account->client_id, $account])
            ->with('success', "{$platform} account connected successfully.");
    }
}
