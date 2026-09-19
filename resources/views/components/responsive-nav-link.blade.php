@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg px-4 py-2 text-start text-sm font-semibold text-white bg-[#7DB9F1] focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600 transition'
            : 'block w-full rounded-lg px-4 py-2 text-start text-sm font-medium text-slate-600 hover:bg-[#7DB9F1] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600 transition';
@endphp

<a @if ($active) aria-current="page" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
