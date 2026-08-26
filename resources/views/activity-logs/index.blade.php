<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Activity Logs') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                A record of who did what across the system.
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search activity or user..."
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_from" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                    Filter
                </button>
                @if ($search || $dateFrom || $dateTo)
                    <a href="{{ route('activity-logs.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                @endif
                <div class="ml-auto">
                    <x-export-menu route="activity-logs.export" />
                </div>
                <x-input-error :messages="$errors->get('date_to')" class="basis-full" />
            </form>

            @include('activity-logs._table', ['activities' => $activities, 'showUser' => true])

            {{ $activities->links() }}
        </div>
    </div>
</x-app-layout>
