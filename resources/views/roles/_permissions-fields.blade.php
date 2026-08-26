@php
    $selected = old('permissions', $selected ?? []);
@endphp

<div>
    <x-input-label value="Permissions" />
    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach ($permissions as $permission)
            <label class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                    @checked(in_array($permission->id, $selected))>
                {{ Str::headline($permission->name) }}
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
</div>
