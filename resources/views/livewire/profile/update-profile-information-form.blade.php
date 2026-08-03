<?php

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $photo = null;
    public string $locale = 'en';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $preference = Auth::user()->preferences()
            ->where('key', 'locale')
            ->first()?->value;
        $this->locale = is_array($preference)
            ? ($preference['locale'] ?? 'en')
            : ($preference ?: 'en');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();
        $photoWasUpdated = $this->photo !== null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'locale' => ['required', Rule::in(array_keys(config('locales.supported')))],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($this->photo) {
            $newPhotoPath = $this->photo->store('profile-photos', 'public');

            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $newPhotoPath;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        UserPreference::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'locale'],
            ['value' => ['locale' => $validated['locale']]]
        );
        App::setLocale($validated['locale']);

        $this->reset('photo');
        $this->dispatch('profile-updated', name: $user->name);
        $this->dispatch('profile-photo-updated');
        if ($photoWasUpdated) {
            $this->dispatch('profile-photo-saved');
        }
    }

    public function removePhoto(): void
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->forceFill(['profile_photo_path' => null])->save();
        $this->reset('photo');
        $this->dispatch('profile-photo-updated');
        $this->dispatch('profile-photo-removed');
        $this->dispatch('close-modal', 'confirm-profile-photo-removal');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        @php
            $profileFormUser = auth()->user()->fresh();
            $profileInitials = collect(explode(' ', trim($profileFormUser->name)))
                ->filter()
                ->take(2)
                ->map(fn (string $part): string => strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
        @endphp

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border-2 border-white bg-slate-900 shadow">
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="New profile photo"
                            class="h-full w-full object-cover">
                    @elseif ($profileFormUser->profile_photo_path)
                        <img src="{{ $profileFormUser->profilePhotoUrl() }}"
                            alt="{{ $profileFormUser->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="flex h-full w-full items-center justify-center text-lg font-bold text-white">
                            {{ $profileInitials ?: 'U' }}
                        </span>
                    @endif
                    <div wire:loading wire:target="photo"
                        class="absolute inset-0 flex items-center justify-center bg-slate-900/60 text-white">
                        <svg class="h-6 w-6 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
                            <path fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" />
                        </svg>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800">{{ __('Profile photo') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('JPG, PNG or WEBP. Maximum size 2 MB.') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <label for="photo"
                            class="inline-flex h-10 cursor-pointer items-center rounded-lg border border-blue-600 bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition duration-150 hover:-translate-y-px hover:border-blue-500 hover:bg-blue-500 hover:shadow-md">
                            {{ $profileFormUser->profile_photo_path ? __('Change photo') : __('Upload photo') }}
                        </label>
                        <input wire:model="photo" id="photo" type="file"
                            accept="image/jpeg,image/png,image/webp" class="sr-only">

                        @if ($profileFormUser->profile_photo_path)
                            <button type="button" x-data
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-profile-photo-removal')"
                                class="inline-flex h-10 items-center rounded-lg border border-red-200 bg-white px-4 text-sm font-semibold text-red-600 shadow-sm transition duration-150 hover:-translate-y-px hover:border-red-300 hover:bg-red-50 hover:shadow-md">
                                {{ __('Remove') }}
                            </button>
                        @endif
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                    <div class="mt-3">
                        <x-action-message on="profile-photo-saved"
                            class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 font-medium text-emerald-700">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-xs text-white">✓</span>
                            {{ __('Profile photo updated successfully.') }}
                        </x-action-message>
                        <x-action-message on="profile-photo-removed"
                            class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 font-medium text-blue-700">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white">✓</span>
                            {{ __('Profile photo removed successfully.') }}
                        </x-action-message>
                    </div>
                </div>
            </div>
        </div>

        <x-modal name="confirm-profile-photo-removal" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 4.7 2.8 17.5A1.75 1.75 0 0 0 4.3 20h15.4a1.75 1.75 0 0 0 1.5-2.5L13.7 4.7a1.95 1.95 0 0 0-3.4 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Remove profile photo?') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ __('Your current photo will be permanently removed. Your initials will be shown instead.') }}</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <button type="button" wire:click="removePhoto" wire:loading.attr="disabled" wire:target="removePhoto"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="removePhoto">{{ __('Remove photo') }}</span>
                        <span wire:loading wire:target="removePhoto">{{ __('Removing...') }}</span>
                    </button>
                </div>
            </div>
        </x-modal>
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="locale" :value="__('Language')" />
            <select wire:model="locale" id="locale"
                class="mt-1 block w-full cursor-pointer rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach (config('locales.supported') as $localeCode => $localeLabel)
                    <option value="{{ $localeCode }}">{{ __($localeLabel) }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">{{ __('Your language preference is saved to your profile.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('locale')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button wire:click.prevent="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
