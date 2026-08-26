<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Owners') }}
            </h2>
            @can('create owners')
                <a href="{{ route('owners.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                    Add Owner
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12" x-data="{ selected: [], allIds: @json($owners->pluck('id')) }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('owners.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search owners..."
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('owners.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    @endif
                </form>

                    <x-export-menu route="owners.export" />
                </div>

                @can('delete owners')
                    <form method="POST" action="{{ route('owners.bulk-destroy') }}"
                        @submit="if (! confirm(`Delete ${selected.length} selected owner(s)?`)) $event.preventDefault()"
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
                            @can('delete owners')
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                                        :checked="allIds.length > 0 && selected.length === allIds.length"
                                        @change="selected = $event.target.checked ? [...allIds] : []">
                                </th>
                            @endcan
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Properties</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($owners as $owner)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $owners->firstItem() + $loop->index }}</td>
                                @can('delete owners')
                                    <td class="px-6 py-4">
                                        <input type="checkbox" value="{{ $owner->id }}" x-model="selected"
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endcan
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $owner->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $owner->company_name ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $owner->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $owner->phone ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $owner->properties_count }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <x-row-actions>
                                        @can('edit owners')
                                            <x-dropdown-link href="{{ route('owners.edit', $owner) }}">Edit</x-dropdown-link>
                                        @endcan
                                        @can('delete owners')
                                            <form method="POST" action="{{ route('owners.destroy', $owner) }}" onsubmit="return confirm('Delete this owner?');">
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
                                        No owners match "{{ $search }}".
                                    @else
                                        No owners yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $owners->links() }}
        </div>
    </div>
</x-app-layout>
