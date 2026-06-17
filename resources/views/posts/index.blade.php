<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clients.show', $client) }}" class="text-gray-400 hover:text-gray-600 transition">&larr;</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Posts — {{ $client->name }}</h2>
            </div>
            <a href="{{ route('posts.create', $client) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Post
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php $canApprove = auth()->user()->id === $client->user_id || $client->users()->where('user_id', auth()->id())->wherePivot('role', 'admin')->exists(); @endphp

            @if($posts->count())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($posts as $post)
                            <div class="block p-4 hover:bg-gray-50/80 transition group">
                                <div class="flex items-start justify-between">
                                    <a href="{{ route('posts.show', [$client, $post]) }}" class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900 truncate font-medium group-hover:text-indigo-700 transition-colors">{{ $post->content ?: '(No text)' }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            @foreach($post->versions as $v)
                                                @php $p = config("platforms.{$v->platform}"); @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                      style="background: {{ $p['color'] ?? '#666' }}15; color: {{ $p['color'] ?? '#666' }}">
                                                    {{ $p['icon'] ?? substr($v->platform, 0, 2) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </a>
                                    <div class="flex items-center gap-3 ml-4 shrink-0">
                                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold
                                            @if($post->status === 'published') bg-emerald-100 text-emerald-700
                                            @elseif($post->status === 'publishing') bg-purple-100 text-purple-700
                                            @elseif($post->status === 'scheduled') bg-blue-100 text-blue-700
                                            @elseif($post->status === 'pending') bg-yellow-100 text-yellow-700
                                            @elseif($post->status === 'pending_approval') bg-orange-100 text-orange-700
                                            @elseif($post->status === 'failed') bg-red-100 text-red-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ $post->status === 'pending_approval' ? 'Pending' : $post->status }}
                                        </span>
                                        <span class="text-xs text-gray-400 hidden sm:inline">{{ $post->created_at->format('M j, g:ia') }}</span>
                                        @if($post->isPendingApproval() && $canApprove)
                                            <div class="flex gap-1">
                                                <form action="{{ route('posts.approve', [$client, $post]) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs px-2.5 py-1.5 bg-gradient-to-r from-emerald-400 to-emerald-500 text-white rounded-lg font-semibold hover:from-emerald-500 hover:to-emerald-600 shadow-sm transition-all btn-scale">Approve</button>
                                                </form>
                                                <button type="button" @click="showReject({{ $post->id }})" class="text-xs px-2.5 py-1.5 bg-gradient-to-r from-rose-400 to-pink-500 text-white rounded-lg font-semibold hover:from-rose-500 hover:to-pink-600 shadow-sm transition-all btn-scale">Reject</button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="text-center py-16">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                        <p class="text-gray-500 font-medium mb-4">No posts yet.</p>
                        <a href="{{ route('posts.create', $client) }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-sm text-white shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Create First Post
                        </a>
                    </div>
                </div>
            @endif

            @if($posts->hasPages())
                <div class="mt-6">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    function showReject(postId) {
        Swal.fire({
            title: 'Reject Post',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Why is this being rejected?',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#dc2626',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: (reason) => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("posts.reject", [$client, "__POST_ID__"]) }}'.replace('__POST_ID__', postId);
                const csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                if (reason) {
                    const input = document.createElement('input'); input.type = 'hidden'; input.name = 'reason'; input.value = reason;
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
