@props(['value', 'symbol' => '€'])

@php
    $amount = (float) $value;
    $absolute = abs($amount);
    $divisor = $absolute >= 1_000_000 ? 1_000_000 : ($absolute >= 1_000 ? 1_000 : 1);
    $suffix = $divisor === 1_000_000 ? ' M' : ($divisor === 1_000 ? ' K' : '');
@endphp

{{ $symbol }} {{ number_format($amount / $divisor, 2) }}{{ $suffix }}
