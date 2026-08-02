<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use Livewire\Volt\Volt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_mfa_setup_from_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Volt::test('profile.two-factor-authentication-form')
            ->set('currentPassword', 'password')
            ->call('enable')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_confirmed_mfa_redirects_login_to_challenge_and_accepts_totp(): void
    {
        $user = User::factory()->create();
        app(EnableTwoFactorAuthentication::class)($user);
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.login', absolute: false));

        $this->assertGuest();
        $this->assertSame($user->getKey(), session('login.id'));

        $secret = Fortify::currentEncrypter()->decrypt(
            $user->fresh()->two_factor_secret,
        );
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->post(route('two-factor.login.store'), ['code' => $code])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_profile_page_displays_security_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Multi-factor authentication')
            ->assertSee('Account settings');
    }
}
