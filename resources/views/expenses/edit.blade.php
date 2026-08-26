<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Expense') }}
        </h2>
            <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Expenses</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="property_id" value="Property" />
                        <select id="property_id" name="property_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected(old('property_id', $expense->property_id) == $property->id)>{{ $property->property_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="category" value="Category" />
                        <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" value="{{ old('category', $expense->category) }}" required autofocus />
                        <x-input-error :messages="$errors->get('category')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="amount" value="Amount" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('amount', $expense->amount) }}" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="expense_date" value="Date" />
                            <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required />
                            <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $expense->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
