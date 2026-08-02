<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Main</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Styles -->
        {{-- <style>
            /* ! tailwindcss v3.2.4 | MIT License | https://tailwindcss.com */*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}::after,::before{--tw-content:''}html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:Figtree, sans-serif;font-feature-settings:normal}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-size:100%;font-weight:inherit;line-height:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}[type=button],[type=reset],[type=submit],button{-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]{display:none}*, ::before, ::after{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::-webkit-backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }::backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: }.relative{position:relative}.mx-auto{margin-left:auto;margin-right:auto}.mx-6{margin-left:1.5rem;margin-right:1.5rem}.ml-4{margin-left:1rem}.mt-16{margin-top:4rem}.mt-6{margin-top:1.5rem}.mt-4{margin-top:1rem}.-mt-px{margin-top:-1px}.mr-1{margin-right:0.25rem}.flex{display:flex}.inline-flex{display:inline-flex}.grid{display:grid}.h-16{height:4rem}.h-7{height:1.75rem}.h-6{height:1.5rem}.h-5{height:1.25rem}.min-h-screen{min-height:100vh}.w-auto{width:auto}.w-16{width:4rem}.w-7{width:1.75rem}.w-6{width:1.5rem}.w-5{width:1.25rem}.max-w-7xl{max-width:80rem}.shrink-0{flex-shrink:0}.scale-100{--tw-scale-x:1;--tw-scale-y:1;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}.grid-cols-1{grid-template-columns:repeat(1, minmax(0, 1fr))}.items-center{align-items:center}.justify-center{justify-content:center}.gap-6{gap:1.5rem}.gap-4{gap:1rem}.self-center{align-self:center}.rounded-lg{border-radius:0.5rem}.rounded-full{border-radius:9999px}.bg-gray-100{--tw-bg-opacity:1;background-color:rgb(243 244 246 / var(--tw-bg-opacity))}.bg-white{--tw-bg-opacity:1;background-color:rgb(255 255 255 / var(--tw-bg-opacity))}.bg-red-50{--tw-bg-opacity:1;background-color:rgb(254 242 242 / var(--tw-bg-opacity))}.bg-dots-darker{background-image:url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E")}.from-gray-700\/50{--tw-gradient-from:rgb(55 65 81 / 0.5);--tw-gradient-to:rgb(55 65 81 / 0);--tw-gradient-stops:var(--tw-gradient-from), var(--tw-gradient-to)}.via-transparent{--tw-gradient-to:rgb(0 0 0 / 0);--tw-gradient-stops:var(--tw-gradient-from), transparent, var(--tw-gradient-to)}.bg-center{background-position:center}.stroke-red-500{stroke:#ef4444}.stroke-gray-400{stroke:#9ca3af}.p-6{padding:1.5rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.text-center{text-align:center}.text-right{text-align:right}.text-xl{font-size:1.25rem;line-height:1.75rem}.text-sm{font-size:0.875rem;line-height:1.25rem}.font-semibold{font-weight:600}.leading-relaxed{line-height:1.625}.text-gray-600{--tw-text-opacity:1;color:rgb(75 85 99 / var(--tw-text-opacity))}.text-gray-900{--tw-text-opacity:1;color:rgb(17 24 39 / var(--tw-text-opacity))}.text-gray-500{--tw-text-opacity:1;color:rgb(107 114 128 / var(--tw-text-opacity))}.underline{-webkit-text-decoration-line:underline;text-decoration-line:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.shadow-2xl{--tw-shadow:0 25px 50px -12px rgb(0 0 0 / 0.25);--tw-shadow-colored:0 25px 50px -12px var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.shadow-gray-500\/20{--tw-shadow-color:rgb(107 114 128 / 0.2);--tw-shadow:var(--tw-shadow-colored)}.transition-all{transition-property:all;transition-timing-function:cubic-bezier(0.4, 0, 0.2, 1);transition-duration:150ms}.selection\:bg-red-500 *::selection{--tw-bg-opacity:1;background-color:rgb(239 68 68 / var(--tw-bg-opacity))}.selection\:text-white *::selection{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.selection\:bg-red-500::selection{--tw-bg-opacity:1;background-color:rgb(239 68 68 / var(--tw-bg-opacity))}.selection\:text-white::selection{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.hover\:text-gray-900:hover{--tw-text-opacity:1;color:rgb(17 24 39 / var(--tw-text-opacity))}.hover\:text-gray-700:hover{--tw-text-opacity:1;color:rgb(55 65 81 / var(--tw-text-opacity))}.focus\:rounded-sm:focus{border-radius:0.125rem}.focus\:outline:focus{outline-style:solid}.focus\:outline-2:focus{outline-width:2px}.focus\:outline-red-500:focus{outline-color:#ef4444}.group:hover .group-hover\:stroke-gray-600{stroke:#4b5563}.z-10{z-index: 10}@media (prefers-reduced-motion: no-preference){.motion-safe\:hover\:scale-\[1\.01\]:hover{--tw-scale-x:1.01;--tw-scale-y:1.01;transform:translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))}}@media (prefers-color-scheme: dark){.dark\:bg-gray-900{--tw-bg-opacity:1;background-color:rgb(17 24 39 / var(--tw-bg-opacity))}.dark\:bg-gray-800\/50{background-color:rgb(31 41 55 / 0.5)}.dark\:bg-red-800\/20{background-color:rgb(153 27 27 / 0.2)}.dark\:bg-dots-lighter{background-image:url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E")}.dark\:bg-gradient-to-bl{background-image:linear-gradient(to bottom left, var(--tw-gradient-stops))}.dark\:stroke-gray-600{stroke:#4b5563}.dark\:text-gray-400{--tw-text-opacity:1;color:rgb(156 163 175 / var(--tw-text-opacity))}.dark\:text-white{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.dark\:shadow-none{--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}.dark\:ring-1{--tw-ring-offset-shadow:var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);--tw-ring-shadow:var(--tw-ring-inset) 0 0 0 calc(1px + var(--tw-ring-offset-width)) var(--tw-ring-color);box-shadow:var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)}.dark\:ring-inset{--tw-ring-inset:inset}.dark\:ring-white\/5{--tw-ring-color:rgb(255 255 255 / 0.05)}.dark\:hover\:text-white:hover{--tw-text-opacity:1;color:rgb(255 255 255 / var(--tw-text-opacity))}.group:hover .dark\:group-hover\:stroke-gray-400{stroke:#9ca3af}}@media (min-width: 640px){.sm\:fixed{position:fixed}.sm\:top-0{top:0px}.sm\:right-0{right:0px}.sm\:ml-0{margin-left:0px}.sm\:flex{display:flex}.sm\:items-center{align-items:center}.sm\:justify-center{justify-content:center}.sm\:justify-between{justify-content:space-between}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width: 768px){.md\:grid-cols-2{grid-template-columns:repeat(2, minmax(0, 1fr))}}@media (min-width: 1024px){.lg\:gap-8{gap:2rem}.lg\:p-8{padding:2rem}}
        </style> --}}
    </head>
    {{-- <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 selection:bg-red-500 selection:text-white">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6 lg:p-8 bg-gray-100 font-sans">

            </div>
        </div>
    </body> --}}

    <body class="bg-custom">
                <!-- Navigation -->
        <nav class="bg-custom flex justify-between py-6 w-full lg:px-48 md:px-12 px-4 content-center bg-secondary border">
            <div class="flex items-center">
                {{-- <img src="{{ asset('img/Underline1.svg') }}" alt="Mi Imagen" class="h-4"> --}}
                <span class="italic font-semibold bg-left-bottom bg-no-repeat pb-4 bg-100%" style="background-image: url('{{ asset('img/Underline1.svg') }}');">
                    DaImperium
                </span>

            </div>

            {{-- <ul class="font-montserrat items-center hidden md:flex">
                <li class="mx-3 ">
                    <a class="growing-underline" href="howitworks">
                    How it works
                    </a>
                </li>
                <li class="growing-underline mx-3">
                    <a href="features">Features</a>
                </li>
                <li class="growing-underline mx-3">
                    <a href="pricing">Pricing</a>
                </li>
            </ul> --}}

            {{-- <div class="font-montserrat hidden md:block">
                <button class="mr-6">Login</button>
                <button class="py-2 px-4 text-white bg-black rounded-3xl">
                    Signup
                </button>
            </div> --}}

            {{-- <div id="showMenu" class="md:hidden">
                <img src='dist/assets/logos/Menu.svg' alt="Menu icon" />
            </div> --}}

            @if (Route::has('login'))
                <div class="sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-white bg-stone-800 hover:bg-stone-600 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50 rounded-md px-4 py-2 transition duration-300 ease-in-out">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50 rounded-md px-4 py-2 transition duration-300 ease-in-out">Login</a>

                        @if (Route::has('register'))
                           {{-- <a href="{{ route('register') }}" class="font-semibold text-white bg-stone-800 hover:bg-stone-600 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50 rounded-md px-4 py-2 transition duration-300 ease-in-out">Register</a> --}}
                           
                           <a href="#" class="font-semibold text-white bg-stone-800 hover:bg-stone-600 focus:outline-none focus:ring focus:ring-gray-400 focus:ring-opacity-50 rounded-md px-4 py-2 transition duration-300 ease-in-out">Register</a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
        {{-- <div id='mobileNav' class="hidden px-4 py-6 fixed top-0 left-0 h-full w-full bg-secondary z-20 animate-fade-in-down">
            <div id="hideMenu" class="flex justify-end">
                <img src='dist/assets/logos/Cross.svg' alt="" class="h-16 w-16" />
            </div>
            <ul class="font-montserrat flex flex-col mx-8 my-24 items-center text-3xl">
                <li class="my-6">
                    <a href="howitworks">How it works</a>
                </li>
                <li class="my-6">
                    <a href="features">Features</a>
                </li>
                <li class="my-6">
                    <a href="pricing">Pricing</a>
                </li>
            </ul>
        </div> --}}

        <!-- Hero -->
        {{-- <section class="md:mt-0 md:h-screen flex flex-col md:flex-row md:justify-between md:items-center lg:px-24 md:px-6 p-4 text-center md:text-left">
            <div class="md:flex-1 md:mr-10">
                <h1 class="italic font-semibold text-5xl mb-7">
                    Project
                    <span class="italic font-semibold bg-left-bottom bg-no-repeat pb-4 bg-100%" style="background-image: url('{{ asset('img/Underline1.svg') }}');">
                        Managment
                    </span>
                </h1>

                <p class="text-justify p-2">
                    Efficient Industrial Project Management Software
                    Our software streamlines industrial project management, simplifying every phase from planning to execution.
                    Designed for various sectors, we offer tools for detailed planning, resource allocation, budget management, and real-time tracking.
                </p>

                <p class="text-justify p-2">
                    Ensure projects meet deadlines and budgets with our platform. Maximize efficiency, minimize costs, and take your company to the next level.
                </p>
            </div>

            <div class="flex justify-around md:block mt-8 md:mt-0 md:flex-1">
                <img class="lg:h-80 md:h-40 sm:h-20 rounded shadow" src="{{ asset('img/project_management.png') }}" alt="Project Management" />
            </div>
        </section> --}}
        <section class="md:mt-0 md:h-screen flex flex-col md:flex-row md:justify-between md:items-center lg:px-24 md:px-6 p-4 text-center md:text-left">
            <div class="md:flex-1 md:mr-10">
                <h1 class="italic font-semibold text-5xl mb-7">
                    Project
                    <span class="italic font-semibold bg-left-bottom bg-no-repeat pb-4 bg-100%" style="background-image: url('{{ asset('img/Underline1.svg') }}');">
                        Management
                    </span>
                </h1>

                <p class="text-justify p-2">
                    Efficient Industrial Project Management Software
                    Our software streamlines industrial project management, simplifying every phase from planning to execution.
                    Designed for various sectors, we offer tools for detailed planning, resource allocation, budget management, and real-time tracking.
                </p>

                <p class="text-justify p-2">
                    Ensure projects meet deadlines and budgets with our platform. Maximize efficiency, minimize costs, and take your company to the next level.
                </p>
            </div>

            <div class="flex justify-around md:block mt-8 md:mt-0 md:flex-1">
                <img class="rounded shadow-xl object-contain" src="{{ asset('img/project_management.png') }}" alt="Project Management" />
            </div>
        </section>

        <!-- How it works -->
        <section class="bg-black text-white sectionSize py-4">
            <div class="text-center pt-2 pb-8">
                {{-- <h2 class="text-2xl font-extrabold italic bg-left-bottom bg-no-repeat pb-4 bg-100%" style="background-image: url('{{ asset('img/Underline2.svg') }}');">How it works</h2> --}}
                <span class="text-2xl italic font-extrabold bg-left-bottom bg-no-repeat pb-8 bg-100%" style="background-image: url('{{ asset('img/Underline2.svg') }}');">
                    How it Works
                </span>
            </div>
            <div class="flex flex-col md:flex-row">
            <div class="flex-1 mx-8 flex flex-col items-center my-4">
                <div class="bg-white border-2 rounded-full bg-secondary text-black h-12 w-12 flex justify-center items-center mb-3">
                    1
                </div>
                <h3 class="font-montserrat font-medium text-xl mb-2">Create</h3>
                <p class="text-center font-montserrat">
                    Begin by creating a project in our software. Input project details, objectives, and scope. Define the timeline and allocate necessary resources
                </p>
            </div>
            <div class="flex-1 mx-8 flex flex-col items-center my-4">
                <div class="bg-white border-2 rounded-full bg-secondary text-black h-12 w-12 flex justify-center items-center mb-3">
                    2
                </div>
                <h3 class="font-montserrat font-medium text-xl mb-2">Update</h3>
                <p class="text-center font-montserrat">
                    Continuously update and monitor your project's progress. Easily make changes, update schedules, and adapt to any evolving requirements.
                </p>
            </div>
            <div class="flex-1 mx-8 flex flex-col items-center my-4">
                <div class="bg-white border-2 rounded-full bg-secondary text-black h-12 w-12 flex justify-center items-center mb-3">
                    3
                </div>
                <h3 class="font-montserrat font-medium text-xl mb-2">Schedule</h3>
                <p class="text-center font-montserrat">
                    Effectively schedule tasks, deadlines, and milestones within our platform. Keep your project on track with automated reminders and real-time updates
                </p>
            </div>
            </div>
        </section>

        <!-- Features -->
        <section class="sectionSize bg-secondary">
            <div>
                <div class="text-center pt-2 pb-8">
                    {{-- <h2 class="text-2xl font-extrabold italic bg-left-bottom bg-no-repeat pb-4 bg-100%" style="background-image: url('{{ asset('img/Underline2.svg') }}');">How it works</h2> --}}
                    <span class="text-2xl italic font-extrabold bg-left-bottom bg-no-repeat pb-10 bg-100%" style="background-image: url('{{ asset('img/Underline3.svg') }}');">
                        Software Features Description
                    </span>
                </div>

                <div class="md:grid md:grid-cols-2 md:grid-rows-2">

                <div class="flex items-start font-montserrat my-6 mr-10">
                    <img src='dist/assets/logos/Heart.svg' alt='' class="h-7 mr-4" />
                    <div>
                    <h3 class="font-semibold text-2xl">Precise Planning and Tracking #1</h3>
                    <p class="text-justify py-2">
                        An effective project management software should offer detailed planning capabilities with scheduling and task assignment tools.
                        Furthermore, it should enable real-time tracking of project progress through Gantt charts and dashboards.
                        This ensures that teams can quickly adjust their activities to meet deadlines and objectives.
                    </p>
                    </div>
                </div>

                <div class="flex items-start font-montserrat my-6 mr-10">
                    <img src='dist/assets/logos/Heart.svg' alt='' class="h-7 mr-4" />
                    <div>
                    <h3 class="font-semibold text-2xl">Integrated Collaboration and Communication #2</h3>
                    <p class="text-justify py-2">
                        Effective communication is crucial in project management.
                        The software should facilitate collaboration among team members through chats, online comments, and document sharing.
                        Integration with email and video conferencing tools is also essential to keep all stakeholders informed and connected.
                    </p>
                    </div>
                </div>

                <div class="flex items-start font-montserrat my-6 mr-10">
                    <img src='dist/assets/logos/Heart.svg' alt='' class="h-7 mr-4" />
                    <div>
                    <h3 class="font-semibold text-2xl">Resource and Cost Management #3</h3>
                    <p class="text-justify py-2">
                        To ensure proper resource allocation and cost control, project management software should provide features to manage budget, time, and human resources.
                        This includes tracking working hours, expense management, and efficient resource scheduling to avoid overloads and delays.
                    </p>
                    </div>
                </div>

                <div class="flex items-start font-montserrat my-6 mr-10">
                    <img src='dist/assets/logos/Heart.svg' alt='' class="h-7 mr-4" />
                    <div>
                        <h3 class="font-semibold text-2xl">Reporting and Analysis Generation #4</h3>
                        <p class="text-justify py-2">
                            Customizable reports and analytical capabilities are essential for assessing project performance and making informed decisions.
                            Good software should provide clear data visualizations, status reports, and trend analysis so that managers can identify areas for improvement and take corrective actions in a timely manner.
                            These features help optimize the decision-making process in project management.
                        </p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Pricing -->
        {{-- <section class="sectionSize bg-secondary py-0">
            <div>
            <h2 class="secondaryTitle bg-underline4 mb-0 bg-100%">Pricing</h2>
            </div>
            <div class="flex w-full flex-col md:flex-row">

            <div class='flex-1 flex flex-col mx-6 shadow-2xl relative bg-secondary rounded-2xl py-5 px-8 my-8 md:top-24'>
                <h3 class="font-pt-serif font-normal text-2xl mb-4">
                The Good
                </h3>
                <div class="font-montserrat font-bold text-2xl mb-4">
                $25
                <span class="font-normal text-base"> / month</span>
                </div>

                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #1</p>
                </div>
                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #2</p>
                </div>
                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #3</p>
                </div>

                <button class=" border-2 border-solid border-black rounded-xl text-lg py-3 mt-4">
                Choose plan
                </button>
            </div>

            <div class='flex-1 flex flex-col mx-6 shadow-2xl relative bg-secondary rounded-2xl py-5 px-8 my-8 md:top-12'>
                <h3 class="font-pt-serif font-normal text-2xl mb-4">
                The Bad
                </h3>
                <div class="font-montserrat font-bold text-2xl mb-4">
                $40
                <span class="font-normal text-base"> / month</span>
                </div>

                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #1</p>
                </div>
                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #2</p>
                </div>
                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #3</p>
                </div>

                <button class=" border-2 border-solid border-black rounded-xl text-lg py-3 mt-4">
                Choose plan
                </button>
            </div>

            <div class='flex-1 flex flex-col mx-6 shadow-2xl relative bg-secondary rounded-2xl py-5 px-8 my-8 md:top-24'>
                <h3 class="font-pt-serif font-normal text-2xl mb-4">
                The Ugly
                </h3>
                <div class="font-montserrat font-bold text-2xl mb-4">
                $50
                <span class="font-normal text-base"> / month</span>
                </div>

                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #1</p>
                </div>
                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #2</p>
                </div>
                <div class="flex">
                <img src='dist/assets/logos/CheckedBox.svg' alt="" class="mr-1" />
                <p>Benefit #3</p>
                </div>

                <button class=" border-2 border-solid border-black rounded-xl text-lg py-3 mt-4">
                Choose plan
                </button>
            </div>

            </div>
        </section> --}}

        <!-- FAQ  -->
        {{-- <section class="sectionSize items-start pt-8 md:pt-36 bg-black text-white">
            <div>
            <h2 class="secondaryTitle bg-highlight3 p-10 mb-0 bg-center bg-100%">
                FAQ
            </h2>
            </div>

            <div toggleElement class="w-full py-4">
            <div class="flex justify-between items-center">
                <div question class="font-montserrat font-medium mr-auto">
                Where can I get this HTML template?
                </div>
                <img src='dist/assets/logos/CaretRight.svg' alt="" class="transform transition-transform" />
            </div>
            <div answer class="font-montserrat text-sm font-extralight pb-8 hidden">
                You can download it on Gumroad.com
            </div>
            </div>
            <hr class="w-full bg-white" />

            <div toggleElement class="w-full py-4">
            <div class="flex justify-between items-center">
                <div question class="font-montserrat font-medium mr-auto">
                Is this HTML template free?
                </div>
                <img src='dist/assets/logos/CaretRight.svg' alt="" class="transform transition-transform" />
            </div>
            <div answer class="font-montserrat text-sm font-extralight pb-8 hidden">
                Yes! For you it is free.
            </div>
            </div>
            <hr class="w-full bg-white" />

            <div toggleElement class="w-full py-4">
            <div class="flex justify-between items-center">
                <div question class="font-montserrat font-medium mr-auto">
                Am I awesome?
                </div>
                <img src='dist/assets/logos/CaretRight.svg' alt="" class="transform transition-transform" />
            </div>
            <div answer class="font-montserrat text-sm font-extralight pb-8 hidden">
                Yes! No doubt about it.
            </div>
            </div>
            <hr class="w-full bg-white" />

        </section> --}}

        <!-- Footer -->
        <section class="bg-black sectionSize">
            <div class="text-white text-sm text-center py-4 font-extrabold italic">
                <a href="https://danilo-tech.com" target="_blank"
                    class="text-white underline hover:opacity-75 transition duration-300">
                    &copy; <?php echo date('Y'); ?> Danilo tech. All rights reserved
                </a>
            </div>
        </section>
    </body>

</html>
