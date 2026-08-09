<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3"><h3 class="font-bold text-slate-900">Upcoming milestones</h3><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $executiveMilestones['elapsed'] }}/{{ $executiveMilestones['total'] }} elapsed</span></div>
    <div class="mt-4 space-y-3">
        @forelse ($executiveMilestones['upcoming'] as $milestone)
            <div class="flex items-center gap-3 rounded-lg border border-slate-100 p-3"><span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $milestone['color'] }}"></span><div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-800">{{ $milestone['code'] }} · {{ $milestone['name'] }}</p><p class="text-xs text-slate-500">{{ $milestone['date'] }} · {{ number_format($milestone['percentage'], 1) }}%</p></div></div>
        @empty
            <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500">No upcoming milestones.</p>
        @endforelse
    </div>
</article>
