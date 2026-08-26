<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Comment') }}
        </h2>
            <a href="{{ route('complaint-comments.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Complaint Comments</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('complaint-comments.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="complaint_id" value="Complaint" />
                        <select id="complaint_id" name="complaint_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select a complaint</option>
                            @foreach ($complaints as $complaint)
                                <option value="{{ $complaint->id }}" @selected(old('complaint_id') == $complaint->id)>{{ $complaint->title }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('complaint_id')" class="mt-2" />
                        @if ($complaints->isEmpty())
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                No complaints yet — <a href="{{ route('complaints.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">log one first</a>.
                            </p>
                        @endif
                    </div>

                    <div>
                        <x-input-label for="comment_text" value="Comment" />
                        <textarea id="comment_text" name="comment_text" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required autofocus>{{ old('comment_text') }}</textarea>
                        <x-input-error :messages="$errors->get('comment_text')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('complaint-comments.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
