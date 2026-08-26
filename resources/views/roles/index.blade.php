<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Roles') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    An overview of the roles configured in your system and what they can do.
                </p>
            </div>
            @if (auth()->user()->can('manage roles'))
                <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                    Add Role
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ($roles as $role)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ Str::headline($role->name) }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</p>
                                </div>
                            </div>

                            @if (auth()->user()->can('manage roles'))
                                <div class="flex items-center gap-3 text-sm shrink-0">
                                    <a href="{{ route('roles.edit', $role) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 flex flex-wrap gap-1.5">
                            @forelse ($role->permissions->take(4) as $permission)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    {{ Str::headline($permission->name) }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-400 dark:text-gray-500">No permissions assigned.</span>
                            @endforelse
                            @if ($role->permissions->count() > 4)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                    +{{ $role->permissions->count() - 4 }} more
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $role->permissions->count() }} {{ Str::plural('permission', $role->permissions->count()) }}</span>
                            <a href="{{ route('permissions.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                View in Matrix &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
