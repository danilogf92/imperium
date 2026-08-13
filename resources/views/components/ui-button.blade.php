@props([
    'text' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'color' => '#2563EB',
    'hoverColor' => '#1D4ED8',
    'hoverOpacity' => null,
    'textColor' => '#FFFFFF',
    'href' => null,
    'type' => 'button',
])

@php
    $icons = [
        'plus' => 'M12 4.5v15m7.5-7.5h-15',

        'download' => 'M12 3v12m0 0 4-4m-4 4-4-4M5 19.5h14',

        'upload' => 'M12 16.5v-12m0 0-4.5 4.5M12 4.5l4.5 4.5M5 19.5h14',

        'excel' => 'M7 3.75h7.5L19.5 8.7v11.55H7V3.75Zm7.5 0V9h5M9.5 12l4 5m0-5-4 5',

        'file' =>
            'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.625a9 9 0 0 0-9-9Z',

        'document' =>
            'M15.75 2.25H6A2.25 2.25 0 0 0 3.75 4.5v15A2.25 2.25 0 0 0 6 21.75h12A2.25 2.25 0 0 0 20.25 19.5V6.75L15.75 2.25Zm0 0v4.5h4.5M8.25 12h7.5m-7.5 3h7.5m-7.5 3H12',

        'folder' =>
            'M3.75 6.75v10.5A2.25 2.25 0 0 0 6 19.5h12a2.25 2.25 0 0 0 2.25-2.25V9A2.25 2.25 0 0 0 18 6.75h-5.25l-1.5-2.25H6A2.25 2.25 0 0 0 3.75 6.75Z',

        'search' => 'm21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z',

        'edit' =>
            'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L18.55 2.8M16.862 4.487 19.5 7.125',

        'delete' =>
            'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.477c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0',

        'save' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',

        'check' => 'M4.5 12.75 9 17.25 19.5 6.75',

        'close' => 'M6 6l12 12M18 6 6 18',

        'arrow-left' => 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18',

        'arrow-right' => 'M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3',

        'external-link' =>
            'M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-7.5 3L21 3m0 0h-6.75M21 3v6.75',

        'chart' =>
            'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Zm6.75-4.5c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625Zm6.75-4.5C16.5 3.504 17.004 3 17.625 3h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',

        'graph' => 'M3.75 19.5h16.5M6.75 16.5v-6m5.25 6V6.75m5.25 9.75V9',

        'chart-line' => 'M3 18.75 8.25 13.5l3.75 3.75 8.25-9M16.5 8.25h3.75V12',

        'chart-bar' => 'M4.5 20.25V12h3v8.25h-3Zm6 0V6.75h3v13.5h-3Zm6 0V9.75h3v10.5h-3Z',

        'pie-chart' => 'M11.25 3.75a8.25 8.25 0 1 0 9 9h-9v-9Zm3 0v6h6a8.25 8.25 0 0 0-6-6Z',

        'table' => 'M3.75 5.25h16.5v13.5H3.75V5.25Zm0 4.5h16.5m-11 0v9',

        'database' =>
            'M4.5 6c0-1.657 3.358-3 7.5-3s7.5 1.343 7.5 3-3.358 3-7.5 3-7.5-1.343-7.5-3Zm0 0v6c0 1.657 3.358 3 7.5 3s7.5-1.343 7.5-3V6m-15 6v6c0 1.657 3.358 3 7.5 3s7.5-1.343 7.5-3v-6',

        'calendar' =>
            'M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z',

        'filter' => 'M3.75 5.25h16.5L13.5 13v5.25l-3 1.5V13L3.75 5.25Z',

        'refresh' => 'M16.023 9.348h4.992V4.356m-.997 13.292A9 9 0 1 1 21.015 9.35',

        'settings' =>
            'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.28a7.5 7.5 0 0 1 1.608.93l1.21-.455a1.125 1.125 0 0 1 1.326.49l1.296 2.245c.275.476.163 1.08-.26 1.43l-.997.827a7.6 7.6 0 0 1 0 1.857l.997.827c.423.35.535.954.26 1.43l-1.296 2.245a1.125 1.125 0 0 1-1.326.49l-1.21-.455a7.5 7.5 0 0 1-1.608.93l-.213 1.28c-.09.542-.56.94-1.11.94h-2.592c-.55 0-1.02-.398-1.11-.94l-.213-1.28a7.5 7.5 0 0 1-1.608-.93l-1.21.455a1.125 1.125 0 0 1-1.326-.49L3.98 14.8a1.125 1.125 0 0 1 .26-1.43l.997-.827a7.6 7.6 0 0 1 0-1.857L4.24 9.86a1.125 1.125 0 0 1-.26-1.43l1.296-2.245a1.125 1.125 0 0 1 1.326-.49l1.21.455a7.5 7.5 0 0 1 1.608-.93l.213-1.28ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',

        'user' => 'M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0',

        'users' => 'M15 19.5a6 6 0 0 0-12 0m12 0h6m-3-3v6M12 7.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z',

        'eye' =>
            'M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Zm12.75 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',

        'home' => 'M2.25 12 12 3l9.75 9M4.5 9.75V21h15V9.75M9 21v-6h6v6',

        'info' => 'M11.25 11.25h1.5v5.25h-1.5v-5.25ZM12 7.5h.008v.008H12V7.5ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',

        'warning' => 'M12 3 2.25 20.25h19.5L12 3Zm0 6v4.5m0 3h.008',
    ];

    $iconPath = is_string($icon) ? $icons[$icon] ?? null : null;
    $iconRight = $iconPosition === 'right';
    $normalizedHoverOpacity = is_numeric($hoverOpacity) ? max(0, min(1, (float) $hoverOpacity)) : 1;
    $useHoverOpacity = $hoverOpacity !== null;
    $styles = "--ui-button-bg: {$color}; --ui-button-hover: {$hoverColor}; --ui-button-text: {$textColor}; --ui-button-hover-opacity: {$normalizedHoverOpacity};";
    $classes =
        'generic-ui-button inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg border border-transparent px-4 text-sm font-semibold shadow-sm transition duration-150 hover:-translate-y-px hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50';
    $classes .= $useHoverOpacity ? ' generic-ui-button-opacity-hover' : '';
@endphp

@if ($href)
    <a href="{{ $href }}" style="{{ $styles }}" {{ $attributes->class($classes) }}>
        @if ($iconPath && !$iconRight)
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            </svg>
        @endif
        <span>{{ $text ?? $slot }}</span>
        @if ($iconPath && $iconRight)
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            </svg>
        @endif
    </a>
@else
    <button type="{{ $type }}" style="{{ $styles }}" {{ $attributes->class($classes) }}>
        @if ($iconPath && !$iconRight)
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            </svg>
        @endif
        <span>{{ $text ?? $slot }}</span>
        @if ($iconPath && $iconRight)
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            </svg>
        @endif
    </button>
@endif
