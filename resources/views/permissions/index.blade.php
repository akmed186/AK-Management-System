@php
    $canManageRoles = auth()->user()->can('manage roles');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Permission Matrix') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                What each role is allowed to do across the system.
                @if ($canManageRoles)
                    Click a cell to grant or revoke it.
                @endif
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-status :status="session('status')" />

            @if ($canManageRoles)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <form method="POST" action="{{ route('permissions.store') }}" class="flex items-end gap-3">
                        @csrf
                        <div class="flex-1 max-w-sm">
                            <x-input-label for="name" value="New Permission" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" placeholder="e.g. manage settings" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('Add Permission') }}</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Permission</th>
                                @foreach ($roles as $role)
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ Str::headline($role->name) }}</th>
                                @endforeach
                                @if ($canManageRoles)
                                    <th class="px-6 py-3"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($permissions as $permission)
                                @php $grantedRoleIds = $permission->roles->pluck('id'); @endphp
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ Str::headline($permission->name) }}</td>
                                    @foreach ($roles as $role)
                                        <td class="px-6 py-4 text-center">
                                            @if ($canManageRoles)
                                                <input type="checkbox"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                                                    data-role-id="{{ $role->id }}"
                                                    data-permission-id="{{ $permission->id }}"
                                                    @checked($grantedRoleIds->contains($role->id))
                                                    onchange="togglePermission(this)">
                                            @elseif ($grantedRoleIds->contains($role->id))
                                                <svg class="w-5 h-5 mx-auto text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </td>
                                    @endforeach
                                    @if ($canManageRoles)
                                        <td class="px-6 py-4 text-right">
                                            <form method="POST" action="{{ route('permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this permission? It will be revoked from every role.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($roles) + ($canManageRoles ? 2 : 1) }}" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">No permissions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($canManageRoles)
        @push('scripts')
            <script>
                function togglePermission(checkbox) {
                    const wasChecked = checkbox.checked;
                    checkbox.disabled = true;

                    fetch('{{ route('permissions.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            role_id: checkbox.dataset.roleId,
                            permission_id: checkbox.dataset.permissionId,
                        }),
                    })
                        .then((response) => {
                            if (! response.ok) {
                                throw new Error('Request failed');
                            }
                            return response.json();
                        })
                        .then((data) => {
                            checkbox.checked = data.granted;
                        })
                        .catch(() => {
                            checkbox.checked = ! wasChecked;
                            alert('Could not update that permission. Please try again.');
                        })
                        .finally(() => {
                            checkbox.disabled = false;
                        });
                }
            </script>
        @endpush
    @endif
</x-app-layout>
