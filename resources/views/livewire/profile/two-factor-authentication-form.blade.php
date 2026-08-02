<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Volt\Component;

new class extends Component
{
    public string $currentPassword = '';
    public string $confirmationCode = '';
    public bool $showRecoveryCodes = false;

    public function enable(EnableTwoFactorAuthentication $enable): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
        ]);

        $enable(Auth::user());
        $this->reset('currentPassword', 'confirmationCode');
        $this->dispatch('mfa-setup-started');
    }

    public function confirm(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->validate([
            'confirmationCode' => ['required', 'digits:6'],
        ]);

        try {
            $confirm(Auth::user(), $this->confirmationCode);
        } catch (ValidationException $exception) {
            $this->addError(
                'confirmationCode',
                $exception->validator->errors()->first('code'),
            );

            return;
        }

        $this->reset('confirmationCode');
        $this->showRecoveryCodes = true;
        $this->dispatch('mfa-confirmed');
    }

    public function disable(DisableTwoFactorAuthentication $disable): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
        ]);

        $disable(Auth::user());
        $this->reset();
        $this->dispatch('mfa-disabled');
    }

    public function regenerate(GenerateNewRecoveryCodes $generate): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
        ]);

        $generate(Auth::user());
        $this->reset('currentPassword');
        $this->showRecoveryCodes = true;
        $this->dispatch('recovery-codes-regenerated');
    }
}; ?>

@php
    $user = auth()->user()->fresh();
    $isEnabled = $user->hasEnabledTwoFactorAuthentication();
    $isPending = filled($user->two_factor_secret) && ! $isEnabled;
@endphp

<section>
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-slate-900">Multi-factor authentication</h2>
                <span @class([
                    'inline-flex rounded-full px-2.5 py-1 text-xs font-bold',
                    'bg-emerald-50 text-emerald-700' => $isEnabled,
                    'bg-amber-50 text-amber-700' => $isPending,
                    'bg-slate-100 text-slate-600' => ! $isEnabled && ! $isPending,
                ])>
                    {{ $isEnabled ? 'Enabled' : ($isPending ? 'Pending confirmation' : 'Disabled') }}
                </span>
            </div>
            <p class="mt-1 text-sm leading-6 text-slate-500">
                Protect your account with a time-based code from your authenticator app.
            </p>
        </div>
        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3 4.5 6v5.25c0 4.77 3.22 8.66 7.5 9.75 4.28-1.09 7.5-4.98 7.5-9.75V6L12 3Zm-2.25 9 1.5 1.5 3-3" />
            </svg>
        </span>
    </div>

    @if (! $isEnabled && ! $isPending)
        <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50/60 p-4 text-sm text-blue-900">
            You will need an authenticator app such as Microsoft Authenticator, Google Authenticator or 1Password.
        </div>

        <form wire:submit="enable" class="mt-5 space-y-4">
            <div>
                <x-input-label for="mfa_current_password_enable" value="Current password" />
                <x-text-input wire:model="currentPassword" id="mfa_current_password_enable"
                    type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('currentPassword')" class="mt-2" />
            </div>
            <x-primary-button>Enable MFA</x-primary-button>
        </form>
    @elseif ($isPending)
        <div class="mt-6 grid gap-6 lg:grid-cols-[220px_1fr]">
            <div class="flex items-center justify-center rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="overflow-hidden rounded-lg">{!! $user->twoFactorQrCodeSvg() !!}</div>
            </div>
            <div>
                <ol class="space-y-3 text-sm text-slate-600">
                    <li><strong class="text-slate-800">1.</strong> Scan the QR code with your authenticator app.</li>
                    <li><strong class="text-slate-800">2.</strong> Enter the generated 6-digit code below.</li>
                    <li><strong class="text-slate-800">3.</strong> Save the recovery codes after confirmation.</li>
                </ol>

                <form wire:submit="confirm" class="mt-5">
                    <x-input-label for="mfa_confirmation_code" value="Authentication code" />
                    <x-text-input wire:model="confirmationCode" id="mfa_confirmation_code"
                        type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code"
                        class="mt-1 block w-full text-center text-lg tracking-[0.3em]" />
                    <x-input-error :messages="$errors->get('confirmationCode')" class="mt-2" />
                    <x-primary-button class="mt-4">Confirm and activate</x-primary-button>
                </form>

                <form wire:submit="disable" class="mt-5 border-t border-slate-200 pt-5">
                    <x-input-label for="mfa_current_password_cancel" value="Current password to cancel setup" />
                    <x-text-input wire:model="currentPassword" id="mfa_current_password_cancel"
                        type="password" class="mt-1 block w-full" autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('currentPassword')" class="mt-2" />
                    <x-secondary-button type="submit" class="mt-3">Cancel setup</x-secondary-button>
                </form>
            </div>
        </div>
    @else
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-semibold text-emerald-800">Your account is protected with MFA.</p>
            <p class="mt-1 text-xs text-emerald-700">
                You will be asked for an authentication code the next time you sign in.
            </p>
        </div>

        <div class="mt-5 flex flex-wrap gap-3">
            <button type="button" wire:click="$toggle('showRecoveryCodes')"
                class="inline-flex h-10 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition duration-150 hover:-translate-y-px hover:border-slate-400 hover:bg-slate-50 hover:shadow-md">
                {{ $showRecoveryCodes ? 'Hide recovery codes' : 'Show recovery codes' }}
            </button>
        </div>

        @if ($showRecoveryCodes)
            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-5">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.3 4.4 2.6 18a1.5 1.5 0 0 0 1.3 2.25h16.2A1.5 1.5 0 0 0 21.4 18L13.7 4.4a1.95 1.95 0 0 0-3.4 0Z" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold text-amber-900">Store these codes in a secure place</p>
                        <p class="mt-1 text-xs text-amber-800">Each code can only be used once.</p>
                    </div>
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($user->recoveryCodes() as $recoveryCode)
                        <code class="rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm text-slate-700">
                            {{ $recoveryCode }}
                        </code>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 grid gap-5 border-t border-slate-200 pt-6 md:grid-cols-2">
            <form wire:submit="regenerate" class="space-y-3">
                <x-input-label for="mfa_current_password_regenerate" value="Current password" />
                <x-text-input wire:model="currentPassword" id="mfa_current_password_regenerate"
                    type="password" class="block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('currentPassword')" />
                <x-secondary-button type="submit">Generate new recovery codes</x-secondary-button>
            </form>

            <form wire:submit="disable" class="space-y-3">
                <x-input-label for="mfa_current_password_disable" value="Current password" />
                <x-text-input wire:model="currentPassword" id="mfa_current_password_disable"
                    type="password" class="block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('currentPassword')" />
                <x-danger-button>Disable MFA</x-danger-button>
            </form>
        </div>
    @endif

    <x-action-message class="mt-4" on="mfa-confirmed">MFA enabled successfully.</x-action-message>
    <x-action-message class="mt-4" on="mfa-disabled">MFA disabled.</x-action-message>
    <x-action-message class="mt-4" on="recovery-codes-regenerated">Recovery codes regenerated.</x-action-message>
</section>
