<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Tenant') }}
        </h2>
            <a href="{{ route('tenants.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Tenants</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="first_name" value="First Name" />
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" value="{{ old('first_name', $tenant->first_name) }}" required autofocus />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="last_name" value="Last Name" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" value="{{ old('last_name', $tenant->last_name) }}" required />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $tenant->email) }}" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone_number" value="Phone Number" />
                        <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" value="{{ old('phone_number', $tenant->phone_number) }}" />
                        <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="emergency_contact_name" value="Emergency Contact Name" />
                            <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" class="mt-1 block w-full" value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}" />
                            <x-input-error :messages="$errors->get('emergency_contact_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="emergency_contact_phone" value="Emergency Contact Phone" />
                            <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="text" class="mt-1 block w-full" value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}" />
                            <x-input-error :messages="$errors->get('emergency_contact_phone')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="user_id" value="Linked User Account" />
                        <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">None</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $tenant->user_id) == $user->id)>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Lets this person log in and see their own info. They must <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">register an account</a> first, then assign them the "Tenant" role on the <a href="{{ route('users.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Users</a> page.
                        </p>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    @if ($tenant->currentRental)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Currently in {{ $tenant->currentRental->room->property->property_name }} — {{ $tenant->currentRental->room->room_number }}.
                            <a href="{{ route('rentals.edit', $tenant->currentRental) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Manage lease</a>
                        </p>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Not currently assigned to a room. <a href="{{ route('rentals.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Create a rental</a> to move this tenant in.
                        </p>
                    @endif

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('tenants.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
