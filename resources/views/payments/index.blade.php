<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Payments') }}
            </h2>
            @can('create payments')
                <a href="{{ route('payments.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                    Add Payment
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12" x-data="{ selected: [], allIds: @json($payments->pluck('id')) }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('payments.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search payments..."
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                        Search
                    </button>
                    @if ($search)
                        <a href="{{ route('payments.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    @endif
                </form>

                    <x-export-menu route="payments.export" />
                </div>

                @can('delete payments')
                    <form method="POST" action="{{ route('payments.bulk-destroy') }}"
                        @submit="if (! confirm(`Delete ${selected.length} selected payment(s)?`)) $event.preventDefault()"
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
                            @can('delete payments')
                                <th class="px-6 py-3 w-10">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                                        :checked="allIds.length > 0 && selected.length === allIds.length"
                                        @change="selected = $event.target.checked ? [...allIds] : []">
                                </th>
                            @endcan
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payments->firstItem() + $loop->index }}</td>
                                @can('delete payments')
                                    <td class="px-6 py-4">
                                        <input type="checkbox" value="{{ $payment->id }}" x-model="selected"
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                @endcan
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $payment->rental->tenant->full_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->rental->room->property->property_name }} — {{ $payment->rental->room->room_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">GH₵ {{ number_format($payment->amount_paid, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_method }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $payment->status === 'completed',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $payment->status === 'pending',
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $payment->status === 'failed',
                                    ])>
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <x-row-actions>
                                        @can('edit payments')
                                            <x-dropdown-link href="{{ route('payments.edit', $payment) }}">Edit</x-dropdown-link>
                                        @endcan
                                        @can('delete payments')
                                            <form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('Delete this payment?');">
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
                                <td colspan="9" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($search)
                                        No payments match "{{ $search }}".
                                    @else
                                        No payments yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $payments->links() }}
        </div>
    </div>
</x-app-layout>
