@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-blue-500 text-start text-base font-semibold text-white bg-[#7DB9F1] [text-shadow:0_1px_2px_rgb(30_64_175_/_0.65)] focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:border-blue-500 hover:bg-[#7DB9F1] hover:text-white hover:[text-shadow:0_1px_2px_rgb(30_64_175_/_0.65)] focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
