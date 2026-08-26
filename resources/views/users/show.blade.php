<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('User') }}
            </h2>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Users</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="shrink-0 w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center text-lg font-semibold">
                            {{ Str::of($user->name)->substr(0, 1)->upper() }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</h3>
                                @if ($user->is_active)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">Inactive</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                            @if ($user->phone_number)
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->phone_number }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @if ($user->is_active)
                            <form method="POST" action="{{ route('users.deactivate', $user) }}" onsubmit="return confirm('Deactivate this user? They will no longer be able to sign in.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-md text-sm font-medium hover:bg-amber-700">
                                    Deactivate
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('users.activate', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-medium hover:bg-emerald-700">
                                    Activate
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                            Edit
                        </a>
                        @unless ($user->id === auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user account? Their tenant profile, activity history, and other records stay — only their login is removed. This can\'t be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                                    Delete
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                    <div>
                        <div class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase">Role</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @if ($user->roles->isNotEmpty())
                                {{ $user->roles->map(fn ($userRole) => Str::headline($userRole->name))->join(', ') }}
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">No role assigned</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase">Joined</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M j, Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase">Activities Logged</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $activities->total() }}</div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide mb-3">Activity</h3>

                @include('activity-logs._table', ['activities' => $activities, 'showUser' => false])

                {{ $activities->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
