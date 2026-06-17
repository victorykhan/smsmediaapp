<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Post — {{ $client->name }}</h2>
    </x-slot>

    <div class="py-12" x-data="composerState">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('posts.update', [$client, $post]) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <textarea name="content" x-model="content" rows="8"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
                                placeholder="Write your post..."></textarea>

                            <div class="mt-3" x-show="selectedPlatforms.length > 0" x-cloak>
                                <div class="flex flex-wrap gap-3 text-sm">
                                    <template x-for="p in selectedPlatforms" :key="p.key">
                                        <span class="px-2 py-1 bg-gray-50 rounded-md border border-gray-100">
                                            <span class="font-medium" x-text="p.label"></span>:
                                            <span x-bind:class="content.length > p.limit ? 'text-red-600 font-bold' : 'text-gray-700'">
                                                <span x-text="content.length"></span><template x-if="p.limit">/<span x-text="p.limit"></span></template>
                                            </span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700">Hashtags</label>
                                <p class="text-xs text-gray-400">Type and press Enter or comma to add</p>
                            </div>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <template x-for="(tag, i) in hashtags" :key="i">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">
                                        #<span x-text="tag"></span>
                                        <button type="button" @click="hashtags.splice(i, 1)" class="text-blue-400 hover:text-blue-600">&times;</button>
                                    </span>
                                </template>
                            </div>
                            <input type="text" x-model="hashInput" @keydown.prevent.enter="addHashtag" @keydown.prevent.,="addHashtag"
                                placeholder="Add a hashtag..."
                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <input type="hidden" name="hashtags" x-bind:value="JSON.stringify(hashtags)">
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Media</label>
                            <input type="file" name="media[]" multiple accept="image/*,video/*"
                                @change="handleFiles($event)"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">

                            <div x-show="uploadProgress > 0 && uploadProgress < 100" x-cloak class="mt-3">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span class="font-medium">Uploading...</span>
                                    <span class="font-bold tabular-nums" x-text="uploadProgress + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden shadow-inner">
                                    <div class="h-full rounded-full transition-all duration-300 ease-out"
                                         x-bind:style="'width: ' + uploadProgress + '%; background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899)'">
                                    </div>
                                </div>
                            </div>

                            <div x-show="uploadedFiles.length" x-cloak class="mt-3 space-y-2">
                                <template x-for="(f, i) in uploadedFiles" :key="i">
                                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 rounded-md text-sm">
                                        <span x-text="f.name"></span>
                                        <button type="button" @click="removeFile(i)" class="text-red-400 hover:text-red-600">&times;</button>
                                    </div>
                                </template>
                            </div>

                            <input type="hidden" name="media_paths" x-bind:value="JSON.stringify(uploadedPaths)">
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="font-semibold text-gray-900 mb-3">Post To</h3>
                            @if($accounts->count())
                                <div class="space-y-2 max-h-80 overflow-y-auto">
                                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-2 border-b border-gray-100">
                                        <input type="checkbox" @click="toggleAll($event.target.checked)"
                                            class="rounded border-gray-300 text-gray-800">
                                        <span class="font-medium">Select All</span>
                                    </label>
                                    @foreach($accounts as $account)
                                        @php $p = $platforms[$account->platform] ?? null; @endphp
                                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 -mx-2 px-2 py-1 rounded">
                                            <input type="checkbox" name="accounts[]" value="{{ $account->id }}"
                                                {{ in_array($account->id, $selectedAccounts) ? 'checked' : '' }}
                                                @change="toggle('{{ $account->platform }}', $event.target.checked)"
                                                class="rounded border-gray-300 text-gray-800 acc-cb">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-[8px] font-bold shrink-0"
                                                 style="background: {{ $p['color'] ?? '#666' }}">
                                                {{ $p['icon'] ?? substr($account->platform, 0, 2) }}
                                            </div>
                                            <span class="truncate">{{ $account->account_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-show="selectedPlatforms.length > 0" x-cloak>
                            <h3 class="font-semibold text-gray-900 mb-3">Preview</h3>
                            <template x-for="p in selectedPlatforms" :key="p.key">
                                <div class="border border-gray-100 rounded-lg p-3 mb-2 last:mb-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-5 h-5 rounded-full flex items-center justify-center text-white text-[6px] font-bold"
                                             x-bind:style="'background:' + p.color">
                                            <span x-text="p.icon"></span>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700" x-text="p.label"></span>
                                        <span class="ml-auto text-[10px]" x-bind:class="content.length > p.limit ? 'text-red-600 font-bold' : 'text-gray-400'">
                                            <span x-text="content.length"></span><template x-if="p.limit">/<span x-text="p.limit"></span></template>
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-wrap" x-text="displayContent"></p>
                                    <template x-if="previews.length">
                                        <div class="mt-2 flex gap-2 overflow-x-auto">
                                            <template x-for="(pr, pi) in previews" :key="pi">
                                                <template x-if="pr.type.startsWith('image/')">
                                                    <img x-bind:src="pr.url" class="w-20 h-20 object-cover rounded-lg border border-gray-200 shrink-0">
                                                </template>
                                                <template x-if="pr.type.startsWith('video/')">
                                                    <video x-bind:src="pr.url" class="w-20 h-20 object-cover rounded-lg border border-gray-200 shrink-0" muted></video>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-3">
                            <button type="submit" :disabled="uploading || selectedAccounts.length === 0"
                                class="w-full px-4 py-2 rounded-md font-semibold text-xs text-white uppercase tracking-widest transition"
                                x-bind:class="uploading || selectedAccounts.length === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-gray-800 hover:bg-gray-700'">
                                <span x-text="uploading ? 'Uploading...' : 'Update & Post'"></span>
                            </button>

                            <button type="button" @click="showScheduler = !showScheduler" :disabled="uploading"
                                class="w-full px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-widest border transition"
                                x-bind:class="uploading ? 'bg-gray-100 text-gray-300 border-gray-100 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                                Reschedule
                            </button>

                            <div x-show="showScheduler" x-cloak>
                                <input type="datetime-local" name="schedule_at" value="{{ $post->versions->first()?->scheduled_at?->format('Y-m-d\TH:i') }}"
                                    class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('composerState', () => ({
                content: `{{ $post->content ? addslashes($post->content) : '' }}`,
                hashtags: [],
                hashInput: '',
                showScheduler: false,
                uploading: false,
                uploadProgress: 0,
                uploadedFiles: [],
                uploadedPaths: @json($post->versions->first()?->media ?? []),
                previews: [],
                platforms: @json($platforms),
                selectedPlatforms: [],

                get selectedAccounts() {
                    return this.selectedPlatforms.map(p => p.key);
                },

                get displayContent() {
                    let text = this.content;
                    if (this.hashtags.length) {
                        text += '\n\n' + this.hashtags.map(t => '#' + t).join(' ');
                    }
                    return text || '(empty)';
                },

                addHashtag() {
                    const tag = this.hashInput.replace(/^#/, '').trim();
                    if (tag && !this.hashtags.includes(tag)) {
                        this.hashtags.push(tag);
                    }
                    this.hashInput = '';
                },

                toggle(platformKey, checked) {
                    const p = this.platforms[platformKey];
                    if (!p) return;
                    if (checked) {
                        if (!this.selectedPlatforms.find(sp => sp.key === platformKey)) {
                            this.selectedPlatforms.push({
                                key: platformKey,
                                label: p.label,
                                limit: p.text_limit || 99999,
                                media_required: p.media_required || null,
                                color: p.color,
                                icon: p.icon,
                            });
                        }
                    } else {
                        this.selectedPlatforms = this.selectedPlatforms.filter(sp => sp.key !== platformKey);
                    }
                },

                toggleAll(checked) {
                    document.querySelectorAll('.acc-cb').forEach(cb => {
                        cb.checked = checked;
                        const label = cb.closest('label');
                        const text = label ? label.textContent : '';
                        for (const key in this.platforms) {
                            if (text.includes(key) || text.includes(this.platforms[key].label)) {
                                this.toggle(key, checked);
                                break;
                            }
                        }
                    });
                    if (!checked) this.selectedPlatforms = [];
                },

                handleFiles(event) {
                    const files = event.target.files;
                    if (!files.length) return;
                    this.uploading = true;
                    this.uploadProgress = 0;

                    for (const file of files) {
                        this.uploadedFiles.push({ name: file.name, size: (file.size / 1024).toFixed(1) + ' KB', file });
                        this.previews.push({ url: URL.createObjectURL(file), type: file.type });
                    }

                    const formData = new FormData();
                    for (const f of files) formData.append('media[]', f);
                    formData.append('_token', '{{ csrf_token() }}');

                    const xhr = new XMLHttpRequest();
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    };
                    xhr.onload = () => {
                        this.uploading = false;
                        this.uploadProgress = 100;
                        if (xhr.status === 200) {
                            const resp = JSON.parse(xhr.responseText);
                            if (resp.paths) resp.paths.forEach(path => this.uploadedPaths.push(path));
                        }
                    };
                    xhr.onerror = () => { this.uploading = false; this.uploadProgress = 0; };
                    xhr.open('POST', '{{ route("media.upload") }}');
                    xhr.send(formData);
                },

                removeFile(index) {
                    if (this.previews[index]) URL.revokeObjectURL(this.previews[index].url);
                    this.previews.splice(index, 1);
                    this.uploadedFiles.splice(index, 1);
                    if (this.uploadedPaths[index]) this.uploadedPaths.splice(index, 1);
                },

                init() {
                    const self = this;
                    document.querySelectorAll('.acc-cb:checked').forEach(cb => {
                        const text = cb.closest('label')?.textContent || '';
                        for (const key in this.platforms) {
                            if (text.includes(key) || text.includes(this.platforms[key].label)) {
                                const p = this.platforms[key];
                                self.selectedPlatforms.push({
                                    key, label: p.label, limit: p.text_limit || 99999,
                                    media_required: p.media_required || null,
                                    color: p.color, icon: p.icon,
                                });
                                break;
                            }
                        }
                    });
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
