@props(['admin' => false])
@if ($admin)
    <x-filament::button :href="route('dashboard')" tag="a" color="gray" outlined
        icon="heroicon-o-arrow-left" labeled-from="md" aria-label="{{ __('System') }}" title="{{ __('System') }}">
        {{ __('System') }}
    </x-filament::button>
@elseif (auth()->user()?->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
    <x-ui-button :href="\Filament\Facades\Filament::getPanel('admin')->getUrl()"
        icon="arrow-right" icon-position="right" compact-mobile text="{{ __('Admin') }}" />
@endif
