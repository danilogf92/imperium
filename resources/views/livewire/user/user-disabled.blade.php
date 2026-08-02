<div class="flex min-h-[70vh] items-center justify-center">

    <div class="w-full max-w-xl rounded-xl bg-white p-10 shadow-lg dark:bg-gray-800">

        <div class="flex justify-center">
            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-red-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728" />
                </svg>
            </div>
        </div>

        <h1 class="mt-6 text-center text-3xl font-bold text-gray-800 dark:text-white">
            User Disabled
        </h1>

        <p class="mt-4 text-center text-gray-600 dark:text-gray-300">
            Your account has been disabled.
        </p>

        <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
            Please contact your system administrator if you believe this is an error.
        </p>

        <div class="mt-8 flex justify-center">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">
                Return to Dashboard
            </a>
        </div>

    </div>

</div>
