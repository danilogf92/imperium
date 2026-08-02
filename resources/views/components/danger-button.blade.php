<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-red-600 bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition duration-150 hover:-translate-y-px hover:border-red-500 hover:bg-red-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:translate-y-0 active:bg-red-700 disabled:cursor-wait disabled:opacity-50']) }}>
    {{ $slot }}
</button>
