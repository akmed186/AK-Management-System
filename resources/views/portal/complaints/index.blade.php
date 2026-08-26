<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Complaints') }}
            </h2>
            <a href="{{ route('portal.complaints.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-sm font-medium hover:bg-gray-700 dark:hover:bg-white">
                File a Complaint / Request
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-status :status="session('status')" />

            @forelse ($complaints as $complaint)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $complaint->title }}</h3>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $complaint->room->property->property_name }} — {{ $complaint->room->room_number }}
                                &middot; Filed {{ $complaint->created_at->format('M j, Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-medium',
                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $complaint->priority === 'low',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' => $complaint->priority === 'medium',
                                'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' => $complaint->priority === 'high',
                                'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' => $complaint->priority === 'emergency',
                            ])>
                                {{ ucfirst($complaint->priority) }}
                            </span>
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ Str::headline($complaint->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                        {{ $complaint->description }}
                    </div>

                    @if ($complaint->comments->isNotEmpty())
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 space-y-3 bg-gray-50 dark:bg-gray-900/40">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Updates</h4>
                            @foreach ($complaint->comments as $comment)
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $comment->user?->name ?? 'Staff' }}</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $comment->created_at->format('M j, Y g:ia') }}</span>
                                    <p class="text-gray-600 dark:text-gray-400">{{ $comment->comment_text }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    You haven't filed any complaints yet.
                </div>
            @endforelse

            {{ $complaints->links() }}
        </div>
    </div>
</x-app-layout>
