<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Tenant') }}
        </h2>
            <a href="{{ route('tenants.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">&larr; Back to Tenants</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow-sm sm:rounded-lg">
                @if ($users->isEmpty())
                    <div class="rounded-md bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 p-4">
                        <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-300">No eligible accounts to link</h3>
                        <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
                            Adding a tenant requires a login account that's already been given the "Tenant" role. Ask the person to
                            <a href="{{ route('register') }}" class="underline">register an account</a>, then assign them the "Tenant" role on the <a href="{{ route('users.index') }}" class="underline">Users</a> page — once that's done, they'll show up here.
                        </p>
                    </div>
                @else
                    @php
                        $selectedUserId = old('user_id') !== null ? (string) old('user_id') : '';
                        $selectedUserLabel = $users->firstWhere('id', (int) $selectedUserId);
                        $selectedUserLabel = $selectedUserLabel ? $selectedUserLabel->name.' ('.$selectedUserLabel->email.')' : '';
                    @endphp
                    <div x-data="{
                        open: false,
                        query: {{ Illuminate\Support\Js::from($selectedUserLabel) }},
                        selectedUser: {{ Illuminate\Support\Js::from($selectedUserId) }},
                        users: {{ Illuminate\Support\Js::from($users->map(fn ($user) => ['id' => (string) $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone_number])) }},
                        firstName: '{{ old('first_name') }}',
                        lastName: '{{ old('last_name') }}',
                        email: '{{ old('email') }}',
                        phone: '{{ old('phone_number') }}',
                        highlighted: 0,
                        get filtered() {
                            const q = this.query.trim().toLowerCase();
                            const current = this.users.find(u => u.id === this.selectedUser);
                            const label = u => `${u.name} (${u.email})`;
                            if (! q || (current && this.query === label(current))) return this.users;
                            return this.users.filter(u => label(u).toLowerCase().includes(q));
                        },
                        applyUser(user) {
                            this.selectedUser = user.id;
                            this.query = `${user.name} (${user.email})`;
                            this.open = false;
                            const parts = user.name.trim().split(' ');
                            this.firstName = parts.shift() ?? '';
                            this.lastName = parts.join(' ');
                            this.email = user.email;
                            this.phone = user.phone ?? '';
                        },
                    }" @click.outside="open = false" class="space-y-6">
                        <form method="POST" action="{{ route('tenants.store') }}" class="space-y-6">
                            @csrf

                            <div>
                                <x-input-label for="user_id" value="Registered Account" />
                                <input type="hidden" name="user_id" :value="selectedUser">
                                <input
                                    type="text"
                                    id="user_id"
                                    x-model="query"
                                    @focus="open = true; highlighted = 0"
                                    @input="open = true; highlighted = 0"
                                    @keydown.escape="open = false"
                                    @keydown.down.prevent="open = true; highlighted = Math.min(highlighted + 1, filtered.length - 1)"
                                    @keydown.up.prevent="highlighted = Math.max(highlighted - 1, 0)"
                                    @keydown.enter.prevent="if (filtered[highlighted]) applyUser(filtered[highlighted])"
                                    autocomplete="off"
                                    placeholder="Search the tenant's registered account…"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                >
                                <div x-show="open" x-cloak class="relative">
                                    <div class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-auto">
                                        <template x-for="(user, index) in filtered" :key="user.id">
                                            <button type="button" @click="applyUser(user)" @mouseenter="highlighted = index"
                                                class="block w-full text-left px-3 py-2 text-sm"
                                                :class="highlighted === index ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300'"
                                                x-text="`${user.name} (${user.email})`"
                                            ></button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">No matching accounts.</div>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Only <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">registered accounts</a> that have already been given the "Tenant" role on the <a href="{{ route('users.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Users</a> page show up here.
                                </p>
                                <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="first_name" value="First Name" />
                                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" x-model="firstName" required />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="last_name" value="Last Name" />
                                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" x-model="lastName" required />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" x-model="email" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="phone_number" value="Phone Number" />
                                <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" x-model="phone" />
                                <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="emergency_contact_name" value="Emergency Contact Name" />
                                    <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" class="mt-1 block w-full" value="{{ old('emergency_contact_name') }}" />
                                    <x-input-error :messages="$errors->get('emergency_contact_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="emergency_contact_phone" value="Emergency Contact Phone" />
                                    <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="text" class="mt-1 block w-full" value="{{ old('emergency_contact_phone') }}" />
                                    <x-input-error :messages="$errors->get('emergency_contact_phone')" class="mt-2" />
                                </div>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                To move this tenant into a room, create a <a href="{{ route('rentals.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">rental</a> after saving.
                            </p>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>
                                <a href="{{ route('tenants.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
