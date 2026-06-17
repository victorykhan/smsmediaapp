<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Post — {{ $client->name }}</h2>
</x-slot>

<div class="py-12" x-data="composerState">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('posts.store', $client) }}" method="POST" @submit.prevent="submitPost">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- ─── LEFT COLUMN: Content + Media  --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Step 1: Content --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                Step 1: Write your post
                                <span class="text-gray-400 font-normal"> &middot; What do you want to say?</span>
                            </label>
                            <span class="text-xs text-gray-400" x-text="'#' + content.length + ' chars'"></span>
                        </div>
                        <textarea name="content" x-model="content" rows="6"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm"
                            placeholder="Type your message here…&#10;&#10;Tip: You can use @mentions, #hashtags, and links.&#10;&#10;Each platform handles formatting differently — see the preview panel on the right."></textarea>

                        {{-- Per-platform character counter --}}
                        <div class="mt-3 flex flex-wrap gap-2" x-show="selectedPlatforms.length > 0">
                            <template x-for="p in selectedPlatforms" :key="p.key">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium border"
                                      x-bind:class="content.length > (p.limit || 99999) ? 'bg-red-50 border-red-200 text-red-700' : 'bg-gray-50 border-gray-100 text-gray-600'">
                                    <span class="w-2 h-2 rounded-full" x-bind:style="'background:' + p.color"></span>
                                    <span x-text="p.label"></span>
                                    <template x-if="p.limit">
                                        <span>
                                            <span x-bind:class="content.length > (p.limit || 99999) ? 'font-bold' : ''" x-text="content.length"></span>
                                            /<span x-text="p.limit"></span>
                                        </span>
                                    </template>
                                </span>
                            </template>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">
                            <strong>Hint:</strong> All platforms will get the same text.
                            <template x-if="selectedPlatforms.length">
                                <span>Character counters above warn you when you exceed a platform's limit.</span>
                            </template>
                            <template x-if="!selectedPlatforms.length">
                                <span>Select platforms on the right to see their limits.</span>
                            </template>
                        </p>
                    </div>

                    {{-- Step 2: Hashtags (optional) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                Hashtags
                                <span class="text-gray-400 font-normal"> &middot; Optional</span>
                            </label>
                            <span class="text-xs text-gray-400" x-text="'#' + hashtags.length + ' tags'"></span>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <template x-for="(tag, i) in hashtags" :key="i">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 text-sm rounded-full border border-indigo-100">
                                    #<span x-text="tag"></span>
                                    <button type="button" @click="hashtags.splice(i, 1)" class="text-indigo-400 hover:text-red-500 transition-colors ml-0.5">&times;</button>
                                </span>
                            </template>
                        </div>
                        <input type="text" x-model="hashInput" @keydown.prevent.enter="addHashtag" @keydown.prevent.,="addHashtag"
                            placeholder="Type a hashtag and press Enter…"
                            class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                        <input type="hidden" name="hashtags" x-bind:value="JSON.stringify(hashtags)">
                        <p class="text-xs text-gray-400 mt-2">
                            <strong>Tip:</strong> Hashtags are appended to every platform version.
                            Press <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[10px] font-mono">Enter</kbd> or
                            <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[10px] font-mono">,</kbd> to add.
                        </p>
                    </div>

                    {{-- Step 3: Media (with chunked upload) --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                Media
                                <span class="text-gray-400 font-normal"> &middot; Optional — images &amp; video</span>
                            </label>
                            <span class="text-xs text-gray-400" x-text="uploadedFiles.length + ' files'"></span>
                        </div>

                        {{-- Media required / incompatible warning --}}
                        <div x-show="mediaRequiredPlatforms.length > 0 || incompatibleSelected" x-cloak class="mb-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-amber-800" x-text="incompatibleSelected ? 'Incompatible file types' : 'Media required'"></p>
                                    <p class="text-amber-700 text-xs mt-0.5">
                                        <template x-if="!incompatibleSelected">
                                            <span>
                                                <template x-for="(p, i) in mediaRequiredPlatforms" :key="i">
                                                    <span><span x-text="p.label"></span><template x-if="i < mediaRequiredPlatforms.length - 1">, </template></span>
                                                </template>
                                                <span> require<template x-if="mediaRequiredPlatforms.length === 1">s</template> attached media.
                                            </span>
                                        </template>
                                        <template x-if="incompatibleSelected">
                                            <span>Selected platforms need file types that weren't uploaded (video required but only images present).</span>
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Resume notification --}}
                        <div x-show="pendingResumes.length > 0" class="mb-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-amber-800">Incomplete uploads found</p>
                                    <p class="text-amber-700 text-xs mt-0.5">
                                        <template x-for="(p, i) in pendingResumes" :key="i">
                                            <span><span x-text="p.name"></span><template x-if="i < pendingResumes.length - 1">, </template></span>
                                        </template>
                                        — drop or select the same file(s) again to resume.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Drop zone --}}
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:border-indigo-400 hover:bg-indigo-50/20 transition-all duration-200 cursor-pointer"
                             @click="document.getElementById('cr-file-input').click()"
                             @dragover.prevent="$event.currentTarget.classList.add('border-indigo-400', 'bg-indigo-50/30')"
                             @dragleave.prevent="$event.currentTarget.classList.remove('border-indigo-400', 'bg-indigo-50/30')"
                             @drop.prevent="handleChunkedDrop($event)">
                            <div class="text-gray-400 mb-2">
                                <svg class="w-10 h-10 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">Drop files here or click to browse</p>
                            <p class="text-xs text-gray-400 mt-1">Supports images (JPG, PNG, GIF, WebP) and videos (MP4, MOV, AVI)</p>
                            <p class="text-xs text-gray-400">No file size limit — large files are uploaded in chunks</p>
                            <input type="file" id="cr-file-input" multiple accept="image/*,video/*" @change="handleChunkedFiles($event)" class="hidden">
                        </div>

                        {{-- Per-file upload progress --}}
                        <div class="mt-4 space-y-2" x-show="uploadingFiles.length > 0">
                            <template x-for="(f, i) in uploadingFiles" :key="i">
                                <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                    <div class="flex items-center justify-between text-sm mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <template x-if="f.status === 'uploading'">
                                                <svg class="w-4 h-4 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            </template>
                                            <template x-if="f.status === 'done'">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                            <template x-if="f.status === 'error'">
                                                <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </template>
                                            <span class="font-medium text-gray-700 truncate" x-text="f.name"></span>
                                            <span class="text-xs text-gray-400" x-text="f.size"></span>
                                        </div>
                                        <span class="text-xs font-bold tabular-nums" x-text="f.progress + '%'"></span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 ease-out"
                                             x-bind:style="'width:' + f.progress + '%'"
                                             x-bind:class="f.status === 'error' ? 'bg-red-400' : (f.status === 'done' ? 'bg-emerald-400' : 'bg-gradient-to-r from-indigo-500 to-purple-500')">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1" x-show="f.message" x-text="f.message"></p>
                                </div>
                            </template>
                        </div>

                        {{-- Uploaded files list --}}
                        <div class="mt-3 space-y-2" x-show="uploadedFiles.length > 0">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ready for scheduling</p>
                            <template x-for="(f, i) in uploadedFiles" :key="i">
                                <div class="flex items-center justify-between px-3 py-2 bg-gradient-to-r from-gray-50 to-white rounded-lg text-sm border border-gray-100">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <template x-if="f.type?.startsWith('image/')">
                                            <img x-bind:src="f.url" class="w-10 h-10 object-cover rounded-lg border border-gray-200 shrink-0">
                                        </template>
                                        <template x-if="f.type?.startsWith('video/')">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                        </template>
                                        <div class="min-w-0">
                                            <span class="text-sm text-gray-700 truncate block" x-text="f.name"></span>
                                            <span class="text-xs text-gray-400" x-text="f.size"></span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeFile(i)" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Remove">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <input type="hidden" name="media_paths" x-bind:value="JSON.stringify(uploadedPaths)">
                        <p class="text-xs text-gray-400 mt-3">
                            <strong>How it works:</strong> Files are uploaded in 2 MB chunks so there's no size limit.
                            Each platform receives your media immediately at schedule time — it's stored as a draft on their servers,
                            not on ours. <strong>This means zero storage cost for large files.</strong>
                        </p>
                    </div>
                </div>

                {{-- ─── RIGHT COLUMN: Platforms + Preview + Schedule  --}}
                <div class="space-y-6">

                    {{-- Step 4: Select Platforms --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">Post To</h3>
                            <span class="text-xs text-gray-400" x-text="selectedPlatforms.length + ' selected'"></span>
                        </div>
                        <p class="text-xs text-gray-400 mb-3">Choose where this post will be published.</p>

                        @if($accounts->count())
                            <div class="space-y-1 max-h-72 overflow-y-auto">
                                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-2 border-b border-gray-100 mb-1">
                                    <input type="checkbox" @click="toggleAll($event.target.checked)"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-medium text-gray-800">Select All / None</span>
                                </label>
                                @foreach($accounts as $account)
                                    @php $p = $platforms[$account->platform] ?? null; @endphp
                                    @php
                                        $mediaMsg = match($p['media_required'] ?? null) {
                                            'video' => 'Needs video',
                                            'image_or_video' => 'Needs image/video',
                                            default => null,
                                        };
                                    @endphp
                                    <label class="flex items-center gap-2.5 text-sm cursor-pointer hover:bg-gray-50 -mx-2 px-2 py-1.5 rounded-lg transition group">
                                        <input type="checkbox" name="accounts[]" value="{{ $account->id }}"
                                            @change="toggle('{{ $account->platform }}', $event.target.checked)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 acc-cb">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[9px] font-bold shrink-0 shadow-sm group-hover:scale-110 transition-transform"
                                             style="background: {{ $p['color'] ?? '#666' }}">
                                            {{ $p['icon'] ?? substr($account->platform, 0, 2) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="truncate block font-medium text-gray-800">{{ $account->account_name }}</span>
                                            <span class="text-[10px] text-gray-400">{{ $p['label'] ?? ucfirst($account->platform) }}</span>
                                        </div>
                                        {{-- Flashing danger badge for incompatible file types --}}
                                        @if($mediaMsg)
                                        <span x-show="flashingPlatforms.includes('{{ $account->platform }}')" x-cloak
                                              class="animate-pulse-fast text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded-full whitespace-nowrap border border-red-200 shadow-sm">
                                            {{ $mediaMsg }}
                                        </span>
                                        @endif
                                        {{-- Media status indicator (shown after pre-upload) --}}
                                        <span x-show="platformStatus['{{ $account->platform }}'] === 'uploaded'" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full whitespace-nowrap">
                                            Media ready
                                        </span>
                                        <span x-show="platformStatus['{{ $account->platform }}'] === 'pending'" class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full whitespace-nowrap">
                                            Uploading…
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('accounts') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        @else
                            <div class="text-center py-6 bg-gray-50 rounded-xl">
                                <p class="text-gray-500 text-sm font-medium mb-2">No accounts connected</p>
                                <a href="{{ route('accounts.create', $client) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                                    + Connect an account
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Step 5: Preview --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover" x-show="selectedPlatforms.length > 0">
                        <h3 class="font-semibold text-gray-900 mb-3">Preview</h3>
                        <p class="text-xs text-gray-400 mb-3">See how your post looks on each platform.</p>
                        <template x-for="p in selectedPlatforms" :key="p.key">
                            <div class="border border-gray-100 rounded-xl p-3 mb-2 last:mb-0 hover:border-gray-200 transition">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <div class="w-5 h-5 rounded flex items-center justify-center text-white text-[7px] font-bold shrink-0"
                                         x-bind:style="'background:' + p.color">
                                        <span x-text="p.icon"></span>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700" x-text="p.label"></span>
                                    <span class="ml-auto text-[10px] font-medium"
                                          x-bind:class="content.length > (p.limit || 99999) ? 'text-red-600' : 'text-gray-400'">
                                        <span x-text="content.length"></span><template x-if="p.limit">/<span x-text="p.limit"></span></template>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-wrap" x-text="displayContent"></p>
                                <template x-if="uploadedFiles.length">
                                    <div class="mt-2 flex gap-2 overflow-x-auto">
                                        <template x-for="(f, fi) in uploadedFiles.slice(0, 4)" :key="fi">
                                            <template x-if="f.type?.startsWith('image/')">
                                                <img x-bind:src="f.url" class="w-14 h-14 object-cover rounded-lg border border-gray-200 shrink-0">
                                            </template>
                                        </template>
                                        <template x-if="uploadedFiles.length > 4">
                                            <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 shrink-0">+<span x-text="uploadedFiles.length - 4"></span></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Step 6: Schedule + Submit --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
                        <h3 class="font-semibold text-gray-900 mb-3">Schedule &amp; Post</h3>
                        <p class="text-xs text-gray-400 mb-4">Choose when to publish or post immediately.</p>

                        <div class="space-y-3">
                            <button type="submit"
                                :disabled="!canSubmit"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-sm text-white uppercase tracking-wider shadow-lg shadow-indigo-200 disabled:opacity-40 disabled:cursor-not-allowed hover:shadow-xl hover:scale-[1.02] transition-all btn-scale"
                                :class="!canSubmit ? 'opacity-40 cursor-not-allowed' : ''">
                                <template x-if="uploading">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </template>
                                <span x-text="uploading ? 'Uploading media…' : (!canSubmit && incompatibleSelected ? 'Incompatible platform' : (!canSubmit && mediaRequiredPlatforms.length > 0 ? 'Add media first' : (selectedPlatforms.length === 0 ? 'Select a platform first' : '{{ $role === 'editor' ? 'Submit for Approval' : 'Post Now' }}')))"></span>
                            </button>

                            <button type="button" @click="showScheduler = !showScheduler"
                                :disabled="uploading"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-white border-2 border-gray-200 rounded-xl font-semibold text-sm text-gray-700 uppercase tracking-wider hover:border-indigo-300 hover:bg-indigo-50/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all btn-scale">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="showScheduler ? 'Cancel Scheduling' : 'Schedule for Later'"></span>
                            </button>

                            <div x-show="showScheduler" x-cloak x-transition:enter.duration.200 class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Pick a date &amp; time</label>
                                <input type="datetime-local" name="schedule_at"
                                    class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                                <p class="text-xs text-gray-400 mt-2">
                                    <strong>Note:</strong> Media is uploaded to each platform <em>immediately</em> when you schedule,
                                    not at publish time. The platform stores it as a draft until we tell them to publish.
                                    <br><br>
                                    This means:
                                </p>
                                <ul class="text-xs text-gray-500 mt-1 space-y-0.5 list-disc pl-4">
                                    <li>No large files sit on our server</li>
                                    <li>Publishing is instant (just flips a switch)</li>
                                    <li>Zero bandwidth cost for your hosting</li>
                                </ul>
                            </div>
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
        content: '',
        hashtags: [],
        hashInput: '',
        showScheduler: false,
        uploading: false,
        uploadingFiles: [],
        uploadedFiles: [],
        uploadedPaths: [],
        platforms: @json($platforms),
        selectedPlatforms: [],
        platformStatus: {},
        crcsrf: '{{ csrf_token() }}',
        pendingResumes: [],
        flashingPlatforms: [],

        init() {
            this.resumeStalledUploads();
        },

        get hasMedia() { return this.uploadedFiles.length > 0; },

        get uploadedFileTypes() {
            const types = { images: 0, videos: 0 };
            for (const f of this.uploadedFiles) {
                if (f.type?.startsWith('video/')) types.videos++;
                else if (f.type?.startsWith('image/')) types.images++;
            }
            return types;
        },

        get mediaRequiredPlatforms() {
            return this.selectedPlatforms.filter(p => p.media_required && !this.hasMedia);
        },

        get canSubmit() {
            return !this.uploading
                && this.selectedPlatforms.length > 0
                && this.mediaRequiredPlatforms.length === 0
                && !this.incompatibleSelected;
        },

        get incompatibleSelected() {
            if (!this.hasMedia) return false;
            const types = this.uploadedFileTypes;
            return this.selectedPlatforms.some(p => {
                if (!p.media_required) return false;
                if (p.media_required === 'video') return types.videos === 0;
                if (p.media_required === 'image_or_video') return types.images === 0 && types.videos === 0;
                return false;
            });
        },

        get displayContent() {
            let text = this.content;
            if (this.hashtags.length) {
                const all = this.hashtags.map(t => '#' + t).join(' ');
                if (text.length + all.length + 2 <= 5000) {
                    text += '\n\n' + all;
                }
            }
            return text || '(empty)';
        },

        get selectedAccounts() {
            return this.selectedPlatforms.map(p => p.key);
        },

        addHashtag() {
            const tag = this.hashInput.replace(/^#/, '').trim();
            if (tag && !this.hashtags.includes(tag) && this.hashtags.length < 30) {
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
                const labelEl = cb.closest('label');
                if (!labelEl) return;
                // Find platform key from the label context
                for (const key in this.platforms) {
                    if (labelEl.textContent.includes(this.platforms[key].label) || labelEl.textContent.includes(key)) {
                        this.toggle(key, checked);
                        break;
                    }
                }
            });
            if (!checked) this.selectedPlatforms = [];
        },

        reconcilePlatforms() {
            const types = this.uploadedFileTypes;
            if (!this.hasMedia) return;
            const toFlash = [];
            for (const sp of [...this.selectedPlatforms]) {
                if (!sp.media_required) continue;
                let compatible = true;
                if (sp.media_required === 'video') compatible = types.videos > 0;
                else if (sp.media_required === 'image_or_video') compatible = types.images > 0 || types.videos > 0;
                if (!compatible) {
                    this.toggle(sp.key, false);
                    toFlash.push(sp.key);
                }
            }
            for (const key of toFlash) {
                if (!this.flashingPlatforms.includes(key)) {
                    this.flashingPlatforms.push(key);
                    setTimeout(() => {
                        this.flashingPlatforms = this.flashingPlatforms.filter(k => k !== key);
                    }, 4000);
                }
            }
        },

        // ─── Chunked Upload (Sequential with Retry + Resume) ───
        CHUNK_SIZE: 2 * 1024 * 1024,
        MAX_RETRIES: 3,
        STORAGE_KEY: 'cr_uploads',

        handleChunkedDrop(event) {
            if (event.dataTransfer.files.length) {
                this.uploadChunkedFiles(event.dataTransfer.files);
            }
        },

        handleChunkedFiles(event) {
            if (event.target.files.length) {
                this.uploadChunkedFiles(event.target.files);
            }
        },

        loadUploadState(fileId) {
            try {
                const all = JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '{}');
                return all[fileId] || null;
            } catch { return null; }
        },

        saveUploadState(fileId, state) {
            try {
                const all = JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '{}');
                all[fileId] = { ...state, lastActive: Date.now() };
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(all));
            } catch {}
        },

        clearUploadState(fileId) {
            try {
                const all = JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '{}');
                delete all[fileId];
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(all));
            } catch {}
        },

        async resumeStalledUploads() {
            try {
                const all = JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '{}');
                for (const [fileId, state] of Object.entries(all)) {
                    if (state.totalChunks === (state.completed?.length || 0)) {
                        this.clearUploadState(fileId);
                        continue;
                    }
                    const res = await fetch('{{ route("upload.chunks.status", ["fileId" => "__FILE_ID__"]) }}'.replace('__FILE_ID__', encodeURIComponent(fileId)));
                    if (!res.ok) { this.clearUploadState(fileId); continue; }
                    const data = await res.json();
                    if (!data.exists) {
                        this.clearUploadState(fileId);
                        this.pendingResumes = this.pendingResumes.filter(p => p.fileId !== fileId);
                        continue;
                    }
                    this.pendingResumes.push({
                        fileId,
                        name: state.name,
                        totalChunks: state.totalChunks,
                        serverChunks: new Set(data.chunks),
                    });
                }
            } catch {}
        },

        async uploadChunkedFiles(files) {
            for (const file of files) {
                await this.uploadSingleFile(file);
            }
            this.uploading = this.uploadingFiles.some(f => f.status === 'uploading');
        },

        async uploadSingleFile(file) {
            const CHUNK_SIZE = this.CHUNK_SIZE;
            const MAX_RETRIES = this.MAX_RETRIES;

            const fileId = Date.now() + '-' + Math.random().toString(36).slice(2, 8);
            const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

            // Check if this file matches a stalled upload (same name + size)
            let resumeFrom = new Set();
            const stalled = this.pendingResumes.find(p => p.name === file.name && p.totalChunks === totalChunks);
            if (stalled) {
                resumeFrom = stalled.serverChunks;
                this.pendingResumes = this.pendingResumes.filter(p => p.fileId !== stalled.fileId);
            }

            // Push entry and save its reactive index for proper Alpine reactivity
            const uploadEntry = {
                id: fileId,
                name: file.name,
                size: (file.size / 1024 / 1024).toFixed(1) + ' MB',
                progress: resumeFrom.size > 0 ? Math.round((resumeFrom.size / totalChunks) * 100) : 0,
                status: 'uploading',
                message: resumeFrom.size > 0 ? 'Resuming…' : 'Starting…',
            };
            const entryIdx = this.uploadingFiles.length;
            this.uploadingFiles.push(uploadEntry);
            this.uploading = true;

            // Helper: update entry through the reactive array index
            const setEntry = (key, value) => { this.uploadingFiles[entryIdx][key] = value; };

            const completed = new Set(resumeFrom);
            const stateKey = fileId;

            try {
                for (let i = 0; i < totalChunks; i++) {
                    if (completed.has(i)) continue;

                    const start = i * CHUNK_SIZE;
                    const end = Math.min(start + CHUNK_SIZE, file.size);
                    const chunk = file.slice(start, end);

                    const data = await new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = (e) => resolve(e.target.result.split(',')[1]);
                        reader.readAsDataURL(chunk);
                    });

                    let lastErr = null;
                    for (let attempt = 0; attempt < MAX_RETRIES; attempt++) {
                        try {
                            const formData = new FormData();
                            formData.append('_token', this.crcsrf);
                            formData.append('file_id', fileId);
                            formData.append('index', i);
                            formData.append('total', totalChunks);
                            formData.append('data', data);
                            formData.append('name', file.name);

                            await new Promise((resolve, reject) => {
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route("upload.chunk") }}');
                                xhr.onload = () => {
                                    if (xhr.status === 200) resolve(xhr.responseText);
                                    else reject(new Error(xhr.responseText));
                                };
                                xhr.onerror = () => reject(new Error('Network error'));
                                xhr.send(formData);
                            });

                            lastErr = null;
                            break;
                        } catch (err) {
                            lastErr = err;
                            if (attempt < MAX_RETRIES - 1) {
                                await new Promise(r => setTimeout(r, 1000 * (attempt + 1)));
                            }
                        }
                    }

                    if (lastErr) throw lastErr;

                    completed.add(i);

                    const pct = Math.round(((completed.size) / totalChunks) * 100);
                    setEntry('progress', pct);
                    setEntry('message', `Chunk ${completed.size} of ${totalChunks} — ${pct}%`);

                    if (completed.size % 5 === 0 || completed.size === totalChunks) {
                        this.saveUploadState(stateKey, {
                            name: file.name,
                            totalChunks,
                            completed: [...completed],
                            size: file.size,
                            type: file.type,
                        });
                    }
                }

                setEntry('status', 'done');
                setEntry('progress', 100);
                setEntry('message', 'Complete');
                this.clearUploadState(stateKey);

                const safeName = fileId + '-' + file.name.replace(/[^a-zA-Z0-9._-]/g, '_');
                const relativePath = 'uploads/' + safeName;

                this.uploadedFiles.push({
                    name: file.name,
                    size: (file.size / 1024 / 1024).toFixed(1) + ' MB',
                    url: URL.createObjectURL(file),
                    type: file.type,
                });
                this.uploadedPaths.push(relativePath);
                this.reconcilePlatforms();

            } catch (err) {
                setEntry('status', 'error');
                setEntry('message', 'Upload failed: ' + (err.message || 'Unknown error'));
                this.saveUploadState(stateKey, {
                    name: file.name,
                    totalChunks,
                    completed: [...completed],
                    size: file.size,
                    type: file.type,
                });
            }
        },

        removeFile(index) {
            if (this.uploadedFiles[index]) {
                URL.revokeObjectURL(this.uploadedFiles[index].url);
            }
            this.uploadedFiles.splice(index, 1);
            this.uploadedPaths.splice(index, 1);
            this.reconcilePlatforms();
        },

        // ─── Submit ───
        async submitPost(e) {
            if (this.uploading) {
                crToast('warning', 'Please wait for all uploads to finish.');
                return;
            }
            if (this.selectedPlatforms.length === 0) {
                crToast('warning', 'Select at least one platform.');
                return;
            }
            // Let the normal form submission go — the form has all hidden inputs
            e.target.submit();
        },
    }));
});
</script>
@endpush
</x-app-layout>
