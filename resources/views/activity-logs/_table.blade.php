@php
    $actionClasses = [
        'created' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
        'updated' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'deleted' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        'granted' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
        'revoked' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'activated' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
        'deactivated' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    ];
    $showUser = $showUser ?? true;
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-12">#</th>
                @if ($showUser)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                @endif
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($activities as $activity)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activities->firstItem() + $loop->index }}</td>
                    @if ($showUser)
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                            @if ($activity->causer)
                                <a href="{{ route('users.show', $activity->causer) }}" class="hover:underline">{{ $activity->causer->name }}</a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">System</span>
                            @endif
                        </td>
                    @endif
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $actionClasses[$activity->action] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ Str::headline($activity->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activity->description }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $activity->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showUser ? 5 : 4 }}" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No activity recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
