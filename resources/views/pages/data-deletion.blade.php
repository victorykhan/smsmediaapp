<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Social Scheduler') }} — Data Deletion Request</title>
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
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Data Deletion Request</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: June 17, 2026</p>

        <div class="prose prose-gray max-w-none space-y-6">
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">How to Request Deletion of Your Data</h2>
                <p class="text-gray-600 leading-relaxed">
                    We respect your privacy and provide multiple ways to request the deletion of your personal data from our Service.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Method 1: Self-Service Deletion (Recommended)</h2>
                <ol class="list-decimal pl-6 text-gray-600 space-y-2">
                    <li>Log in to your account at <a href="https://sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">https://sms.vicips.ca</a>.</li>
                    <li>Navigate to your Profile settings.</li>
                    <li>Select "Delete Account" or "Request Account Deletion."</li>
                    <li>Confirm your decision. All your data, including connected social accounts, scheduled posts, and personal information, will be permanently deleted.</li>
                </ol>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Method 2: Email Request</h2>
                <p class="text-gray-600 leading-relaxed">
                    Send an email to <a href="mailto:dev@sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">dev@sms.vicips.ca</a> with the subject line <strong>"Data Deletion Request"</strong>.
                    Please include the email address associated with your account so we can verify your identity.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">What Data Will Be Deleted</h2>
                <p class="text-gray-600 leading-relaxed mb-3">Upon receiving your deletion request, we will permanently delete:</p>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li>Your account registration details (name, email, password hash).</li>
                    <li>All connected social media account credentials and OAuth tokens.</li>
                    <li>Scheduled and past posts, including content and media references.</li>
                    <li>Analytics data associated with your account.</li>
                    <li>Team and client relationships.</li>
                    <li>Notifications and activity logs.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Data That May Be Retained</h2>
                <p class="text-gray-600 leading-relaxed">
                    Certain information may be retained for legal compliance or legitimate business purposes:
                </p>
                <ul class="list-disc pl-6 text-gray-600 space-y-1">
                    <li>Records of financial transactions (if applicable) as required by tax law.</li>
                    <li>Anonymized or aggregated data that cannot identify you personally.</li>
                    <li>Information required to comply with legal obligations or resolve disputes.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Processing Timeline</h2>
                <p class="text-gray-600 leading-relaxed">
                    We will process your deletion request within <strong>30 days</strong> of verification. You will receive a confirmation email once the deletion is complete. In most cases, deletion is processed within 48 hours.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Social Media Platforms</h2>
                <p class="text-gray-600 leading-relaxed">
                    Deleting your account on our Service does <strong>not</strong> automatically delete content published to your connected social media accounts. To remove content published through our Service on third-party platforms, you must do so directly on each platform (Facebook, Instagram, LinkedIn, etc.).
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-3">Contact</h2>
                <p class="text-gray-600 leading-relaxed">
                    For any questions about data deletion, contact:<br>
                    Email: <a href="mailto:dev@sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">dev@sms.vicips.ca</a><br>
                    Website: <a href="https://sms.vicips.ca" class="text-indigo-600 hover:text-indigo-800 underline">https://sms.vicips.ca</a>
                </p>
            </section>
        </div>
    </div>

    @include('pages._footer')
</body>
</html>