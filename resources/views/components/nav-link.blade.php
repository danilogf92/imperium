@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-t-md px-2 pt-1 border-b-2 border-blue-500 bg-[#7DB9F1] text-sm font-semibold leading-5 text-white [text-shadow:0_1px_2px_rgb(30_64_175_/_0.65)] focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-t-md px-2 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-600 hover:border-blue-500 hover:bg-[#7DB9F1] hover:text-white hover:shadow-sm hover:[text-shadow:0_1px_2px_rgb(30_64_175_/_0.65)] focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
