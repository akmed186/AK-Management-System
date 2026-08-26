<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Maintenance Request') }}
        </h2>
            <a href="{{ route('maintenance-requests.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Maintenance Requests</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('maintenance-requests.update', $maintenanceRequest) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="property_id" value="Property" />
                        <x-searchable-select
                            name="property_id"
                            id="property_id"
                            :options="$properties->map(fn ($property) => ['value' => $property->id, 'label' => $property->property_name])"
                            :selected="$maintenanceRequest->property_id"
                            placeholder="Search properties…"
                            empty="No matching properties."
                            required
                        />
                        <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="room_id" value="Room (optional)" />
                        <x-searchable-select
                            name="room_id"
                            id="room_id"
                            :options="$rooms->map(fn ($room) => ['value' => $room->id, 'label' => $room->property->property_name.' — '.$room->room_number])"
                            :selected="$maintenanceRequest->room_id"
                            placeholder="Property-wide — search to pick a specific room…"
                            empty="No matching rooms."
                        />
                        <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="complaint_id" value="Related Complaint (optional)" />
                        <x-searchable-select
                            name="complaint_id"
                            id="complaint_id"
                            :options="$complaints->map(fn ($complaint) => ['value' => $complaint->id, 'label' => $complaint->title])"
                            :selected="$maintenanceRequest->complaint_id"
                            placeholder="Staff-initiated — search to link a complaint…"
                            empty="No matching complaints."
                        />
                        <x-input-error :messages="$errors->get('complaint_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="assigned_to_user_id" value="Assigned To (optional)" />
                        <x-searchable-select
                            name="assigned_to_user_id"
                            id="assigned_to_user_id"
                            :options="$users->map(fn ($user) => ['value' => $user->id, 'label' => $user->name])"
                            :selected="$maintenanceRequest->assigned_to_user_id"
                            placeholder="Unassigned — search for a staff member…"
                            empty="No matching users."
                        />
                        <x-input-error :messages="$errors->get('assigned_to_user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="cost" value="Cost (optional)" />
                        <x-text-input id="cost" name="cost" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('cost', $maintenanceRequest->cost) }}" />
                        <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            @foreach (['scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $maintenanceRequest->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="scheduled_date" value="Scheduled Date" />
                            <x-text-input id="scheduled_date" name="scheduled_date" type="date" class="mt-1 block w-full" value="{{ old('scheduled_date', $maintenanceRequest->scheduled_date?->format('Y-m-d')) }}" />
                            <x-input-error :messages="$errors->get('scheduled_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="completed_date" value="Completed Date" />
                            <x-text-input id="completed_date" name="completed_date" type="date" class="mt-1 block w-full" value="{{ old('completed_date', $maintenanceRequest->completed_date?->format('Y-m-d')) }}" />
                            <x-input-error :messages="$errors->get('completed_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('maintenance-requests.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
