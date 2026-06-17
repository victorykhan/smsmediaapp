<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SocialAccount;
use App\Services\TokenManager;
use Illuminate\Http\Request;

class SocialAccountsController extends Controller
{
    public function __construct(
        private TokenManager $tokenManager
    ) {}

    public function index(Client $client)
    {
        $accounts = $client->socialAccounts;
        $platforms = config('platforms');
        return view('accounts.index', compact('client', 'accounts', 'platforms'));
    }

    public function create(Client $client, Request $request)
    {
        $platform = $request->query('platform');
        $platforms = config('platforms');
        $oauthProviders = config('oauth', []);

        if ($platform && !isset($platforms[$platform])) {
            return redirect()->route('accounts.index', $client)
                ->with('error', 'Unknown platform.');
        }

        return view('accounts.create', compact('client', 'platform', 'platforms', 'oauthProviders'));
    }

    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'platform' => ['required', 'string'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_id' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
            'credentials' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $validated['client_id'] = $client->id;

        // Merge manual fields into credentials
        $manualFields = $request->input('manual_fields', []);

        // Encrypt credentials if plaintext provided
        if (!empty($validated['credentials'])) {
            $raw = $validated['credentials'];
            $creds = json_decode($raw, true) ?? ['token' => $raw];
            $creds = array_merge($creds, $manualFields);
            $validated['credentials'] = $this->tokenManager->encrypt($creds);
        } elseif (!empty($manualFields)) {
            $validated['credentials'] = $this->tokenManager->encrypt($manualFields);
        }

        SocialAccount::create($validated);

        return redirect()->route('accounts.index', $client)
            ->with('success', ucfirst($validated['platform']) . ' account connected.');
    }

    public function edit(Client $client, SocialAccount $account)
    {
        $platforms = config('platforms');
        return view('accounts.edit', compact('client', 'account', 'platforms'));
    }

    public function update(Request $request, Client $client, SocialAccount $account)
    {
        $validated = $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'account_id' => ['nullable', 'string', 'max:255'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
            'credentials' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $manualFields = $request->input('manual_fields', []);

        if (!empty($validated['credentials'])) {
            $raw = $validated['credentials'];
            $creds = json_decode($raw, true) ?? ['token' => $raw];
            $creds = array_merge($creds, $manualFields);
            $validated['credentials'] = $this->tokenManager->encrypt($creds);
        } elseif (!empty($manualFields)) {
            // Merge manual fields into existing credentials
            try {
                $existing = $this->tokenManager->decrypt($account->credentials);
            } catch (\Exception $e) {
                $existing = [];
            }
            $existing = array_merge($existing, $manualFields);
            $validated['credentials'] = $this->tokenManager->encrypt($existing);
        } else {
            unset($validated['credentials']);
        }

        $account->update($validated);

        return redirect()->route('accounts.index', $client)
            ->with('success', 'Account updated.');
    }

    public function destroy(Client $client, SocialAccount $account)
    {
        $account->delete();
        return redirect()->route('accounts.index', $client)
            ->with('success', 'Account disconnected.');
    }
}
