<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ __('Orders') }}</p>
            <h1 class="text-2xl font-bold text-slate-900">
                {{ $project ? $project->name : __('All project orders') }}
            </h1>
            @if ($project)
                <p class="mt-1 text-sm text-slate-500">PDA: {{ $project->pda_code }}</p>
            @endif
        </div>

        @if ($project)
            <div class="flex gap-2">
                <a href="{{ route('orders') }}" wire:navigate
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-px hover:bg-blue-500 hover:shadow-md">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12.5 15 7.5 10l5-5M8 10h8" />
                    </svg>
                    {{ __('Back to orders') }}
                </a>
            </div>
        @endif
    </header>

    <section x-data="{ open: true }" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-blue-50 via-white to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 5.25h16.5l-6.375 7.125v5.25l-3.75 1.875v-7.125L3.75 5.25Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-bold text-slate-900">{{ __('Order filters') }}</h2>
                        @if (filled($search))
                            <span class="rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                Active
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500">{{ __('Search and adjust the orders displayed in the table') }}</p>
                </div>
            </div>
            <button type="button" x-on:click="open = !open"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                </svg>
            </button>
        </div>

        <div x-show="open" x-collapse>
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <label for="orders-search" class="sr-only">{{ __('Search orders') }}</label>
                <div class="relative w-full sm:max-w-lg">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                    </svg>
                    <input id="orders-search" wire:model.live.debounce.400ms="search" data-global-loading type="text"
                        placeholder="{{ __('Search order, project or PDA') }}"
                        autocomplete="off"
                        class="block h-11 w-full rounded-lg border border-slate-300 bg-white py-2 pl-11 pr-11 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    @if (filled($search))
                        <button wire:click="clearSearch" data-global-loading type="button" title="{{ __('Clear search') }}"
                            aria-label="{{ __('Clear search') }}"
                            class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                            </svg>
                        </button>
                    @endif
                </div>
                <span class="hidden whitespace-nowrap text-sm text-slate-500 lg:inline">
                    {{ number_format($orders->total()) }} orders
                </span>
            </div>

            <x-per-page-select id="orders-per-page" />
        </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-100/80 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="w-16 px-4 py-3 text-center">Excel</th>
                        <th class="px-4 py-3">Project</th>
                        <th class="px-4 py-3">PDA</th>
                        <th class="px-4 py-3">
                            <button wire:click="toggleSort" data-global-loading type="button" class="font-semibold hover:text-blue-600">
                                Order No. {{ $sortDir === 'asc' ? '↑' : '↓' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right">Items</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr wire:key="order-{{ $order->project_id }}-{{ $order->order_no }}"
                            class="text-slate-700 transition odd:bg-white even:bg-slate-50/50 hover:bg-blue-50/60">
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="downloadOrder({{ $order->project_id }}, @js((string) $order->order_no))"
                                    data-global-loading type="button"
                                    title="Download order Excel"
                                    aria-label="Download order {{ $order->order_no }} as Excel"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                    style="background-color: #2563eb">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v12m0 0 4-4m-4 4-4-4M5 18.75h14" />
                                    </svg>
                                </button>
                            </td>
                            <td class="max-w-sm px-4 py-3">
                                <a href="{{ route('projects.orders', ['project' => $order->project_id]) }}"
                                    wire:navigate class="font-semibold text-slate-900 hover:text-blue-600">
                                    {{ $order->project_name }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-md bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-700">
                                    {{ $order->pda_code }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-700">
                                    {{ $order->order_no }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex min-w-8 justify-center rounded-full bg-emerald-100 px-2.5 py-1 font-semibold text-emerald-700">
                                    {{ number_format($order->item_count) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('projects.dashboard', ['project' => $order->project_id]) }}"
                                        wire:navigate title="View project dashboard"
                                        aria-label="View project dashboard"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-amber-600 bg-amber-500 text-white shadow-sm transition hover:-translate-y-px hover:bg-amber-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 19.5h15M6.75 16.5v-4.5m5.25 4.5V8.25m5.25 8.25V5.25M5.25 3.75h13.5a1.5 1.5 0 0 1 1.5 1.5v12.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                                        </svg>
                                    </a>
                                    @if (!$project)
                                        <a href="{{ route('projects.orders', ['project' => $order->project_id]) }}"
                                            wire:navigate title="View project orders" aria-label="View project orders"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-700 bg-blue-600 text-white shadow-sm transition hover:-translate-y-px hover:bg-blue-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Zm2.25 4.5h7.5m-7.5 3.75h7.5m-7.5 3.75h4.5" />
                                            </svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('projects.data', ['project' => $order->project_id]) }}"
                                        wire:navigate title="View project data" aria-label="View project data"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-emerald-700 bg-emerald-600 text-white shadow-sm transition hover:-translate-y-px hover:bg-emerald-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 3.75c4.142 0 7.5 1.343 7.5 3s-3.358 3-7.5 3-7.5-1.343-7.5-3 3.358-3 7.5-3Zm7.5 3v5.25c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3V6.75m15 5.25v5.25c0 1.657-3.358 3-7.5 3s-7.5-1.343-7.5-3V12" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No orders were found for the selected scope.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/70 p-4">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
</div>
