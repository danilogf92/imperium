<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Project health</p>
    <div class="mt-3 flex items-center gap-3">
        <span @class(['h-4 w-4 rounded-full ring-4', 'bg-emerald-500 ring-emerald-100' => $executiveHealth['color'] === 'emerald', 'bg-amber-500 ring-amber-100' => $executiveHealth['color'] === 'amber', 'bg-red-500 ring-red-100' => $executiveHealth['color'] === 'red'])></span>
        <strong class="text-lg text-slate-900">{{ $executiveHealth['label'] }}</strong>
    </div>
    <p class="mt-2 text-sm text-slate-500">Status, deadlines, financial limits and missing control information.</p>
</article>
