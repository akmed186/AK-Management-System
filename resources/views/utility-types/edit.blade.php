<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Utility Type') }}
        </h2>
            <a href="{{ route('utility-types.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Utility Types</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('utility-types.update', $utilityType) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="utility_name" value="Utility Name" />
                        <x-text-input id="utility_name" name="utility_name" type="text" class="mt-1 block w-full" value="{{ old('utility_name', $utilityType->utility_name) }}" required autofocus />
                        <x-input-error :messages="$errors->get('utility_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="unit_of_measure" value="Unit of Measure" />
                        <x-text-input id="unit_of_measure" name="unit_of_measure" type="text" class="mt-1 block w-full" value="{{ old('unit_of_measure', $utilityType->unit_of_measure) }}" required />
                        <x-input-error :messages="$errors->get('unit_of_measure')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="rate_per_unit" value="Rate Per Unit" />
                        <x-text-input id="rate_per_unit" name="rate_per_unit" type="number" step="0.0001" min="0" class="mt-1 block w-full" value="{{ old('rate_per_unit', $utilityType->rate_per_unit) }}" required />
                        <x-input-error :messages="$errors->get('rate_per_unit')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('utility-types.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
