@props([
    'model' => 'perPage',
    'id' => 'per-page',
    'label' => __('Show'),
    'ariaLabel' => 'Records per page',
    'options' => [5, 10, 20, 50, 100],
])

<label for="{{ $id }}" class="flex shrink-0 items-center gap-2 whitespace-nowrap text-sm font-medium text-slate-600">
    <span class="hidden sm:inline">{{ __($label) }}</span>
    <select id="{{ $id }}" wire:model.live="{{ $model }}" data-global-loading aria-label="{{ __($ariaLabel) }}"
        title="{{ __($ariaLabel) }}"
        style="cursor: pointer;"
        class="app-per-page-select h-11 min-w-20 cursor-pointer rounded-lg border border-slate-300 bg-white pl-3 pr-8 text-sm font-semibold text-slate-700 shadow-sm outline-none transition duration-150 hover:border-blue-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/25">
        @foreach ($options as $option)
        <option style="cursor: pointer;" class="cursor-pointer" value="{{ $option }}">{{ $option }}</option>
        @endforeach
    </select>
</label>
