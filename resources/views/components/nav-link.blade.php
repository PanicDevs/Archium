@props(['active'])

@php
$classes = ($active ?? false)
    ? 'bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md'
    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white group flex items-center px-2 py-2 text-sm font-medium rounded-md';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a> 