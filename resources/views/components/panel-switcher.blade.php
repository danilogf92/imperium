@props(['admin' => false])
@if ($admin)
    <x-filament::button :href="route('dashboard')" tag="a" color="gray" outlined
        icon="heroicon-o-arrow-left" labeled-from="md" aria-label="Sistema" title="Sistema">
        Sistema
    </x-filament::button>
@elseif (auth()->user()?->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
    <x-ui-button :href="\Filament\Facades\Filament::getPanel('admin')->getUrl()"
        icon="arrow-right" icon-position="right" compact-mobile text="Admin" />
@endif
