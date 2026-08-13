<img
    src="{{ \App\Models\BrandSetting::logoUrl() }}"
    alt="{{ \App\Models\BrandSetting::current()?->name ?? 'DaImperium' }}"
    {{ $attributes }}
>
