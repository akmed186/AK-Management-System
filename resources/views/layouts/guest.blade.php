<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AK Properties') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-gray-50 dark:bg-gray-900">
            <!-- Branding panel -->
            <div class="hidden lg:flex lg:w-1/2 flex-col justify-between bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 px-12 py-12">
                <a href="/" class="text-white font-semibold text-xl tracking-wide">
                    {{ config('app.name', 'AK Properties') }}
                </a>

                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-300">
                        Property Management, Simplified
                    </span>
                    <h1 class="mt-5 text-3xl font-bold text-white tracking-tight text-balance">
                        Every property, tenant, and payment — in one place.
                    </h1>
                    <p class="mt-4 text-slate-400 max-w-sm">
                        Sign in to manage your owners, properties, tenants, and finances from a single dashboard.
                    </p>
                </div>

                <p class="text-xs text-slate-500">&copy; {{ now()->year }} {{ config('app.name', 'AK Properties') }}. All rights reserved.</p>
            </div>

            <!-- Form panel -->
            <div class="flex-1 flex flex-col justify-center items-center px-6 py-12">
                <div class="w-full max-w-sm">
                    <div class="lg:hidden text-center mb-8">
                        <span class="text-gray-900 dark:text-white font-semibold text-xl tracking-wide">
                            {{ config('app.name', 'AK Properties') }}
                        </span>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-xl px-6 py-8 sm:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
