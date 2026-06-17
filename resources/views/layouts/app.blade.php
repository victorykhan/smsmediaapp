<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Social Scheduler') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
            @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
            @keyframes pulse-fast { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.6; transform: scale(1.05); } }
            @keyframes slide-down { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes slide-up { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
            .animate-slide-down { animation: slide-down 0.2s ease-out; }
            .animate-slide-up { animation: slide-up 0.3s ease-out; }
            .animate-pulse-fast { animation: pulse-fast 0.6s ease-in-out infinite; }
            [x-cloak] { display: none !important; }
            .shimmer { background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent); background-size: 200% 100%; animation: shimmer 2s infinite; }
            .nav-blur { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
            .btn-scale { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
            .btn-scale:hover { transform: scale(1.04); }
            .btn-scale:active { transform: scale(0.96); }
            .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            .card-hover:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(0,0,0,0.08); }
            .gradient-text { background: linear-gradient(135deg, #6366f1, #8b5cf6, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 min-h-screen">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white/80 border-b border-gray-100/80 nav-blur sticky top-0 z-40">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>

        <x-notification-toast />
    </body>

    @if(session('success'))
        <script>document.addEventListener('DOMContentLoaded', () => notify('success', '{{ addslashes(session('success')) }}'));</script>
    @endif
    @if(session('error'))
        <script>document.addEventListener('DOMContentLoaded', () => notify('error', '{{ addslashes(session('error')) }}'));</script>
    @endif

    @stack('scripts')
</html>
