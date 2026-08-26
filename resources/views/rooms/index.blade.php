<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Rooms') }}
            </h2>
            @can('create rooms')
                <a href="{{ route('rooms.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                    Add Room
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12" x-data="{ selected: [], allIds: @json($rooms->pluck('id')) }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('rooms.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search rooms..."
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('rooms.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    @endif
                </form>

                    <x-export-menu route="rooms.export" />
                </div>

                @can('delete rooms')
                    <form method="POST" action="{{ route('rooms.bulk-destroy') }}"
                        @submit="if (! confirm(`Delete ${selected.length} selected room(s)?`)) $event.preventDefault()"
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
                            @can('delete rooms')
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                                        :checked="allIds.length > 0 && selected.length === allIds.length"
                                        @change="selected = $event.target.checked ? [...allIds] : []">
                                </th>
                            @endcan
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Property</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Floor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Base Rent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($rooms as $room)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $rooms->firstItem() + $loop->index }}</td>
                                @can('delete rooms')
                                    <td class="px-6 py-4">
                                        <input type="checkbox" value="{{ $room->id }}" x-model="selected"
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endcan
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $room->room_number }}
                                    @if ($room->amenities->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($room->amenities->take(3) as $amenity)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $amenity->name }}</span>
                                            @endforeach
                                            @if ($room->amenities->count() > 3)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">+{{ $room->amenities->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $room->property->property_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $room->floor ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">${{ number_format($room->base_rent_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $room->status === 'occupied',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $room->status === 'vacant',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' => $room->status === 'maintenance',
                                    ])>
                                        {{ ucfirst($room->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <x-row-actions>
                                    @can('edit rooms')
                                        <x-dropdown-link href="{{ route('rooms.edit', $room) }}">Edit</x-dropdown-link>
                                    @endcan
                                    @can('delete rooms')
                                        <form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?');">
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
                                        No rooms match "{{ $search }}".
                                    @else
                                        No rooms yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $rooms->links() }}
        </div>
    </div>
</x-app-layout>
