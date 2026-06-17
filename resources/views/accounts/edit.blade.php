<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Account — {{ $account->account_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                @php
                    $oauthProviders = config('oauth', []);
                    $platformConfig = $oauthProviders[$account->platform] ?? null;
                    $manualFields = $platformConfig['manual_fields'] ?? [];
                    $creds = [];
                    try {
                        $creds = app(App\Services\TokenManager::class)->decrypt($account->credentials);
                    } catch (\Exception $e) {}
                @endphp

                <form action="{{ route('accounts.update', [$client, $account]) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Name</label>
                        <input type="text" name="account_name" value="{{ old('account_name', $account->account_name) }}" required
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Platform User/Page ID</label>
                        <input type="text" name="account_id" value="{{ old('account_id', $account->account_id) }}"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Access Token / API Key</label>
                        <textarea name="credentials" rows="3"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-xs px-3 py-2"></textarea>
                        <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing credentials. Enter to replace them.</p>
                    </div>

                    {{-- Platform-specific manual fields --}}
                    @foreach($manualFields as $key => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                            <input type="text" name="manual_fields[{{ $key }}]" value="{{ old('manual_fields.' . $key, $creds[$key] ?? '') }}"
                                class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Token Expires</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at', $account->expires_at?->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" @checked($account->is_active)
                            class="rounded-xl border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label for="is_active" class="text-sm text-gray-700 font-medium">Active</label>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                            Update
                        </button>
                        <a href="{{ route('accounts.index', $client) }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
