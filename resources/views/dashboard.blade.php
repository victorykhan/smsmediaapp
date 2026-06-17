<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Clients</p>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ auth()->user()->ownedClients->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Team Access</p>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-200">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ auth()->user()->clients->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pending Setup</p>
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-200">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-amber-600">{{ auth()->user()->ownedClients->where('is_onboarded', false)->count() }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ route('clients.create') }}" class="group block p-5 border border-gray-200 rounded-xl hover:border-indigo-300 hover:shadow-md hover:shadow-indigo-100/50 transition-all duration-200">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-md shadow-indigo-200">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <p class="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">New Client</p>
                        <p class="text-sm text-gray-500 mt-0.5">Add a client and start scheduling</p>
                    </a>
                    <a href="{{ route('clients.index') }}" class="group block p-5 border border-gray-200 rounded-xl hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-100/50 transition-all duration-200">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-md shadow-emerald-200">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </div>
                        <p class="font-semibold text-gray-900 group-hover:text-emerald-700 transition-colors">Manage Clients</p>
                        <p class="text-sm text-gray-500 mt-0.5">View all clients and accounts</p>
                    </a>
                    <div class="block p-5 border border-gray-200 rounded-xl text-center opacity-60 cursor-not-allowed">
                        <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <p class="font-semibold text-gray-500">Compose Post</p>
                        <p class="text-sm text-gray-400 mt-0.5">Coming soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
