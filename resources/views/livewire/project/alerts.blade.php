<div>
    @if (session()->has('delete-project'))
        <div class="fixed top-4 right-4 z-50 w-full max-w-sm px-4 sm:px-0" role="alert">
            <div
                class="flex items-start justify-between rounded-lg border-l-4 border-orange-700 bg-red-500 p-4 text-white shadow-lg">
                <div class="flex items-start gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                    </svg>

                    <p class="text-sm font-medium">
                        {{ session('delete-project') }}
                    </p>

                </div>
            </div>
        </div>
    @endif

    @if (session()->has('create-project'))
        <div class="fixed top-4 right-4 z-50 w-full max-w-sm px-4 sm:px-0" role="alert">
            <div
                class="flex items-start justify-between rounded-lg border-l-4 border-green-700 bg-green-400 p-4 text-green-900 shadow-lg">
                <div class="flex items-start gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <p class="text-sm font-medium">
                        {{ session('create-project') }}
                    </p>

                </div>
            </div>
        </div>
    @endif

    @if (session()->has('edit-project'))
        <div class="fixed top-4 right-4 z-50 w-full max-w-sm px-4 sm:px-0" role="alert">
            <div
                class="flex items-start justify-between rounded-lg border-l-4 border-orange-700 bg-red-500 p-4 text-white shadow-lg">
                <div class="flex items-start gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.41-9.41a2 2 0 112.83 2.83L11 15H8v-3l9.59-9.59z" />
                    </svg>

                    <p class="text-sm font-medium">
                        {{ session('edit-project') }}
                    </p>

                </div>
            </div>
        </div>
    @endif

    @if (session()->has('delete-excel-data'))
        <div class="fixed top-4 right-4 z-50 w-full max-w-sm px-4 sm:px-0" role="alert">
            <div
                class="flex items-start justify-between rounded-lg border-l-4 border-orange-700 bg-red-500 p-4 text-white shadow-lg">
                <div class="flex items-start gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>

                    <p class="text-sm font-medium">
                        {{ session('delete-excel-data') }}
                    </p>

                </div>
            </div>
        </div>
    @endif

    @if (session()->has('load-excel-data'))
        <div class="fixed top-4 right-4 z-50 w-full max-w-sm px-4 sm:px-0" role="alert">
            <div
                class="flex items-start justify-between rounded-lg border-l-4 border-green-700 bg-green-400 p-4 text-green-900 shadow-lg">
                <div class="flex items-start gap-3">

                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    <p class="text-sm font-medium">
                        {{ session('load-excel-data') }}
                    </p>

                </div>
            </div>
        </div>
    @endif
</div>
