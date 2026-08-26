@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Search…',
    'id' => null,
    'empty' => 'No matches.',
])

@php
    $id = $id ?? $name;
    // Callers can pass extra keys per option (e.g. a rental's monthly_rent)
    // beyond value/label — they ride along in the dispatched "select" event
    // so a parent form can react to a pick without a full custom rewrite.
    $normalizedOptions = collect($options)->map(fn ($option) => array_merge($option, [
        'value' => (string) $option['value'],
        'label' => $option['label'],
    ]))->values();
    $selectedValue = old($name, $selected);
    $selectedValue = $selectedValue !== null ? (string) $selectedValue : '';
    $selectedLabel = $normalizedOptions->firstWhere('value', $selectedValue)['label'] ?? '';
@endphp

{{--
    A type-to-filter replacement for a plain <select>, for lists that can grow
    long (tenants, rooms, users, ...). Submits the same single form field a
    <select> would — a hidden input carries the real value — so controllers
    and validation rules don't need to change, only the markup that renders
    the field.

    Dispatches a "select" event with the full chosen option (value, label,
    and any extra keys passed in :options) — listen for it on an ancestor
    element (e.g. <form @select="...">) to react to a pick, such as
    prefilling another field from data carried on the option.
--}}
<div
    x-data="{
        open: false,
        query: {{ Illuminate\Support\Js::from($selectedLabel) }},
        value: {{ Illuminate\Support\Js::from($selectedValue) }},
        options: {{ Illuminate\Support\Js::from($normalizedOptions) }},
        highlighted: 0,
        get filtered() {
            const q = this.query.trim().toLowerCase();
            const current = this.options.find(o => o.value === this.value);
            if (! q || (current && this.query === current.label)) return this.options;
            return this.options.filter(o => o.label.toLowerCase().includes(q));
        },
        select(option) {
            this.value = option.value;
            this.query = option.label;
            this.open = false;
            this.$dispatch('select', option);
        },
        clear() {
            this.value = '';
            this.query = '';
            this.open = true;
            this.$refs.input.focus();
        },
    }"
    @click.outside="open = false"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" :value="value">

    <div class="relative">
        <input
            type="text"
            x-ref="input"
            x-model="query"
            @focus="open = true; highlighted = 0"
            @input="open = true; highlighted = 0"
            @keydown.escape="open = false"
            @keydown.down.prevent="open = true; highlighted = Math.min(highlighted + 1, filtered.length - 1)"
            @keydown.up.prevent="highlighted = Math.max(highlighted - 1, 0)"
            @keydown.enter.prevent="if (filtered[highlighted]) select(filtered[highlighted])"
            id="{{ $id }}"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pr-8']) }}
        >
        <button type="button" x-show="value" x-cloak @click="clear()" tabindex="-1"
            class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak
        class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-auto">
        <template x-for="(option, index) in filtered" :key="option.value">
            <button type="button" @click="select(option)" @mouseenter="highlighted = index"
                class="block w-full text-left px-3 py-2 text-sm"
                :class="highlighted === index ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300'"
                x-text="option.label"
            ></button>
        </template>
        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $empty }}</div>
    </div>
</div>
