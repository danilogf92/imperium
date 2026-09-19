@props(['active' => false])

@php
    $classes =
        $active ?? false
            ? 'inline-flex shrink-0 items-center whitespace-nowrap rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold leading-5 text-white ring-1 ring-inset ring-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600 transition'
            : 'inline-flex shrink-0 items-center whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium leading-5 text-slate-600 hover:bg-blue-700 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-600 transition';
@endphp

<a @if ($active) aria-current="page" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
