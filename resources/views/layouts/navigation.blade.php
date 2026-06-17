<nav x-data="{ open: false, notifOpen: false, unreadCount: 0, notifications: [] }"
     x-init="
        async function loadUnread() {
            try {
                let r = await fetch('{{ route('notifications.unread') }}');
                let d = await r.json();
                unreadCount = d.count;
                notifications = d.notifications || [];
                if (notifications.length > 6) notifications.length = 6;
            } catch(e) {}
        }
        loadUnread();
        setInterval(loadUnread, 15000);
     "
     class="bg-slate-900 border-b border-slate-800 nav-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-white/5 transition group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-white font-bold text-sm tracking-tight hidden sm:inline">{{ config('app.name', 'Social Scheduler') }}</span>
                </a>

                <div class="hidden sm:flex items-center ml-6 space-x-1">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        {{ __('Clients') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Notification Bell -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open; if(open) { loadUnread(); $refs.badge.style.transform='scale(0)'; setTimeout(()=>$refs.badge.style.display='none', 300); fetch('{{ route('notifications.mark-all-read') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).catch(()=>{}); }"
                            class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-200 btn-scale">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-ref="badge" x-show="unreadCount > 0"
                              x-transition:enter.duration.200ms
                              class="absolute -top-0.5 -right-0.5 w-4.5 h-4.5 bg-gradient-to-br from-rose-400 to-pink-600 rounded-full flex items-center justify-center text-[9px] font-bold text-white shadow-lg shadow-rose-500/30"
                              style="display: none;">
                            <span x-text="unreadCount > 99 ? '99+' : unreadCount" class="leading-none">0</span>
                        </span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="animate-slide-down"
                         x-transition:leave="animate-slide-up"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100/80 overflow-hidden z-50"
                         style="display: none;">
                        <div class="p-3 border-b border-gray-100 flex justify-between items-center">
                            <span class="text-sm font-semibold text-gray-900">Notifications</span>
                            <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium transition">View all</a>
                        </div>
                        <div class="max-h-[320px] overflow-y-auto">
                            <template x-for="n in notifications" :key="n.id">
                                <a :href="n.data?.action_url || '#'"
                                   class="flex items-start gap-3 p-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0 group">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-700 leading-snug" x-text="n.data?.message || ''"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" x-text="n.created_at ? new Date(n.created_at).toLocaleDateString() : ''"></p>
                                    </div>
                                </a>
                            </template>
                            <div x-show="notifications.length === 0" class="p-8 text-center">
                                <div class="text-2xl mb-2 text-gray-300">&#128276;</div>
                                <p class="text-sm text-gray-500">No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition-all duration-200 btn-scale">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-[10px] font-bold text-white shadow-md">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">{{ Auth::user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="animate-slide-down"
                         x-transition:leave="animate-slide-up"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-100/80 overflow-hidden z-50"
                         style="display: none;">
                        <div class="p-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="p-1">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Hamburger -->
                <button @click="open = !open" class="sm:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Nav -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-800">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                {{ __('Clients') }}
            </x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-3 border-t border-slate-800 px-3">
            <div class="px-3 pb-3">
                <div class="font-medium text-sm text-white">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
            </div>
        </div>
    </div>
</nav>
