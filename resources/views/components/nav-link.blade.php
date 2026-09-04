@props(['active'])

@php
    $classes =
        $active ?? false
            ? 'inline-flex items-center rounded-t-md px-2 pt-1 border-b-2 border-blue-700 bg-blue-700 text-sm font-semibold leading-5 text-white shadow-sm focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-t-md px-2 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-600 hover:border-blue-700 hover:bg-blue-700 hover:text-white hover:shadow-sm focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
