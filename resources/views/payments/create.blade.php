<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Payment') }}
        </h2>
            <a href="{{ route('payments.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Payments</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('payments.store') }}" class="space-y-6"
                    x-data="{ amountPaid: '{{ old('amount_paid') }}' }"
                    @select="if ($event.detail.monthly_rent !== undefined) amountPaid = $event.detail.monthly_rent"
                >
                    @csrf

                    <div>
                        <x-input-label for="rental_id" value="Rental" />
                        <x-searchable-select
                            name="rental_id"
                            id="rental_id"
                            :options="$rentals->map(fn ($rental) => ['value' => $rental->id, 'label' => $rental->tenant->full_name.' — '.$rental->room->property->property_name.' ('.$rental->room->room_number.')', 'monthly_rent' => $rental->monthly_rent])"
                            placeholder="Search by tenant or room…"
                            empty="No matching rentals."
                            required
                        />
                        <x-input-error :messages="$errors->get('rental_id')" class="mt-2" />
                        @if ($rentals->isEmpty())
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                No rentals yet — <a href="{{ route('rentals.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">add one first</a>.
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="amount_paid" value="Amount Paid" />
                            <x-text-input id="amount_paid" name="amount_paid" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model="amountPaid" required />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Filled in from the rental's monthly rent — adjust for partial or extra payments.</p>
                            <x-input-error :messages="$errors->get('amount_paid')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="payment_date" value="Payment Date" />
                            <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" value="{{ old('payment_date') }}" required />
                            <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="payment_method" value="Payment Method" />
                        <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['Bank Transfer', 'Credit Card', 'Cash'] as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['completed', 'pending', 'failed'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'completed') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="transaction_reference" value="Transaction Reference (optional)" />
                        <x-text-input id="transaction_reference" name="transaction_reference" type="text" class="mt-1 block w-full" value="{{ old('transaction_reference') }}" />
                        <x-input-error :messages="$errors->get('transaction_reference')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('payments.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
