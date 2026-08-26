<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AK Properties') }}</title>
        <meta name="description" content="AK Properties is a property management system for tracking owners, properties, tenants, rentals, payments, and maintenance in one place.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white dark:bg-gray-900">
        @php
            $features = [
                [
                    'label' => 'Properties & Rooms',
                    'description' => 'Track every property and unit, from address and type down to room-level rent and status.',
                    'color' => 'blue',
                    'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
                ],
                [
                    'label' => 'Tenants & Leases',
                    'description' => 'Keep tenant contacts, active rentals, and lease dates organized and easy to search.',
                    'color' => 'emerald',
                    'icon' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                [
                    'label' => 'Payments & Expenses',
                    'description' => 'Record rent payments and property expenses, and see where the money is going.',
                    'color' => 'amber',
                    'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                ],
                [
                    'label' => 'Maintenance & Complaints',
                    'description' => 'Log tenant complaints and maintenance requests, and follow them through to resolved.',
                    'color' => 'red',
                    'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z',
                ],
                [
                    'label' => 'Reports & Activity',
                    'description' => 'Generate reports and see a full audit trail of who changed what, and when.',
                    'color' => 'indigo',
                    'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                ],
                [
                    'label' => 'Roles & Permissions',
                    'description' => 'Give each team member exactly the access they need, from support staff to super admin.',
                    'color' => 'violet',
                    'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                ],
            ];

            $colorClasses = [
                'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                'red' => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
                'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
            ];
        @endphp

        <!-- Hero -->
        <div class="sticky top-0 z-0 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800">
            <nav class="max-w-7xl mx-auto px-6 lg:px-8 py-6 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5">
                    <x-application-logo class="h-8 w-auto fill-current text-white" />
                    <span class="text-white font-semibold tracking-wide">{{ config('app.name', 'AK Properties') }}</span>
                </a>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white text-slate-900 rounded-md text-sm font-medium hover:bg-gray-100">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 hover:text-white">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-white text-slate-900 rounded-md text-sm font-medium hover:bg-gray-100">
                            Get Started
                        </a>
                    @endauth
                </div>
            </nav>

            <div class="max-w-7xl mx-auto px-6 lg:px-8 pt-12 pb-20 sm:pt-16 sm:pb-28 text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-300">
                    Property Management, Simplified
                </span>
                <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-white tracking-tight text-balance">
                    Every property, tenant,<br class="hidden sm:block"> and payment — in one place.
                </h1>
                <p class="mt-5 max-w-2xl mx-auto text-lg text-slate-400">
                    AK Properties helps you manage owners, properties, tenants, rentals, payments, and maintenance requests without juggling spreadsheets.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-500">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-500">
                            Log in to your account
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-white/10 text-white rounded-md text-sm font-semibold hover:bg-white/20">
                            Create an account
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="relative z-10 bg-gray-50 dark:bg-gray-950 rounded-t-3xl shadow-[0_-25px_50px_-12px_rgba(0,0,0,0.25)]">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 sm:py-20">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">Everything you need to run your portfolio</h2>
                    <p class="mt-3 text-gray-500 dark:text-gray-400">One dashboard for the day-to-day of managing real estate.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($features as $feature)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md hover:-translate-y-0.5 transition">
                            <div class="w-11 h-11 rounded-lg flex items-center justify-center {{ $colorClasses[$feature['color']] }}">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                                </svg>
                            </div>
                            <h3 class="mt-4 font-semibold text-gray-900 dark:text-gray-100">{{ $feature['label'] }}</h3>
                            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $feature['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- How it works -->
        <div class="relative z-10 bg-white dark:bg-gray-950">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 sm:py-20">
                <div class="text-center max-w-2xl mx-auto">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                        Get Started in Minutes
                    </span>
                    <h2 class="mt-4 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-gray-100">Up and running in three steps</h2>
                    <p class="mt-3 text-gray-500 dark:text-gray-400">No spreadsheets, no setup headaches — just add your portfolio and go.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-6">
                    @foreach ([
                        ['step' => '01', 'title' => 'Add your properties', 'description' => 'Register each property and its rooms, with base rent and amenities like AC, Wi-Fi, or a generator.'],
                        ['step' => '02', 'title' => 'Move in your tenants', 'description' => 'Link a tenant to a room with a lease, and they get their own portal to see rent, bills, and lease status.'],
                        ['step' => '03', 'title' => 'Track everything', 'description' => 'Payments, expenses, maintenance, and complaints all flow into one dashboard and exportable reports.'],
                    ] as $item)
                        <div class="relative text-center sm:text-left">
                            <span class="text-5xl font-bold text-gray-100 dark:text-gray-800">{{ $item['step'] }}</span>
                            <h3 class="mt-2 font-semibold text-gray-900 dark:text-gray-100">{{ $item['title'] }}</h3>
                            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Closing CTA -->
        <div class="relative z-10 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 sm:py-20">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-slate-900 px-6 py-14 sm:px-16 sm:py-16 text-center shadow-xl">
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 24px 24px;"></div>
                    <div class="relative">
                        <h2 class="text-2xl sm:text-3xl font-bold text-white">Ready to get your portfolio organized?</h2>
                        <p class="mt-3 max-w-xl mx-auto text-indigo-100">
                            Create an account and start adding your properties today — it only takes a few minutes.
                        </p>
                        <div class="mt-8 flex items-center justify-center gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-slate-900 rounded-md text-sm font-semibold hover:bg-gray-100">
                                    Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-slate-900 rounded-md text-sm font-semibold hover:bg-gray-100">
                                    Create your account
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 bg-white/10 text-white rounded-md text-sm font-semibold hover:bg-white/20">
                                    Log in
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="relative z-10 bg-white dark:bg-gray-950 border-t border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-application-logo class="h-5 w-auto fill-current text-gray-400 dark:text-gray-600" />
                    <span class="text-sm text-gray-500 dark:text-gray-400">&copy; {{ now()->year }} {{ config('app.name', 'AK Properties') }}. All rights reserved.</span>
                </div>
                @guest
                    <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">Log in &rarr;</a>
                @endguest
            </div>
        </footer>
    </body>
</html>
