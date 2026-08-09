<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <h3 class="font-bold text-slate-900">Weekly activities</h3>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        @foreach ($executiveActivities as $week)
            <div class="rounded-lg border border-cyan-100 bg-cyan-50 p-3"><p class="text-xs font-bold uppercase text-cyan-700">{{ $week['label'] }} · W{{ str_pad($week['week'], 2, '0', STR_PAD_LEFT) }}</p><ul class="mt-2 space-y-2 text-sm text-slate-700">@forelse ($week['items'] as $activity)<li class="rounded-md bg-white p-2">{{ $activity }}</li>@empty<li class="text-slate-500">No activities registered.</li>@endforelse</ul></div>
        @endforeach
    </div>
</article>
