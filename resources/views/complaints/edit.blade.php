<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Complaint') }}
        </h2>
            <a href="{{ route('complaints.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Complaints</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('complaints.update', $complaint) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="tenant_id" value="Tenant" />
                        <x-searchable-select
                            name="tenant_id"
                            id="tenant_id"
                            :options="$tenants->map(fn ($tenant) => ['value' => $tenant->id, 'label' => $tenant->full_name])"
                            :selected="$complaint->tenant_id"
                            placeholder="Search tenants…"
                            empty="No matching tenants."
                            required
                        />
                        <x-input-error :messages="$errors->get('tenant_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="room_id" value="Room" />
                        <x-searchable-select
                            name="room_id"
                            id="room_id"
                            :options="$rooms->map(fn ($room) => ['value' => $room->id, 'label' => $room->property->property_name.' — '.$room->room_number])"
                            :selected="$complaint->room_id"
                            placeholder="Search rooms…"
                            empty="No matching rooms."
                            required
                        />
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" value="{{ old('title', $complaint->title) }}" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>{{ old('description', $complaint->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="priority" value="Priority" />
                            <select id="priority" name="priority" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                @foreach (['low', 'medium', 'high', 'emergency'] as $priority)
                                    <option value="{{ $priority }}" @selected(old('priority', $complaint->priority) === $priority)>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                @foreach (['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $complaint->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('complaints.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Comments</h3>
                    <a href="{{ route('complaint-comments.create') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Add comment</a>
                </div>

                @forelse ($complaint->comments as $comment)
                    <div class="py-3 border-t border-gray-100 dark:border-gray-700 first:border-t-0 first:pt-0">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $comment->user?->name ?? 'Deleted user' }}</span>
                            <span>{{ $comment->created_at->format('M j, Y g:ia') }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $comment->comment_text }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No comments yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
