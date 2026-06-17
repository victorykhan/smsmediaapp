<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Setup') }}: {{ $client->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 card-hover" x-data="{ step: {{ $client->onboarding_step ?: 1 }} }">
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        @foreach(['Profile', 'Team', 'Connect', 'Verify'] as $i => $label)
                            <div class="flex items-center">
                                <div class="flex items-center justify-center" :class="step > {{ $i + 1 }} ? 'text-emerald-600' : (step === {{ $i + 1 }} ? 'text-indigo-600' : 'text-gray-400')">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm border-2 transition-all duration-300"
                                        :class="step > {{ $i + 1 }} ? 'bg-emerald-50 border-emerald-500 shadow-sm' : (step === {{ $i + 1 }} ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white border-transparent shadow-lg shadow-indigo-200' : 'bg-white border-gray-200')">
                                        <template x-if="step > {{ $i + 1 }}">&#10003;</template>
                                        <template x-if="step <= {{ $i + 1 }}"><span x-text="{{ $i + 1 }}"></span></template>
                                    </div>
                                    <span class="ml-2 text-sm hidden sm:inline font-medium" :class="step === {{ $i + 1 }} ? 'font-bold text-gray-900' : 'text-gray-500'">{{ $label }}</span>
                                </div>
                                @if($i < 3)
                                    <div class="w-12 sm:w-24 h-0.5 mx-2 rounded-full transition-all duration-300" :class="step > {{ $i + 1 }} ? 'bg-emerald-500' : 'bg-gray-200'"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <form action="{{ route('clients.onboarding.step', $client) }}" method="POST">
                    @csrf
                    <input type="hidden" name="step" x-model="step">
                    <input type="hidden" name="completed" value="1">

                    <!-- Step 1: Profile -->
                    <div x-show="step === 1" x-transition:enter.duration.300ms>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Client Profile</h3>
                        <p class="text-sm text-gray-500 mb-6">Your client profile was created. You can edit it anytime from settings.</p>
                        <div class="bg-gradient-to-r from-gray-50 to-indigo-50/30 rounded-xl p-5 space-y-2 text-sm border border-gray-100">
                            <p><span class="font-semibold text-gray-700">Name:</span> <span class="text-gray-900">{{ $client->name }}</span></p>
                            @if($client->website)<p><span class="font-semibold text-gray-700">Website:</span> <span class="text-gray-900">{{ $client->website }}</span></p>@endif
                            @if($client->industry)<p><span class="font-semibold text-gray-700">Industry:</span> <span class="text-gray-900">{{ $client->industry }}</span></p>@endif
                            <p><span class="font-semibold text-gray-700">Timezone:</span> <span class="text-gray-900">{{ $client->timezone }}</span></p>
                        </div>
                    </div>

                    <!-- Step 2: Team -->
                    <div x-show="step === 2" x-cloak x-transition:enter.duration.300ms>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Invite Your Team</h3>
                        <p class="text-sm text-gray-500 mb-6">Add team members to collaborate. Skip and do it later if you prefer.</p>
                        <div class="flex gap-2">
                            <input type="email" name="invite_email" placeholder="colleague@example.com"
                                class="flex-1 rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                            <select name="invite_role" class="rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                                <option value="admin">Admin</option>
                                <option value="editor" selected>Editor</option>
                                <option value="viewer">Viewer</option>
                            </select>
                            <button type="button" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg hover:scale-[1.02] transition-all btn-scale">Invite</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Invite more people anytime from the client settings page.</p>
                    </div>

                    <!-- Step 3: Connect -->
                    <div x-show="step === 3" x-cloak x-transition:enter.duration.300ms>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Connect Social Accounts</h3>
                        <p class="text-sm text-gray-500 mb-6">Connect your client's social media profiles. You can do this later from the dashboard.</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach(['YouTube', 'Facebook', 'Instagram', 'Threads', 'Bluesky', 'Mastodon', 'X', 'TikTok', 'Pinterest', 'LinkedIn'] as $platform)
                                <div class="group border border-gray-200 rounded-xl p-4 text-center hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-100/30 cursor-pointer transition-all duration-200 card-hover">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 group-hover:from-indigo-100 group-hover:to-purple-100 flex items-center justify-center mx-auto mb-1.5 group-hover:scale-110 transition-transform">
                                        <span class="text-xs font-bold text-gray-500 group-hover:text-indigo-600 transition-colors">{{ substr($platform, 0, 2) }}</span>
                                    </div>
                                    <p class="text-xs font-semibold text-gray-600 group-hover:text-gray-900 transition-colors">{{ $platform }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-4 text-center">Click any platform to connect — or skip and connect later.</p>
                    </div>

                    <!-- Step 4: Complete -->
                    <div x-show="step === 4" x-cloak x-transition:enter.duration.300ms>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">All Set!</h3>
                        <div class="text-center py-8">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-100 to-green-100 flex items-center justify-center mx-auto mb-5">
                                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $client->name }} is ready!</h4>
                            <p class="text-sm text-gray-500">Start creating and scheduling posts across all platforms.</p>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between mt-8 pt-6 border-t border-gray-100">
                        <button type="button" x-show="step > 1" @click="step--"
                            class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all btn-scale">
                            Back
                        </button>
                        <div x-show="step < 4" class="ml-auto">
                            <button type="button" @click="step++"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                                Continue
                            </button>
                        </div>
                        <div x-show="step === 4">
                            <button type="submit"
                                class="px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl shadow-lg shadow-emerald-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                                Finish Setup
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
