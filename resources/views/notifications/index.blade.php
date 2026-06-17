<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->unreadNotifications->count())
                <div class="mb-4 text-right">
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">Mark all as read</button>
                    </form>
                </div>
            @endif

            <div class="space-y-2">
                @forelse($notifications as $n)
                    <a href="{{ $n->data['action_url'] ?? '#' }}" class="block bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 p-4 transition-all duration-200 {{ $n->unread() ? 'border-l-4 border-l-indigo-500 bg-gradient-to-r from-indigo-50/50 to-white' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm {{ $n->unread() ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }}">
                                    {!! $n->unread() ? '&#9679;' : '&#9675;' !!}
                                </div>
                                <div>
                                    <p class="text-sm {{ $n->unread() ? 'font-semibold text-gray-900' : 'text-gray-600' }}">
                                        {{ $n->data['message'] ?? 'Notification' }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if($n->unread())
                                <button onclick="event.preventDefault(); fetch('{{ route('notifications.read', $n->id) }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>this.closest('a').classList.remove('border-l-indigo-500','bg-gradient-to-r','from-indigo-50/50'))" class="text-xs text-gray-400 hover:text-gray-600">Dismiss</button>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="text-4xl mb-3 text-gray-300">&#128276;</div>
                        <p class="text-gray-500 font-medium">All caught up!</p>
                        <p class="text-xs text-gray-400 mt-1">Notifications will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
