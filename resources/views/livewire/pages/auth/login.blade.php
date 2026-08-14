<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        if ($this->form->authenticate()) {
            $this->redirectRoute('two-factor.login', navigate: false);

            return;
        }

        Session::regenerate();

        /*
         * El dashboard utiliza scripts de Livewire Charts que pertenecen al
         * layout autenticado. La redirección SPA desde el layout de invitado
         * no vuelve a cargar esos scripts, por lo que las gráficas aparecen
         * vacías hasta refrescar manualmente.
         */
        $this->redirectIntended(
            default: route('dashboard', absolute: false),
            navigate: false
        );
    }
}; ?>

<div class="space-y-6" x-data="{ showPassword: false }">
    <div class="text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Welcome back</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to continue to your workspace.</p>
    </div>

    <x-auth-session-status class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700" :status="session('status')" />

    <form wire:submit="login" class="space-y-5">
        <div>
            <x-input-label for="email" :value="__('Email address')" class="font-semibold text-slate-700" />
            <x-text-input wire:model="form.email" id="email"
                class="mt-1.5 block w-full px-3 py-2.5 text-slate-900 placeholder:text-slate-400"
                type="email" name="email" required autofocus autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="font-semibold text-slate-700" />
            <div class="relative mt-1.5">
                <x-text-input wire:model="form.password" id="password"
                    class="block w-full py-2.5 pl-3 pr-12 text-slate-900"
                    x-bind:type="showPassword ? 'text' : 'password'" name="password" required
                    autocomplete="current-password" />
                <button type="button" x-on:click="showPassword = ! showPassword"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-md text-slate-500 transition hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                    x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                    x-bind:title="showPassword ? 'Hide password' : 'Show password'">
                    <svg x-show="! showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.1 12s3.6-6 9.9-6 9.9 6 9.9 6-3.6 6-9.9 6-9.9-6-9.9-6Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.1A11.6 11.6 0 0 1 12 5c6.3 0 9.9 7 9.9 7a16 16 0 0 1-2.1 2.9M6.6 6.6C3.6 8.4 2.1 12 2.1 12s3.6 7 9.9 7a10 10 0 0 0 4-.8" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember" class="inline-flex cursor-pointer items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Keep me signed in') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-semibold text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="flex w-full justify-center py-3 text-sm" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">{{ __('Sign in') }}</span>
            <span wire:loading wire:target="login">{{ __('Signing in...') }}</span>
        </x-primary-button>
    </form>
</div>
