<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Client') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 card-hover">
                <form action="{{ route('clients.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Client Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                        @error('name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="text" name="website" id="website" value="{{ old('website') }}"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2" placeholder="example.com">
                        <p class="text-xs text-gray-400 mt-1">Full URL (https://...) or just domain name. Optional.</p>
                        @error('website') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="industry" class="block text-sm font-medium text-gray-700">Industry</label>
                        <input type="text" name="industry" id="industry" value="{{ old('industry') }}"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                        @error('industry') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                        <select name="timezone" id="timezone"
                            class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                            @foreach(timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                        @error('timezone') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-indigo-200 hover:shadow-xl hover:scale-[1.02] transition-all btn-scale">
                            Create Client
                        </button>
                        <a href="{{ route('clients.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
