@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-2 pt-1 border-b-2 border-orange-400 bg-sky-50 text-sm font-semibold leading-5 text-sky-800 focus:outline-none focus:border-orange-500 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-2 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-600 hover:text-sky-800 hover:border-orange-300 hover:bg-sky-50 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
