<div class="dashboard-page-shell">
    <div class="dashboard-page-content space-y-6">
        <section class="module-accent-line overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="soft-title-surface border-b px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 2.25h7.5L19.5 7.5v12A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V4.5a2.25 2.25 0 0 1 2.25-2.25Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.25 2.25V7.5h5.25M8 13l2 3m0-3-2 3m4-3v3m3-3v3" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('Excel templates') }}</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Download the approved files before preparing project data or orders.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 p-4 sm:p-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($templates as $template)
                    <article
                        class="flex min-h-56 flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition duration-150 hover:-translate-y-px hover:border-blue-300 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 border-emerald-500 text-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 19.5h16M6.5 17V11m5 6V6.5m5 10.5V9" />
                                </svg>
                            </div>
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                {{ match ($template->category) {
                                    'orders' => 'Orders',
                                    'project_ideas' => 'Project Ideas',
                                    'project_data' => 'Project Data',
                                    default => \Illuminate\Support\Str::headline($template->category),
                                } }}
                            </span>
                        </div>

                        <h2 class="mt-4 text-base font-bold text-slate-900">{{ $template->name }}</h2>
                        <p class="mt-2 flex-1 text-sm leading-6 text-slate-500">
                            {{ $template->description }}
                        </p>

                        {{-- <a href="{{ route('templates.download', $template) }}" data-no-global-loading
                            class="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-[#6AA9E3] bg-[#7DB9F1] px-4 text-sm font-semibold text-slate-900 shadow-sm transition duration-150 hover:-translate-y-px hover:border-[#5A9AD7] hover:bg-[#6AA9E3] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#7DB9F1]/40 focus:ring-offset-2">

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v11m0 0 4-4m-4 4-4-4M5 19.5h14" />
                            </svg>

                            <span>{{ __('Download template') }}</span>
                        </a> --}}

                        <x-ui-button :href="route('templates.download', $template)" :text="__('Download template')" icon="download" color="#7DB9F1"
                            hover-opacity="0.80" text-color="#FFFFFF" data-no-global-loading class="mt-5" />
                    </article>
                @empty
                    <div
                        class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                        <p class="font-semibold text-slate-700">No templates are available.</p>
                        <p class="mt-1 text-sm text-slate-500">An administrator can publish them from Filament.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
