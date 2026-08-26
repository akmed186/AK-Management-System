@props(['status'])

@if ($status)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 3000)"
        x-transition
        {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 dark:text-green-400']) }}
    >
        {{ $status }}
    </div>
@endif
