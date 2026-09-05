<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2 lg:col-span-1">
    <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Financial variance</p>
    <div class="mt-3 grid grid-cols-2 gap-3">

        <div class="rounded-lg bg-emerald-50 p-3"><span class="text-xs text-emerald-700">Available</span>
            <p class="mt-1 font-bold text-emerald-900">{{ $executiveCurrencySymbol }}
                {{ number_format($executiveFinancial['available'], 2) }}</p>
        </div>

        <div class="rounded-lg bg-violet-50 p-3"><span class="text-xs text-violet-700">Budget - Booked (Real SAP)</span>
            <p class="mt-1 font-bold text-violet-900">{{ $executiveCurrencySymbol }}
                {{ number_format($executiveFinancial['real_variance'], 2) }}</p>
        </div>

        <div class="rounded-lg bg-amber-50 p-3"><span class="text-xs text-amber-700">Assigned rate</span>
            <p class="mt-1 font-bold text-amber-900">{{ number_format($executiveFinancial['booked_rate'], 1) }}%</p>
        </div>

        <div class="rounded-lg bg-cyan-50 p-3"><span class="text-xs text-cyan-700">Execution rate</span>
            <p class="mt-1 font-bold text-cyan-900">{{ number_format($executiveFinancial['execution_rate'], 1) }}%</p>
        </div>

        <div @class([
            'rounded-lg p-3',
            'bg-red-50' => $executiveFinancial['execution_variance'] < 0,
            'bg-slate-50' => $executiveFinancial['execution_variance'] >= 0,
        ])><span @class([
            'text-xs',
            'text-red-700' => $executiveFinancial['execution_variance'] < 0,
            'text-slate-600' => $executiveFinancial['execution_variance'] >= 0,
        ])>Execution variance</span>
            <p @class([
                'mt-1 font-bold',
                'text-red-900' => $executiveFinancial['execution_variance'] < 0,
                'text-slate-900' => $executiveFinancial['execution_variance'] >= 0,
            ])>{{ $executiveCurrencySymbol }}
                {{ number_format($executiveFinancial['execution_variance'], 2) }}</p>
        </div>

        <div @class([
            'rounded-lg p-3',
            'bg-red-100' => $executiveFinancial['execution_overrun'] > 0,
            'bg-emerald-50' => $executiveFinancial['execution_overrun'] <= 0,
        ])><span @class([
            'text-xs',
            'text-red-700' => $executiveFinancial['execution_overrun'] > 0,
            'text-emerald-700' => $executiveFinancial['execution_overrun'] <= 0,
        ])>Execution overrun</span>
            <p @class([
                'mt-1 font-bold',
                'text-red-900' => $executiveFinancial['execution_overrun'] > 0,
                'text-emerald-900' => $executiveFinancial['execution_overrun'] <= 0,
            ])>{{ $executiveCurrencySymbol }}
                {{ number_format($executiveFinancial['execution_overrun'], 2) }}</p>
        </div>
    </div>
</article>
