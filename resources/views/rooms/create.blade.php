<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Room') }}
        </h2>
            <a href="{{ route('rooms.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Rooms</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('rooms.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="property_id" value="Property" />
                        <select id="property_id" name="property_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select a property</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)>{{ $property->property_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                        @if ($properties->isEmpty())
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                No properties yet — <a href="{{ route('properties.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">add one first</a>.
                            </p>
                        @endif
                    </div>

                    <div>
                        <x-input-label for="room_number" value="Room Number" />
                        <x-text-input id="room_number" name="room_number" type="text" class="mt-1 block w-full" value="{{ old('room_number') }}" required autofocus placeholder="e.g. Apt 4B, Room 101" />
                        <x-input-error :messages="$errors->get('room_number')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="floor" value="Floor" />
                            <x-text-input id="floor" name="floor" type="text" class="mt-1 block w-full" value="{{ old('floor') }}" />
                            <x-input-error :messages="$errors->get('floor')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="size_sqft" value="Size (sqft)" />
                            <x-text-input id="size_sqft" name="size_sqft" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('size_sqft') }}" />
                            <x-input-error :messages="$errors->get('size_sqft')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="base_rent_amount" value="Base Rent Amount" />
                        <x-text-input id="base_rent_amount" name="base_rent_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('base_rent_amount') }}" required />
                        <x-input-error :messages="$errors->get('base_rent_amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['vacant', 'occupied', 'maintenance'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'vacant') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label value="Amenities" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">What's included in this room — useful context when setting the base rent.</p>
                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach ($amenities as $amenity)
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                        @checked(collect(old('amenities', []))->contains($amenity->id))
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    {{ $amenity->name }}
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('amenities')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('rooms.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
