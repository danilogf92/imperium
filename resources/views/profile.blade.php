<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Account settings</h2>
            <p class="mt-1 text-sm text-slate-500">Manage your personal information and account security.</p>
        </div>
    </x-slot>

    @php
        $profileUser = auth()->user();
        $initials = collect(explode(' ', trim($profileUser->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
        $mfaEnabled = $profileUser->hasEnabledTwoFactorAuthentication();
    @endphp

    <div class="bg-slate-50 py-8"
        x-data
        x-on:profile-photo-updated.window="window.location.reload()">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="module-accent-line relative isolate overflow-hidden rounded-2xl bg-sky-600 shadow-lg">

                <div class="relative flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 flex-col items-center gap-5 text-center sm:flex-row sm:text-left">
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-4 border-white/90 bg-slate-800 text-2xl font-bold text-white shadow-xl ring-1 ring-white/30">
                            @if ($profileUser->profile_photo_path)
                                <img src="{{ $profileUser->profilePhotoUrl() }}"
                                    alt="{{ $profileUser->name }}" class="h-full w-full object-cover">
                            @else
                                {{ $initials ?: 'U' }}
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200">Personal profile</p>
                            <h1 class="mt-1 truncate text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                {{ $profileUser->name }}
                            </h1>
                            <p class="mt-1 truncate text-sm text-blue-100/80">{{ $profileUser->email }}</p>
                            @if ($profileUser->area)
                                <p class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-blue-100/70">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 21h16.5M5.25 21V5.25A2.25 2.25 0 0 1 7.5 3h9a2.25 2.25 0 0 1 2.25 2.25V21M9 7.5h.008v.008H9V7.5Zm0 4.5h.008v.008H9V12Zm0 4.5h.008v.008H9V16.5Zm6-9h.008v.008H15V7.5Zm0 4.5h.008v.008H15V12Zm0 4.5h.008v.008H15V16.5Z" />
                                    </svg>
                                    {{ $profileUser->area->name }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-2 lg:justify-end">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white shadow-sm backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 ring-2 ring-emerald-400/20"></span>
                            Active account
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white shadow-sm backdrop-blur">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.25c0 4.77 3.22 8.66 7.5 9.75 4.28-1.09 7.5-4.98 7.5-9.75V6L12 3Z" />
                            </svg>
                            MFA {{ $mfaEnabled ? 'enabled' : 'disabled' }}
                        </span>
                    </div>
                </div>
            </section>

            <div class="grid items-start gap-6 xl:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <livewire:profile.update-profile-information-form />
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <livewire:profile.update-password-form />
                </section>
            </div>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <livewire:profile.two-factor-authentication-form />
            </section>

            <section class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm sm:p-8">
                <livewire:profile.delete-user-form />
            </section>
        </div>
    </div>
</x-app-layout>
