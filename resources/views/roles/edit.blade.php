<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Role') }}
        </h2>
            <a href="{{ route('roles.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Roles</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            value="{{ old('name', $role->name) }}" required autofocus
                            @if ($isProtected) readonly @endif />
                        @if ($isProtected)
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">This role is used by the system and can't be renamed.</p>
                        @endif
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    @php $selected = $role->permissions->pluck('id')->all(); @endphp
                    @include('roles._permissions-fields')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('roles.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
