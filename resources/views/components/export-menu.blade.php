@props(['route', 'params' => []])

<x-dropdown align="left" width="48">
    <x-slot name="trigger">
        <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export
        </button>
    </x-slot>

    <x-slot name="content">
        <a href="{{ route($route, array_merge(request()->query(), $params, ['format' => 'csv'])) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
            Export as CSV
        </a>
        <a href="{{ route($route, array_merge(request()->query(), $params, ['format' => 'pdf'])) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
            Export as PDF
        </a>
    </x-slot>
</x-dropdown>
