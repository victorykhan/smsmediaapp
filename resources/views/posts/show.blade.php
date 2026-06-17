<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('posts.index', $client) }}" class="text-gray-400 hover:text-gray-600 transition">&larr;</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Post — {{ $client->name }}</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('posts.edit', [$client, $post]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all btn-scale">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form action="{{ route('posts.destroy', [$client, $post]) }}" method="POST" class="inline"
                    x-data @submit.prevent="Swal.fire({ title:'Delete this post?', text:'This cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Yes, delete' }).then(r => r.isConfirmed && $el.submit())">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-white border border-red-200 rounded-xl font-medium text-red-600 hover:bg-red-50 hover:border-red-300 transition-all btn-scale">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php $canApprove = auth()->user()->id === $client->user_id || $client->users()->where('user_id', auth()->id())->wherePivot('role', 'admin')->exists(); @endphp

            @if($post->isPendingApproval() && $canApprove)
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-xl p-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-orange-800">Pending Approval</p>
                            <p class="text-xs text-orange-600">This post needs your review before it goes live.</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('posts.approve', [$client, $post]) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-emerald-400 to-emerald-500 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md hover:shadow-lg hover:scale-[1.02] transition-all btn-scale">Approve</button>
                        </form>
                        <button type="button" onclick="showReject({{ $post->id }})" class="px-4 py-2 bg-white border border-red-200 text-red-600 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-red-50 hover:border-red-300 transition-all btn-scale">Reject</button>
                    </div>
                </div>
            @endif

            @if($post->rejection_reason)
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">Rejected</p>
                            @if($post->reviewer)<p class="text-xs text-yellow-600">By {{ $post->reviewer->name }}</p>@endif
                        </div>
                    </div>
                    @if($post->rejection_reason)
                        <p class="text-sm text-yellow-700 ml-11 mt-1">&ldquo;{{ $post->rejection_reason }}&rdquo;</p>
                    @endif
                </div>
            @endif

            <!-- Post Content -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold
                        @if($post->status === 'published') bg-emerald-100 text-emerald-700
                        @elseif($post->status === 'publishing') bg-purple-100 text-purple-700
                        @elseif($post->status === 'scheduled') bg-blue-100 text-blue-700
                        @elseif($post->status === 'pending') bg-yellow-100 text-yellow-700
                        @elseif($post->status === 'pending_approval') bg-orange-100 text-orange-700
                        @elseif($post->status === 'failed') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ $post->status === 'pending_approval' ? 'Pending Approval' : $post->status }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $post->created_at->format('M j, Y g:ia') }}</span>
                </div>
                <div class="prose prose-sm max-w-none">
                    <p class="text-gray-900">{{ $post->content ?: '(No text content)' }}</p>
                </div>
            </div>

            <!-- Platform Versions -->
            <div>
                <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Platform Versions
                </h3>
                <div class="space-y-3">
                    @foreach($post->versions as $version)
                        @php $p = config("platforms.{$version->platform}"); @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 card-hover">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[9px] font-bold shadow-sm"
                                         style="background: {{ $p['color'] ?? '#666' }}">
                                        {{ $p['icon'] ?? substr($version->platform, 0, 2) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $p['label'] ?? ucfirst($version->platform) }}</span>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                                    @if($version->status === 'published') bg-emerald-100 text-emerald-700
                                    @elseif($version->status === 'publishing') bg-purple-100 text-purple-700
                                    @elseif($version->status === 'failed') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $version->status }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700">{{ $version->content ?: '(Same as master)' }}</p>
                            @if($version->scheduled_at)
                                <p class="text-xs text-gray-400 mt-1">Scheduled: {{ $version->scheduled_at->format('M j, Y g:ia') }}</p>
                            @endif
                            @if($version->published_at)
                                <p class="text-xs text-gray-400 mt-1">Published: {{ $version->published_at->format('M j, Y g:ia') }}</p>
                            @endif
                            @if($version->error_message)
                                <p class="text-xs text-red-500 mt-1 bg-red-50 rounded-lg px-2 py-1">Error: {{ $version->error_message }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
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
