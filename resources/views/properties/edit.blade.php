<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Property') }}
        </h2>
            <a href="{{ route('properties.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Properties</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('properties.update', $property) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="owner_id" value="Owner" />
                        <select id="owner_id" name="owner_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}" @selected(old('owner_id', $property->owner_id) == $owner->id)>{{ $owner->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('owner_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="property_name" value="Property Name" />
                        <x-text-input id="property_name" name="property_name" type="text" class="mt-1 block w-full" value="{{ old('property_name', $property->property_name) }}" required autofocus />
                        <x-input-error :messages="$errors->get('property_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="property_type" value="Property Type" />
                        <select id="property_type" name="property_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['Apartment Complex', 'Commercial', 'Single Family'] as $type)
                                <option value="{{ $type }}" @selected(old('property_type', $property->property_type) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('property_type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="address" value="Address" />
                        <textarea id="address" name="address" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>{{ old('address', $property->address) }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="city" value="City" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" value="{{ old('city', $property->city) }}" required />
                            <x-input-error :messages="$errors->get('city')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="state" value="State" />
                            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" value="{{ old('state', $property->state) }}" required />
                            <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="zip_code" value="ZIP Code" />
                            <x-text-input id="zip_code" name="zip_code" type="text" class="mt-1 block w-full" value="{{ old('zip_code', $property->zip_code) }}" required />
                            <x-input-error :messages="$errors->get('zip_code')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('properties.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
