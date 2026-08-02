<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-blue-600 bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition duration-150 hover:-translate-y-px hover:border-blue-500 hover:bg-blue-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:translate-y-0 active:bg-blue-700 disabled:cursor-wait disabled:opacity-50']) }}>
    {{ $slot }}
</button>
