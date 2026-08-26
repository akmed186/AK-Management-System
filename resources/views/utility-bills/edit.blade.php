<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Utility Bill') }}
        </h2>
            <a href="{{ route('utility-bills.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Utility Bills</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('utility-bills.update', $utilityBill) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="room_id" value="Room" />
                        <x-searchable-select
                            name="room_id"
                            id="room_id"
                            :options="$rooms->map(fn ($room) => ['value' => $room->id, 'label' => $room->property->property_name.' — '.$room->room_number])"
                            :selected="$utilityBill->room_id"
                            placeholder="Search rooms…"
                            empty="No matching rooms."
                            required
                        />
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="utility_type_id" value="Utility Type" />
                        <select id="utility_type_id" name="utility_type_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach ($utilityTypes as $utilityType)
                                <option value="{{ $utilityType->id }}" @selected(old('utility_type_id', $utilityBill->utility_type_id) == $utilityType->id)>{{ $utilityType->utility_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('utility_type_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="billing_month" value="Billing Month" />
                        <x-text-input id="billing_month" name="billing_month" type="date" class="mt-1 block w-full" value="{{ old('billing_month', $utilityBill->billing_month->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('billing_month')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="consumption_units" value="Consumption Units" />
                            <x-text-input id="consumption_units" name="consumption_units" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('consumption_units', $utilityBill->consumption_units) }}" required />
                            <x-input-error :messages="$errors->get('consumption_units')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="total_amount" value="Total Amount" />
                            <x-text-input id="total_amount" name="total_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('total_amount', $utilityBill->total_amount) }}" required />
                            <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="due_date" value="Due Date" />
                        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" value="{{ old('due_date', $utilityBill->due_date->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['unpaid', 'paid'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $utilityBill->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('utility-bills.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
