<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Rental') }}
        </h2>
            <a href="{{ route('rentals.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Rentals</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('rentals.update', $rental) }}" class="space-y-6"
                    x-data="{
                        startDate: '{{ old('start_date', $rental->start_date->format('Y-m-d')) }}',
                        endDate: '{{ old('end_date', $rental->end_date?->format('Y-m-d') ?? '') }}',
                        months: '',
                        monthlyRent: '{{ old('monthly_rent', $rental->monthly_rent) }}',
                        get totalAmount() {
                            return (parseFloat(this.monthlyRent) || 0) * (parseFloat(this.months) || 0);
                        },
                        setEndDateFromMonths() {
                            if (! this.startDate || this.months === '') return;
                            const d = new Date(this.startDate + 'T00:00:00');
                            d.setMonth(d.getMonth() + Number(this.months));
                            d.setDate(d.getDate() - 1);
                            this.endDate = d.toISOString().slice(0, 10);
                        },
                        setMonthsFromEndDate() {
                            if (! this.startDate || ! this.endDate) return;
                            const start = new Date(this.startDate + 'T00:00:00');
                            const end = new Date(this.endDate + 'T00:00:00');
                            end.setDate(end.getDate() + 1);
                            const diff = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
                            this.months = Math.max(diff, 0);
                        },
                    }"
                    x-init="setMonthsFromEndDate()"
                >
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="tenant_id" value="Tenant" />
                        <x-searchable-select
                            name="tenant_id"
                            id="tenant_id"
                            :options="$tenants->map(fn ($tenant) => ['value' => $tenant->id, 'label' => $tenant->full_name])"
                            :selected="$rental->tenant_id"
                            placeholder="Search tenants…"
                            empty="No matching tenants."
                            required
                        />
                        <x-input-error :messages="$errors->get('tenant_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="room_id" value="Room" />
                        <x-searchable-select
                            name="room_id"
                            id="room_id"
                            :options="$rooms->map(fn ($room) => ['value' => $room->id, 'label' => $room->property->property_name.' — '.$room->room_number])"
                            :selected="$rental->room_id"
                            placeholder="Search rooms…"
                            empty="No matching rooms."
                            required
                        />
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="start_date" value="Start Date" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" x-model="startDate" @change="setEndDateFromMonths()" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_date" value="End Date" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" x-model="endDate" @change="setMonthsFromEndDate()" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="lease_months" value="Number of Months (optional)" />
                            <input id="lease_months" type="number" min="1" step="1" x-model="months" @input="setEndDateFromMonths()"
                                placeholder="e.g. 12"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Fills in the End Date for you. Leave blank to set dates manually.</p>
                        </div>
                        <div>
                            <x-input-label value="Total for This Lease" />
                            <div class="mt-1 flex items-center h-[42px] px-3 rounded-md bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300"
                                x-text="months !== '' && monthlyRent ? ('GH₵ ' + totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '—'"
                            ></div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Monthly rent × number of months — for reference, not saved separately.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="monthly_rent" value="Monthly Rent" />
                            <x-text-input id="monthly_rent" name="monthly_rent" type="number" step="0.01" min="0" class="mt-1 block w-full" x-model="monthlyRent" required />
                            <x-input-error :messages="$errors->get('monthly_rent')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="security_deposit" value="Security Deposit" />
                            <x-text-input id="security_deposit" name="security_deposit" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('security_deposit', $rental->security_deposit) }}" />
                            <x-input-error :messages="$errors->get('security_deposit')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="lease_status" value="Lease Status" />
                        <select id="lease_status" name="lease_status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['active', 'expired', 'terminated'] as $status)
                                <option value="{{ $status }}" @selected(old('lease_status', $rental->lease_status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lease_status')" class="mt-2" />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Ending or terminating the lease frees up the room automatically.</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('rentals.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
