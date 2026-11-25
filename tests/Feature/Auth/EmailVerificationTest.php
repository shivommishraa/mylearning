<?php

namespace Tests\Feature\Auth;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();
        $this->enableVerification();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();
        $this->enableVerification();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();
        $this->enableVerification();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_customers_are_blocked_only_when_verification_is_enabled(): void
    {
        $user = User::factory()->unverified()->create();
        SiteSetting::create([
            'key' => 'require_email_verification',
            'value' => '1',
            'group' => 'general',
        ]);

        $this->actingAs($user)
            ->get('/account')
            ->assertRedirect(route('verification.notice'));

        SiteSetting::updateOrCreate(
            ['key' => 'require_email_verification'],
            ['value' => '0', 'group' => 'general'],
        );

        $this->get('/account')->assertOk();
    }

    public function test_disabled_verification_disables_prompt_resend_and_signed_verification(): void
    {
        $user = User::factory()->unverified()->create();
        SiteSetting::updateOrCreate(
            ['key' => 'require_email_verification'],
            ['value' => '0', 'group' => 'general'],
        );
        Event::fake();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertRedirect(route('dashboard', absolute: false));
        $this->post('/email/verification-notification')
            ->assertRedirect(route('dashboard', absolute: false));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $this->get($verificationUrl)->assertRedirect(route('dashboard', absolute: false));

        Event::assertNotDispatched(Verified::class);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    private function enableVerification(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'require_email_verification'],
            ['value' => '1', 'group' => 'general'],
        );
    }
}
