<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-gray-600 transition">&larr;</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $client->name }}</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('analytics.index', $client) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all btn-scale">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Analytics
                </a>
                <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all btn-scale">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>
                @if(!$client->is_onboarded)
                    <a href="{{ route('clients.onboarding', $client) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-amber-400 to-orange-500 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-amber-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Complete Setup
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!$client->is_onboarded)
                <div class="mb-6 px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl text-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/></svg>
                        </div>
                        <span class="font-medium text-amber-800">Setup incomplete — finish the onboarding wizard to activate this client.</span>
                    </div>
                    <a href="{{ route('clients.onboarding', $client) }}" class="font-semibold text-amber-700 hover:text-amber-800 underline transition">Continue Setup &rarr;</a>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Connected Accounts
                            </h3>
                            <a href="{{ route('accounts.create', $client) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition btn-scale">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Add
                            </a>
                        </div>
                        @if($client->socialAccounts->count())
                            <div class="space-y-1">
                                @foreach($client->socialAccounts as $account)
                                    @php $p = config("platforms.{$account->platform}"); @endphp
                                    <div class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 transition group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-sm"
                                                 style="background: {{ $p['color'] ?? '#666' }}">
                                                {{ $p['icon'] ?? substr($account->platform, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $account->account_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $p['label'] ?? ucfirst($account->platform) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                                                {{ $account->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $account->is_active ? 'bg-emerald-500' : 'bg-red-400' }}"></span>
                                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <a href="{{ route('accounts.index', $client) }}" class="text-xs text-gray-500 hover:text-indigo-600 font-medium transition inline-flex items-center gap-1">
                                    Manage all accounts
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-10">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                </div>
                                <p class="text-sm text-gray-500 font-medium">No accounts connected yet.</p>
                                <a href="{{ route('accounts.create', $client) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium mt-1 inline-block">Connect your first account</a>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                Recent Posts
                            </h3>
                            <a href="{{ route('posts.create', $client) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition btn-scale">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                New
                            </a>
                        </div>
                        @if($client->posts->count())
                            <div class="space-y-1">
                                @foreach($client->posts->take(5) as $post)
                                    <a href="{{ route('posts.show', [$client, $post]) }}" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 transition group">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900 truncate group-hover:text-indigo-700 transition-colors">{{ $post->content ?: '(No text)' }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                @foreach($post->versions as $v)
                                                    @php $p = config("platforms.{$v->platform}"); @endphp
                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded flex items-center gap-1"
                                                          style="background: {{ $p['color'] ?? '#666' }}15; color: {{ $p['color'] ?? '#666' }}">
                                                        {{ $p['icon'] ?? substr($v->platform, 0, 2) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400 shrink-0 ml-3">{{ $post->created_at->format('M j, Y') }}</span>
                                    </a>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <a href="{{ route('posts.index', $client) }}" class="text-xs text-gray-500 hover:text-indigo-600 font-medium transition inline-flex items-center gap-1">
                                    View all posts
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @else
                            <div class="text-center py-10">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </div>
                                <p class="text-sm text-gray-500 font-medium">No posts yet.</p>
                                <a href="{{ route('posts.create', $client) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium mt-1 inline-block">Create your first post</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Details
                        </h3>
                        <dl class="space-y-3 text-sm">
                            @if($client->website)
                                <div><dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Website</dt><dd class="text-gray-900 mt-0.5">{{ $client->website }}</dd></div>
                            @endif
                            @if($client->industry)
                                <div class="mt-2"><dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Industry</dt><dd class="text-gray-900 mt-0.5">{{ $client->industry }}</dd></div>
                            @endif
                            <div class="mt-2"><dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Timezone</dt><dd class="text-gray-900 mt-0.5">{{ $client->timezone }}</dd></div>
                        </dl>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-10a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                            Team Members
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-1">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                        {{ substr($client->owner->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $client->owner->name }}</p>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-600 uppercase tracking-wider">
                                            <span class="w-1 h-1 rounded-full bg-gray-500"></span>
                                            Owner
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @foreach($client->users as $user)
                                <div class="flex items-center justify-between group py-1">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wider
                                                {{ $user->pivot->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($user->pivot->role === 'editor' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                                <span class="w-1 h-1 rounded-full {{ $user->pivot->role === 'admin' ? 'bg-indigo-500' : ($user->pivot->role === 'editor' ? 'bg-amber-500' : 'bg-gray-500') }}"></span>
                                                {{ ucfirst($user->pivot->role) }}
                                            </span>
                                        </div>
                                    </div>
                                    @if(auth()->id() === $client->user_id)
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                            <form action="{{ route('clients.members.role', [$client, $user]) }}" method="POST" class="inline">
                                                @csrf @method('PUT')
                                                <select name="role" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded-lg px-1.5 py-1 focus:ring-1 focus:ring-indigo-500">
                                                    <option value="admin" @if($user->pivot->role === 'admin') selected @endif>Admin</option>
                                                    <option value="editor" @if($user->pivot->role === 'editor') selected @endif>Editor</option>
                                                    <option value="viewer" @if($user->pivot->role === 'viewer') selected @endif>Viewer</option>
                                                </select>
                                            </form>
                                            <form action="{{ route('clients.members.remove', [$client, $user]) }}" method="POST" class="inline"
                                                x-data @submit.prevent="Swal.fire({ title:'Remove team member?', text:'Remove {{ $user->name }} from {{ $client->name }}?', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Remove' }).then(r => r.isConfirmed && $el.submit())">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition" title="Remove from team">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <form action="{{ route('clients.invite', $client) }}" method="POST" class="mt-4 pt-4 border-t border-gray-100">
                            @csrf
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Invite by email</label>
                            <div class="flex flex-col gap-2">
                                <input type="email" name="email" placeholder="email@example.com" required
                                    class="rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                                <div class="flex gap-2">
                                    <select name="role" class="rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 flex-1">
                                        <option value="admin">Admin</option>
                                        <option value="editor" selected>Editor</option>
                                        <option value="viewer">Viewer</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-bold rounded-xl uppercase tracking-wider shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">Send</button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Admins: manage accounts & approve posts. Editors: create posts. Viewers: read-only.</p>
                            @error('email') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
