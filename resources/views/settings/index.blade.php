@php
    $timezones = [
        'UTC' => 'UTC',
        'America/New_York' => 'Eastern Time (US)',
        'America/Chicago' => 'Central Time (US)',
        'America/Denver' => 'Mountain Time (US)',
        'America/Los_Angeles' => 'Pacific Time (US)',
        'Europe/London' => 'London',
        'Europe/Berlin' => 'Berlin',
        'Africa/Lagos' => 'Lagos',
        'Africa/Accra' => 'Accra',
        'Asia/Dubai' => 'Dubai',
        'Asia/Kolkata' => 'Kolkata',
        'Asia/Shanghai' => 'Shanghai',
        'Asia/Tokyo' => 'Tokyo',
        'Australia/Sydney' => 'Sydney',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Settings') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                General application settings.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="site_name" value="Site Name" />
                        <x-text-input id="site_name" name="site_name" type="text" class="mt-1 block w-full" value="{{ old('site_name', $settings['site_name']) }}" required />
                        <x-input-error :messages="$errors->get('site_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="support_email" value="Support Email" />
                        <x-text-input id="support_email" name="support_email" type="email" class="mt-1 block w-full" value="{{ old('support_email', $settings['support_email']) }}" required />
                        <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="timezone" value="Timezone" />
                        <select id="timezone" name="timezone" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach ($timezones as $value => $label)
                                <option value="{{ $value }}" @selected(old('timezone', $settings['timezone']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
