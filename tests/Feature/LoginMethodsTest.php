<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class LoginMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_still_exposes_otp_and_google_actions(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('action="'.route('login.otp.email').'"', false);
        $response->assertSee('action="'.route('login.google.redirect').'"', false);
    }

    public function test_otp_login_flow_still_works(): void
    {
        $user = $this->createUser([
            'nama' => 'User OTP',
            'email' => 'otp@example.test',
        ]);

        $this->post(route('login.otp.request'), [
            'otp_email' => 'otp@example.test',
        ])->assertRedirect(route('login.otp.verify.form'));

        $this->withSession([
            'otp_login_email' => 'otp@example.test',
            'otp_login_user_id' => $user->id_user,
            'otp_send_on_verify' => true,
        ])->get(route('login.otp.verify.form'))
            ->assertOk();

        $otpRecord = DB::table('login_otps')
            ->where('email', 'otp@example.test')
            ->latest('id')
            ->first();

        $this->assertNotNull($otpRecord);

        DB::table('login_otps')
            ->where('id', $otpRecord->id)
            ->update([
                'otp_code' => Hash::make('123456'),
                'expires_at' => now()->addMinutes(5),
                'used_at' => null,
                'updated_at' => now(),
            ]);

        $this->post(route('login.otp.verify'), [
            'otp_email' => 'otp@example.test',
            'otp_code' => '123456',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(session('logged_in'));
        $this->assertSame($user->id_user, session('user.id_user'));
    }

    public function test_google_redirect_route_still_works(): void
    {
        Config::set('services.google.client_id', 'test-client');
        Config::set('services.google.client_secret', 'test-secret');
        Config::set('services.google.redirect', 'http://127.0.0.1:8000/auth/google/callback');

        Socialite::shouldReceive('driver')->once()->with('google')->andReturnSelf();
        Socialite::shouldReceive('with')->once()->with([
            'prompt' => 'select_account',
        ])->andReturnSelf();
        Socialite::shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $this->get(route('login.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_callback_can_log_in_existing_user(): void
    {
        Config::set('services.google.client_id', 'test-client');
        Config::set('services.google.client_secret', 'test-secret');
        Config::set('services.google.redirect', 'http://127.0.0.1:8000/auth/google/callback');

        $user = $this->createUser([
            'nama' => 'User Google',
            'email' => 'google@example.test',
        ]);

        $googleUser = Mockery::mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getId')->once()->andReturn('google-user-123');
        $googleUser->shouldReceive('getEmail')->once()->andReturn('google@example.test');
        $googleUser->shouldReceive('getName')->once()->andReturn('User Google');
        $googleUser->shouldReceive('getAvatar')->once()->andReturn('https://example.test/avatar.png');

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(session('logged_in'));
        $this->assertSame($user->id_user, session('user.id_user'));
        $this->assertDatabaseHas('social_accounts', [
            'id_user' => $user->id_user,
            'provider' => 'google',
            'provider_id' => 'google-user-123',
            'provider_email' => 'google@example.test',
        ]);
    }

    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => (string) fake()->unique()->numerify('####'),
            'nama' => 'User Test',
            'email' => fake()->unique()->safeEmail(),
            'otp_enabled' => false,
            'password' => 'secret123',
            'level' => 1,
            'kelas' => null,
        ], $overrides));
    }
}
