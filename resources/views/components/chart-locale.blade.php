@php
    $locale = app()->getLocale();
    $months = collect(range(1, 12))->map(fn ($month) => \Carbon\CarbonImmutable::create(2000, $month, 1)->locale($locale));
    $days = collect(range(0, 6))->map(fn ($day) => \Carbon\CarbonImmutable::create(2026, 1, 4)->addDays($day)->locale($locale));
    $chartLocale = ['name' => $locale, 'options' => [
        'months' => $months->map(fn ($date) => $date->translatedFormat('F'))->all(),
        'shortMonths' => $months->map(fn ($date) => $date->translatedFormat('M'))->all(),
        'days' => $days->map(fn ($date) => $date->translatedFormat('l'))->all(),
        'shortDays' => $days->map(fn ($date) => $date->translatedFormat('D'))->all(),
        'toolbar' => __('ui.chart'),
    ]];
@endphp
<script>
    window.Apex = { ...window.Apex, chart: { ...window.Apex?.chart, locales: [{{ \Illuminate\Support\Js::from($chartLocale) }}], defaultLocale: {{ \Illuminate\Support\Js::from($locale) }} } };
</script>
