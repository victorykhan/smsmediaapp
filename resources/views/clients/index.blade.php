<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Clients') }}</h2>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 hover:shadow-xl hover:shadow-indigo-300 hover:scale-[1.02] transition-all duration-200 btn-scale">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Client
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($owned->count() || $member->count())
                @if($owned->count())
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                        Your Clients
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                        @foreach($owned as $client)
                            <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-indigo-200 transition-all duration-300 card-hover">
                                <a href="{{ route('clients.show', $client) }}" class="block p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform duration-300">
                                            {{ substr($client->name, 0, 1) }}
                                        </div>
                                        @if(!$client->is_onboarded)
                                            <span class="px-2.5 py-1 text-[10px] font-bold bg-gradient-to-r from-amber-400 to-orange-500 text-white rounded-full uppercase tracking-wider shadow-sm">Setup</span>
                                        @endif
                                    </div>
                                    <h4 class="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">{{ $client->name }}</h4>
                                    @if($client->industry)
                                        <p class="text-sm text-gray-500 mt-1">{{ $client->industry }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-3">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-xs text-gray-400">{{ $client->timezone }}</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($member->count())
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        Teams
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($member as $client)
                            <div class="group bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-emerald-200 transition-all duration-300 card-hover">
                                <a href="{{ route('clients.show', $client) }}" class="block p-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold shadow-lg shadow-emerald-200 group-hover:scale-110 transition-transform duration-300">
                                            {{ substr($client->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors truncate">{{ $client->name }}</h4>
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider
                                                {{ $client->pivot->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($client->pivot->role === 'editor' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $client->pivot->role === 'admin' ? 'bg-indigo-500' : ($client->pivot->role === 'editor' ? 'bg-amber-500' : 'bg-gray-500') }}"></span>
                                                {{ $client->pivot->role }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center mx-auto mb-5">
                            <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">No clients yet</h3>
                        <p class="text-gray-500 mb-6 max-w-sm mx-auto">Create your first client to start managing their social media presence across all platforms.</p>
                        <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-sm text-white shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all duration-200 btn-scale">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Create Client
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
