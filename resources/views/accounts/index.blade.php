<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clients.show', $client) }}" class="text-gray-400 hover:text-gray-600 transition">&larr;</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Accounts — {{ $client->name }}
                </h2>
            </div>
            <a href="{{ route('accounts.create', $client) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Connect Account
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($accounts->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($accounts as $account)
                            @php $p = $platforms[$account->platform] ?? null; @endphp
                            <div class="flex items-center justify-between p-4 hover:bg-gray-50/80 transition group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-sm group-hover:scale-110 transition-transform duration-300"
                                         style="background: {{ $p['color'] ?? '#666' }}">
                                        {{ $p['icon'] ?? substr($account->platform, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $account->account_name }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-500">{{ $p['label'] ?? ucfirst($account->platform) }}</span>
                                            @if(!$account->is_active)
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-red-600">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('accounts.edit', [$client, $account]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Edit</a>
                                    <form action="{{ route('accounts.destroy', [$client, $account]) }}" method="POST" class="inline"
                                        x-data @submit.prevent="Swal.fire({ title:'Disconnect account?', text:'{{ $account->account_name }} will be disconnected.', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Disconnect' }).then(r => r.isConfirmed && $el.submit())">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">Disconnect</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="text-center py-16">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mx-auto mb-5">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">No accounts connected</h3>
                        <p class="text-gray-500 mb-6">Connect your first social media account to start posting.</p>
                        <a href="{{ route('accounts.create', $client) }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-sm text-white shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Connect Account
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
