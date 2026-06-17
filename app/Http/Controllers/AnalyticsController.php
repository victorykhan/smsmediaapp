<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Post;

class AnalyticsController extends Controller
{
    public function index(Client $client)
    {
        $platforms = config('platforms');

        $postsByPlatform = Post::forClient($client)
            ->where('status', 'published')
            ->with('versions')
            ->get()
            ->flatMap(fn($p) => $p->versions)
            ->groupBy('platform')
            ->map(fn($v, $key) => [
                'label' => $platforms[$key]['label'] ?? $key,
                'color' => $platforms[$key]['color'] ?? '#666',
                'icon' => $platforms[$key]['icon'] ?? '?',
                'count' => $v->count(),
            ])
            ->sortByDesc('count');

        $totalPublished = Post::forClient($client)->where('status', 'published')->count();
        $totalScheduled = Post::forClient($client)->where('status', 'scheduled')->count();
        $totalFailed = Post::forClient($client)->where('status', 'failed')->count();
        $totalPending = Post::forClient($client)->where('status', 'pending_approval')->count();

        // Last 30 days chart data
        $dailyData = Post::forClient($client)
            ->where('status', 'published')
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->groupBy(fn($p) => $p->created_at->format('Y-m-d'))
            ->map(fn($posts, $date) => [
                'date' => $date,
                'count' => $posts->count(),
            ])
            ->values();

        return view('analytics.index', compact(
            'client', 'platforms', 'postsByPlatform', 'totalPublished',
            'totalScheduled', 'totalFailed', 'totalPending', 'dailyData'
        ));
    }
}
