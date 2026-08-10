@props(['name', 'show' => false, 'maxWidth' => '2xl', 'closeMethod' => null])

@php
    $maxWidthClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        '6xl' => 'sm:max-w-6xl',
        '7xl' => 'sm:max-w-7xl',

        '75vw' => 'sm:max-w-[75vw]',
        '80vw' => 'sm:max-w-[80vw]',
        '85vw' => 'sm:max-w-[85vw]',
        '90vw' => 'sm:max-w-[90vw]',
        '95vw' => 'sm:max-w-[95vw]',

        'full' => 'sm:max-w-[calc(100vw-2rem)]',
    ];

    $maxWidthClass = $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['2xl'];
@endphp

@once
    <style>
        .app-modal button:not(:disabled),
        .app-modal a[href],
        .app-modal label[for],
        .app-modal select:not(:disabled),
        .app-modal input[type='checkbox']:not(:disabled),
        .app-modal input[type='radio']:not(:disabled),
        .app-modal [role='button']:not([aria-disabled='true']) {
            cursor: pointer;
        }

        .app-modal button:disabled,
        .app-modal [aria-disabled='true'] {
            cursor: not-allowed;
        }
    </style>
@endonce

<div x-data="{
    show: @js($show),
    closeMethod: @js($closeMethod),
    isDismissing: false,

    dismiss(event = null) {
        event?.stopPropagation();

        if (!this.show || this.isDismissing) {
            return;
        }

        this.isDismissing = true;
        this.show = false;

        if (this.closeMethod) {
            Promise.resolve(this.$wire.call(this.closeMethod))
                .finally(() => this.isDismissing = false);
        } else {
            this.isDismissing = false;
        }
    },

    focusables() {
        let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';

        return [...$el.querySelectorAll(selector)]
            .filter(element => !element.hasAttribute('disabled'));
    },

    firstFocusable() {
        return this.focusables()[0];
    },

    lastFocusable() {
        return this.focusables().slice(-1)[0];
    },

    nextFocusable() {
        return this.focusables()[this.nextFocusableIndex()] ||
            this.firstFocusable();
    },

    prevFocusable() {
        return this.focusables()[this.prevFocusableIndex()] ||
            this.lastFocusable();
    },

    nextFocusableIndex() {
        return (
            this.focusables().indexOf(document.activeElement) + 1
        ) % (this.focusables().length + 1);
    },

    prevFocusableIndex() {
        return Math.max(
            0,
            this.focusables().indexOf(document.activeElement)
        ) - 1;
    },
}" x-init="$watch('show', value => {
    if (value) {
        document.body.classList.add('overflow-y-hidden');

        {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
    } else {
        document.body.classList.remove('overflow-y-hidden');
    }
})"
    x-on:open-modal.window="
        if ($event.detail == '{{ $name }}') {
            isDismissing = false;
            show = true;
        }
    "
    x-on:close-modal.window="
        if ($event.detail == '{{ $name }}') {
            show = false;
            isDismissing = false;
        }
    "
    x-on:close.stop="dismiss()" x-on:keydown.escape.window="if (show) dismiss()"
    x-on:keydown.tab.prevent="
        $event.shiftKey || nextFocusable().focus()
    "
    x-on:keydown.shift.tab.prevent="
        prevFocusable().focus()
    " x-show="show"
    class="app-modal fixed inset-0 z-50 overflow-hidden px-2 py-2 sm:px-6 sm:py-6" style="display: {{ $show ? 'block' : 'none' }};">
    {{-- Fondo oscuro --}}
    <div x-show="show" class="fixed inset-0 cursor-pointer transform transition-all"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    {{-- Contenedor para centrar el modal --}}
    <div class="relative flex h-full min-h-0 cursor-pointer items-center justify-center" x-on:click.self="dismiss($event)">

        {{-- Caja principal del modal --}}
        <div x-show="show"
            x-on:click.stop
            class="relative flex max-h-[calc(100dvh-1rem)] w-full cursor-default flex-col overflow-hidden rounded-lg bg-white shadow-xl transform transition-all sm:max-h-[calc(100vh-3rem)] {{ $maxWidthClass }}"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            {{ $slot }}
        </div>

    </div>
</div>
