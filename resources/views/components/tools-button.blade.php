@props(['variant' => 'secondary', 'type' => 'button'])
<button type="{{ $type }}" wire:loading.attr="disabled" {{ $attributes->class([
    'inline-flex min-h-10 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-50',
    'bg-blue-600 text-white hover:bg-blue-700' => $variant === 'primary',
    'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' => $variant === 'secondary',
    'text-blue-700 hover:bg-blue-50' => $variant === 'link',
]) }}>{{ $slot }}</button>
