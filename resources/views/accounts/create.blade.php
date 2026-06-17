<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Connect Account — {{ $client->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                @if($platform)
                    @php $p = $platforms[$platform]; @endphp
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-md"
                                 style="background: {{ $p['color'] }}">
                                {{ $p['icon'] }}
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $p['label'] }}</h3>
                        </div>
                        <p class="text-xs text-gray-500">
                            @if($p['auth_type'] === 'oauth')
                                Uses OAuth authentication. Enter the access token from your {{ $p['label'] }} developer app.
                            @else
                                Uses app password or API key.
                            @endif
                        </p>
                    </div>

                    <form action="{{ route('accounts.store', $client) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="platform" value="{{ $platform }}">

                        @php $oAuthConfig = $oauthProviders[$platform] ?? null; @endphp

                        @if($oAuthConfig && $oAuthConfig['authorize_url'] && config("services.{$platform}.client_id"))
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5 text-center">
                                <p class="text-sm text-blue-800 font-semibold mb-3">Connect with {{ $p['label'] }} via OAuth</p>
                                <a href="{{ route('oauth.redirect', [$platform, $client->id]) }}"
                                   class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold text-xs uppercase tracking-widest shadow-lg shadow-blue-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    Connect {{ $p['label'] }}
                                </a>
                                <p class="text-xs text-blue-600 mt-2">Or enter credentials manually below.</p>
                            </div>
                        @elseif($p['auth_type'] === 'password' && $platform === 'bluesky')
                            <div class="bg-gradient-to-r from-sky-50 to-blue-50 border border-sky-200 rounded-xl p-5 text-center">
                                <p class="text-sm text-sky-800 font-semibold mb-1">Bluesky App Password</p>
                                <p class="text-xs text-sky-600">Go to Settings &rarr; App Passwords on bsky.app to create one.</p>
                            </div>
                        @elseif($platform === 'mastodon')
                            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5">
                                <p class="text-sm text-amber-800 font-semibold mb-1">Mastodon Credentials</p>
                                <p class="text-xs text-amber-700">Enter credentials as JSON with your instance URL and access token:</p>
                                <pre class="mt-2 text-xs bg-white/60 border border-amber-100 rounded-lg p-2 overflow-x-auto font-mono text-amber-900">{&quot;instance&quot;: &quot;mastodon.social&quot;, &quot;access_token&quot;: &quot;your_token_here&quot;}</pre>
                                <p class="text-xs text-amber-600 mt-2">Get these from Preferences &rarr; Development on your instance.</p>
                            </div>
                        @elseif($platform === 'pinterest')
                            <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 rounded-xl p-5">
                                <p class="text-sm text-red-800 font-semibold mb-1">Pinterest Credentials</p>
                                <p class="text-xs text-red-700">Use OAuth to connect, then add your board ID to credentials.</p>
                                <p class="text-xs text-red-600 mt-2">Create a Business app at developers.pinterest.com, set PIN_CLIENT_ID and PIN_CLIENT_SECRET in .env, then click Connect.</p>
                            </div>
                        @elseif(in_array($platform, ['linkedin_profile', 'linkedin_company']))
                            <div class="bg-gradient-to-r from-blue-50 to-sky-50 border border-blue-200 rounded-xl p-5">
                                <p class="text-sm text-blue-800 font-semibold mb-1">{{ $p['label'] }} Credentials</p>
                                <p class="text-xs text-blue-700">Use OAuth to connect. LinkedIn Profile will auto-fetch your profile.</p>
                                <p class="text-xs text-blue-600 mt-2">For Company, add &quot;organization_id&quot; to credentials after connecting (found in LinkedIn Page admin).</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Account Name</label>
                            <input type="text" name="account_name" required
                                class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                            <p class="text-xs text-gray-400 mt-1">Display name for this account (e.g. "My Business Page").</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Platform User/Page ID</label>
                            <input type="text" name="account_id"
                                class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                            <p class="text-xs text-gray-400 mt-1">Your Mastodon account username (e.g. @user).</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Access Token / API Key</label>
                            <textarea name="credentials" rows="3"
                                class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-xs px-3 py-2"></textarea>
                            @if($platform === 'mastodon')
                                <p class="text-xs text-amber-600 mt-1">Paste the JSON above (with your instance and token). Plain token also works (defaults to mastodon.social).</p>
                            @else
                                <p class="text-xs text-gray-400 mt-1">Paste your access token, API key, or app password. Stored encrypted.</p>
                            @endif
                        </div>

                        {{-- Platform-specific manual fields (board_id, organization_id, etc.) --}}
                        @if($platform && isset($oauthProviders[$platform]['manual_fields']))
                            @foreach($oauthProviders[$platform]['manual_fields'] as $key => $label)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                                    <input type="text" name="manual_fields[{{ $key }}]"
                                        class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                                    <p class="text-xs text-gray-400 mt-1">Required for posting on this platform.</p>
                                </div>
                            @endforeach
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Token Expires</label>
                            <input type="date" name="expires_at"
                                class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                            <p class="text-xs text-gray-400 mt-1">Optional. If set, you'll be notified before expiry.</p>
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                                Connect
                            </button>
                            <a href="{{ route('accounts.index', $client) }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition">Cancel</a>
                        </div>
                    </form>
                @else
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Select a Platform</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($platforms as $key => $p)
                            <a href="{{ route('accounts.create', [$client, 'platform' => $key]) }}"
                               class="group border border-gray-200 rounded-xl p-4 text-center hover:border-gray-300 hover:shadow-lg hover:shadow-gray-100/50 transition-all duration-200 card-hover">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-sm font-bold mx-auto mb-2 shadow-sm group-hover:scale-110 transition-transform duration-300"
                                     style="background: {{ $p['color'] }}">
                                    {{ $p['icon'] }}
                                </div>
                                <p class="text-sm font-semibold text-gray-700 group-hover:text-gray-900 transition-colors">{{ $p['label'] }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
