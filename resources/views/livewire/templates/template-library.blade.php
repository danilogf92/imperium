<div class="dashboard-page-shell">
    <div class="dashboard-page-content space-y-6">
        <section class="module-accent-line overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="soft-title-surface border-b px-4 py-3">
                <h1 class="text-lg font-bold tracking-tight text-slate-900">{{ __('Files & templates') }}</h1>
                <p class="mt-1 text-xs text-slate-500">{{ __('Download general files and files shared with your plants.') }}</p>
            </header>

            <nav class="flex flex-wrap gap-1.5 border-b border-slate-200 px-4 py-2" aria-label="{{ __('File sections') }}">
                @foreach (['all' => __('All available'), 'general' => __('General')] as $key => $label)
                    <button type="button" wire:click="$set('section', '{{ $key }}')"
                        aria-pressed="{{ $section === $key ? 'true' : 'false' }}"
                        @class([
                            'rounded-md border px-2.5 py-1 text-xs font-semibold transition',
                            'border-blue-600 bg-blue-600 text-white' => $section === $key,
                            'border-slate-200 bg-white text-slate-600 hover:bg-blue-50' => $section !== $key,
                        ])>{{ $label }}</button>
                @endforeach
                @foreach ($companies as $company)
                    <button type="button" wire:click="$set('section', '{{ $company->id }}')"
                        aria-pressed="{{ $section === (string) $company->id ? 'true' : 'false' }}"
                        @class([
                            'rounded-md border px-2.5 py-1 text-xs font-semibold transition',
                            'border-blue-600 bg-blue-600 text-white' => $section === (string) $company->id,
                            'border-slate-200 bg-white text-slate-600 hover:bg-blue-50' => $section !== (string) $company->id,
                        ])>{{ $company->company_name }}</button>
                @endforeach
            </nav>

            <div class="space-y-4 p-3 sm:p-4" wire:loading.class="opacity-50">
                @php
                    $groups = collect();
                    $generalFiles = $templates->where('is_global', true);
                    if ($generalFiles->isNotEmpty()) {
                        $groups->put('general', ['label' => __('General'), 'files' => $generalFiles]);
                    }
                    foreach ($companies as $company) {
                        if ($section !== 'all' && $section !== (string) $company->id) {
                            continue;
                        }
                        $companyFiles = $templates->filter(fn ($template) => ! $template->is_global && $template->companies->contains('id', $company->id));
                        if ($companyFiles->isNotEmpty()) {
                            $groups->put('company-'.$company->id, ['label' => $company->company_name, 'files' => $companyFiles]);
                        }
                    }
                @endphp
                @forelse ($groups as $groupKey => $group)
                    @php($files = $group['files'])
                    <details wire:key="library-section-{{ $section }}-{{ $groupKey }}"
                        class="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center gap-3 bg-slate-50 px-4 py-4 transition hover:bg-blue-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-600 [&::-webkit-details-marker]:hidden">
                            <span @class([
                                'h-8 w-1 shrink-0 rounded-full',
                                'bg-blue-500' => $groupKey === 'general',
                                'bg-amber-500' => $groupKey !== 'general',
                            ])></span>
                            <h2 class="min-w-0 flex-1 break-words text-sm font-bold text-slate-900">{{ $group['label'] }}</h2>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $files->count() }} {{ __('Files') }}</span>
                            <svg class="h-5 w-5 shrink-0 text-slate-500 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                        </summary>
                        <div class="grid gap-2 border-t border-slate-200 p-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($files as $template)
                    <article wire:key="library-file-{{ $groupKey }}-{{ $template->id }}"
                        @class([
                            'flex min-w-0 flex-col rounded-lg border p-3',
                            'border-blue-200 bg-blue-50/30' => $template->is_global,
                            'border-amber-200 bg-amber-50/30' => ! $template->is_global,
                        ])>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span @class([
                                'rounded px-1.5 py-0.5 text-[10px] font-bold uppercase',
                                'bg-red-100 text-red-700' => $template->fileType() === 'pdf',
                                'bg-orange-100 text-orange-700' => in_array($template->fileType(), ['ppt', 'pptx']),
                                'bg-emerald-100 text-emerald-700' => in_array($template->fileType(), ['xls', 'xlsx']),
                            ])>{{ $template->fileType() }}</span>
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                {{ \Illuminate\Support\Str::headline($template->category) }}
                            </span>
                        </div>
                        <h3 class="mt-2 break-words text-sm font-bold leading-5 text-slate-900">{{ $template->name }}</h3>
                        @if ($template->description)
                            <p class="mt-1 line-clamp-2 text-xs leading-4 text-slate-500" title="{{ $template->description }}">{{ $template->description }}</p>
                        @endif
                        <p class="mt-2 text-[11px] font-semibold text-slate-600">
                            {{ $template->is_global ? __('All users') : $template->companies->whereIn('id', $companies->modelKeys())->pluck('company_name')->implode(', ') }}
                        </p>
                        <p class="mt-1 truncate text-[11px] text-slate-400" title="{{ $template->original_file_name }}">{{ $template->original_file_name }}</p>
                        <div class="mt-auto pt-2">
                            <a href="{{ route('templates.download', $template) }}" data-no-global-loading
                                class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-2 py-1 text-xs font-semibold text-white hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 19.5h14" /></svg>
                                {{ __('Download file') }}
                            </a>
                        </div>
                    </article>
                @endforeach
                        </div>
                    </details>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                        <p class="font-semibold text-slate-700">{{ __('No files are available in this section.') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
