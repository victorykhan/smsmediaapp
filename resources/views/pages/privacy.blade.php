<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Social Scheduler') }} — Privacy Policy</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .nav-blur { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 min-h-screen">
    <nav class="bg-white/80 border-b border-gray-100/80 nav-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ url('/') }}" class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    {{ config('app.name', 'Social Scheduler') }}
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: June 17, 2026</p>

        <div class="prose prose-gray max-w-none space-y-6">
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">1. Introduction</h2>
                <p class="text-gray-600 leading-relaxed">
                    SMS Media Scheduler ("we," "our," or "us") operates the website <strong>sms.vicips.ca</strong> (the "Service").
                    This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our social media scheduling platform.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">2. Information We Collect</h2>
                <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">Personal Data</h3>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li><strong>Account Information:</strong> Name, email address, and password when you register.</li>
                    <li><strong>Profile Information:</strong> Avatar, display name, and preferences.</li>
                    <li><strong>Social Media Credentials:</strong> OAuth tokens and API keys for connected social media accounts (Facebook, Instagram, Threads, LinkedIn, Mastodon, Bluesky, X/Twitter, TikTok, Pinterest, YouTube). All credentials are encrypted at rest.</li>
                    <li><strong>Content:</strong> Posts, media, and scheduling data you create through the Service.</li>
                </ul>
                <h3 class="text-lg font-medium text-gray-800 mt-4 mb-2">Automatically Collected Data</h3>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li>Usage data (pages visited, features used).</li>
                    <li>Device and browser information.</li>
                    <li>IP address and approximate location.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">3. How We Use Your Information</h2>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li>To provide and maintain the Service.</li>
                    <li>To schedule and publish content to your connected social media accounts.</li>
                    <li>To send notifications about scheduled posts, approvals, and account activity.</li>
                    <li>To improve and personalize your experience.</li>
                    <li>To comply with legal obligations.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">4. Third-Party Services</h2>
                <p class="text-gray-600 leading-relaxed">
                    Our Service integrates with third-party social media platforms including but not limited to: Meta (Facebook, Instagram, Threads), LinkedIn, Mastodon, Bluesky, X Corp (Twitter/X), TikTok, Pinterest, and Google (YouTube).
                    When you connect an account, your content and credentials are shared with that platform according to its terms and privacy policy. We encourage you to review each platform's privacy policy.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">5. Data Storage and Security</h2>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li>All OAuth tokens and API credentials are encrypted using industry-standard encryption (AES-256) before storage.</li>
                    <li>Media files are stored temporarily for processing and are uploaded directly to social media platforms; we do not permanently store your media content beyond what is necessary for scheduling.</li>
                    <li>Your data is stored on secure servers managed by our hosting provider.</li>
                    <li>We implement appropriate technical and organizational measures to protect your data.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">6. Data Retention</h2>
                <p class="text-gray-600 leading-relaxed">
                    We retain your personal data only as long as necessary to provide the Service or as required by law. You may request deletion of your account and associated data at any time.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">7. Your Rights</h2>
                <p class="text-gray-600 leading-relaxed mb-3">Depending on your jurisdiction, you may have the right to:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li>Access the personal data we hold about you.</li>
                    <li>Request correction of inaccurate data.</li>
                    <li>Request deletion of your data ("right to be forgotten").</li>
                    <li>Object to or restrict processing of your data.</li>
                    <li>Data portability.</li>
                    <li>Withdraw consent at any time.</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-3">
                    To exercise any of these rights, contact us at <a href="mailto:dev@sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">dev@sms.vicips.ca</a>.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">8. Cookies</h2>
                <p class="text-gray-600 leading-relaxed">
                    We use essential cookies for authentication and session management. We do not use tracking cookies or third-party analytics cookies. You can control cookie preferences through your browser settings.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">9. Changes to This Privacy Policy</h2>
                <p class="text-gray-600 leading-relaxed">
                    We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">10. Contact Us</h2>
                <p class="text-gray-600 leading-relaxed">
                    If you have questions about this Privacy Policy, please contact us at:<br>
                    Email: <a href="mailto:dev@sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">dev@sms.vicips.ca</a><br>
                    Website: <a href="https://sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">https://sms.vicips.ca</a>
                </p>
            </section>
        </div>
    </div>

    @include('pages._footer')
</body>
</html>