<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Maintenance Requests') }}
            </h2>
            @can('create maintenance requests')
                <a href="{{ route('maintenance-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                    Add Request
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12" x-data="{ selected: [], allIds: @json($maintenanceRequests->pluck('id')) }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('maintenance-requests.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search requests..."
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('maintenance-requests.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    @endif
                </form>

                    <x-export-menu route="maintenance-requests.export" />
                </div>

                @can('delete maintenance requests')
                    <form method="POST" action="{{ route('maintenance-requests.bulk-destroy') }}"
                        @submit="if (! confirm(`Delete ${selected.length} selected request(s)?`)) $event.preventDefault()"
                        x-show="selected.length > 0" x-cloak class="flex items-center gap-2">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <span class="text-sm text-gray-500 dark:text-gray-400" x-text="`${selected.length} selected`"></span>
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                            Delete Selected
                        </button>
                    </form>
                @endcan
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-visible shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-12">#</th>
                            @can('delete maintenance requests')
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                                        :checked="allIds.length > 0 && selected.length === allIds.length"
                                        @change="selected = $event.target.checked ? [...allIds] : []">
                                </th>
                            @endcan
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Property</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assigned To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($maintenanceRequests as $request)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $maintenanceRequests->firstItem() + $loop->index }}</td>
                                @can('delete maintenance requests')
                                    <td class="px-6 py-4">
                                        <input type="checkbox" value="{{ $request->id }}" x-model="selected"
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endcan
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $request->property->property_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $request->room?->room_number ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $request->assignedTo?->name ?? 'Unassigned' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $request->cost !== null ? '$'.number_format($request->cost, 2) : '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $request->status === 'scheduled',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $request->status === 'in_progress',
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $request->status === 'completed',
                                    ])>
                                        {{ Str::headline($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <x-row-actions>
                                        @can('edit maintenance requests')
                                            <x-dropdown-link href="{{ route('maintenance-requests.edit', $request) }}">Edit</x-dropdown-link>
                                        @endcan
                                        @can('delete maintenance requests')
                                            <form method="POST" action="{{ route('maintenance-requests.destroy', $request) }}" onsubmit="return confirm('Delete this request?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-dropdown-button danger>Delete</x-dropdown-button>
                                            </form>
                                        @endcan
                                    </x-row-actions>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($search)
                                        No maintenance requests match "{{ $search }}".
                                    @else
                                        No maintenance requests yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $maintenanceRequests->links() }}
        </div>
    </div>
</x-app-layout>
