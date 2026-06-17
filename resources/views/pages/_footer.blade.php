<footer class="bg-white/80 border-t border-gray-100/80 mt-12">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'Social Scheduler') }}. All rights reserved.
            </p>
            <div class="flex items-center gap-6">
                <a href="{{ route('pages.privacy') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">Privacy Policy</a>
                <a href="{{ route('pages.data-deletion') }}" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">Data Deletion</a>
            </div>
        </div>
    </div>
</footer>