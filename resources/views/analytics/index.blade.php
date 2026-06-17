<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analytics — {{ $client->name }}</h2>
            <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-500 hover:text-gray-700 transition">&larr; Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Stat cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Published</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPublished }}</p>
                    <div class="mt-2 w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full" style="width: 100%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalScheduled }}</p>
                    <div class="mt-2 w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-400 to-blue-500 rounded-full" style="width: {{ $totalPublished ? min(100, ($totalScheduled / max($totalPublished,1)) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending Approval</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPending }}</p>
                    <div class="mt-2 w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 rounded-full" style="width: {{ $totalPublished ? min(100, ($totalPending / max($totalPublished,1)) * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalFailed }}</p>
                    <div class="mt-2 w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-red-400 to-red-500 rounded-full" style="width: {{ $totalPublished ? min(100, ($totalFailed / max($totalPublished,1)) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-200">
                <h3 class="font-semibold text-gray-900 mb-4">Last 30 Days</h3>
                <div class="relative" style="height: 250px;">
                    <canvas id="postsChart"></canvas>
                </div>
            </div>

            <!-- Platform breakdown -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-200">
                <h3 class="font-semibold text-gray-900 mb-4">By Platform</h3>
                @if($postsByPlatform->count())
                    <div class="space-y-3">
                        @foreach($postsByPlatform as $key => $p)
                            @php $pct = $totalPublished ? round(($p['count'] / $totalPublished) * 100) : 0; @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                     style="background: {{ $p['color'] }}">
                                    {{ $p['icon'] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-700">{{ $p['label'] }}</span>
                                        <span class="text-gray-500">{{ $p['count'] }} posts</span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             style="width: {{ $pct }}%; background: {{ $p['color'] }}"></div>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold text-gray-500 w-10 text-right">{{ $pct }}%</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm text-center py-8">No published posts yet. Start posting to see analytics!</p>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('postsChart');
            if (!ctx) return;
            const data = @json($dailyData);
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: 'Posts Published',
                        data: data.map(d => d.count),
                        borderColor: '#6366f1',
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 250);
                            g.addColorStop(0, 'rgba(99,102,241,0.2)');
                            g.addColorStop(1, 'rgba(99,102,241,0)');
                            return g;
                        },
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, maxTicksLimit: 10 }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 10 }, stepSize: 1 }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
