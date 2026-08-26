<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ selected: [], allIds: @json($users->pluck('id')) }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search users..."
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <select name="status" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" @selected($status === '')>All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                    <select name="role" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" @selected($role === '')>All roles</option>
                        <option value="unassigned" @selected($role === 'unassigned')>No role assigned</option>
                    </select>
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600">
                        Search
                    </button>
                    @if ($search || $status || $role)
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    @endif
                </form>

                    <x-export-menu route="users.export" />
                </div>

                <form method="POST" action="{{ route('users.bulk-deactivate') }}"
                    @submit="if (! confirm(`Deactivate ${selected.length} selected user(s)?`)) $event.preventDefault()"
                    x-show="selected.length > 0" x-cloak class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="`${selected.length} selected`"></span>
                    <button type="submit" class="inline-flex items-center px-3 py-2 bg-amber-600 text-white rounded-md text-sm font-medium hover:bg-amber-700">
                        Deactivate Selected
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-visible shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-12">#</th>
                            <th class="px-6 py-3 w-10">
                                <input type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                                    :checked="allIds.length > 0 && selected.length === allIds.length"
                                    @change="selected = $event.target.checked ? [...allIds] : []">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $users->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4">
                                    <input type="checkbox" value="{{ $user->id }}" x-model="selected"
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('users.show', $user) }}" class="hover:underline">{{ $user->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($user->roles->isNotEmpty())
                                        {{ $user->roles->map(fn ($userRole) => Str::headline($userRole->name))->join(', ') }}
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">No role assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($user->is_active)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <x-row-actions>
                                        <x-dropdown-link href="{{ route('users.show', $user) }}">View</x-dropdown-link>
                                        <x-dropdown-link href="{{ route('users.edit', $user) }}">Edit</x-dropdown-link>
                                        @if ($user->is_active)
                                            <form method="POST" action="{{ route('users.deactivate', $user) }}" onsubmit="return confirm('Deactivate this user? They will no longer be able to sign in.');">
                                                @csrf
                                                @method('PATCH')
                                                <x-dropdown-button>Deactivate</x-dropdown-button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('users.activate', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-dropdown-button>Activate</x-dropdown-button>
                                            </form>
                                        @endif
                                        @unless ($user->id === auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user account? Their tenant profile, activity history, and other records stay — only their login is removed. This can\'t be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <x-dropdown-button danger>Delete</x-dropdown-button>
                                            </form>
                                        @endunless
                                    </x-row-actions>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($search || $status || $role)
                                        No users match your filters.
                                    @else
                                        No users found.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
