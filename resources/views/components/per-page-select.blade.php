@props([
    'model' => 'perPage',
    'id' => 'per-page',
    'label' => 'Show',
    'options' => [5, 10, 20, 50, 100],
])

<label for="{{ $id }}" class="flex shrink-0 items-center gap-2 whitespace-nowrap text-sm font-medium text-slate-600">
    <span>{{ $label }}</span>
    <select id="{{ $id }}" wire:model.live="{{ $model }}" data-global-loading aria-label="Records per page"
        title="Records per page"
        style="cursor: pointer;"
        class="h-11 min-w-20 cursor-pointer rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm outline-none transition duration-150 hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/25">
        @foreach ($options as $option)
        <option style="cursor: pointer;" class="cursor-pointer" value="{{ $option }}">{{ $option }}</option>
        @endforeach
    </select>
</label>
