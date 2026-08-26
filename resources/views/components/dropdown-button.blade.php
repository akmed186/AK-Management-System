@props(['danger' => false])

{{-- Button counterpart to <x-dropdown-link> for actions that must POST
     (delete, deactivate, ...) instead of navigating. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'block w-full px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 '.($danger ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300')]) }}>{{ $slot }}</button>
