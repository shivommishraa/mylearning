<?php

namespace Tests\Feature\Auth;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200)
            ->assertSee('अन्य विवरण भरना चाहते हैं?')
            ->assertSee('Password')
            ->assertSee('Confirm password')
            ->assertSee('Come on in')
            ->assertDontSee('A few helpful links');
    }

    public function test_storefront_displays_a_registration_link_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee(route('register'));
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertNull(auth()->user()->profile_photo_path);
        $this->assertDatabaseMissing('addresses', ['user_id' => auth()->id()]);
        Notification::assertNothingSent();
    }

    public function test_registration_can_save_optional_profile_photo_and_address(): void
    {
        Notification::fake();
        Storage::fake('local');

        $response = $this->post('/register', [
            'name' => 'Asha Customer',
            'email' => 'asha.profile@example.com',
            'phone' => '9876543210',
            'additional_details' => '1',
            'father_husband_name' => 'Ramesh Sharma',
            'date_of_birth' => '1990-04-15',
            'gender' => 'female',
            'profile_photo' => UploadedFile::fake()->image('asha.png'),
            'address_line' => '12 Green Avenue',
            'address_phone' => '9876543211',
            'city' => 'Greater Noida',
            'state' => 'Uttar Pradesh',
            'postal_code' => '201318',
            'landmark' => 'Near the park',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'asha.profile@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('local')->assertExists($user->profile_photo_path);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'address_line' => '12 Green Avenue',
            'phone' => '9876543211',
            'city' => 'Greater Noida',
            'is_default' => true,
        ]);
        $this->assertSame('Ramesh Sharma', $user->father_husband_name);
        $this->assertSame('female', $user->gender);
        $this->get(route('profile.photo', $user))->assertOk();
        $this->get(route('media.show', ['path' => $user->profile_photo_path]))->assertNotFound();
        Notification::assertNothingSent();
    }

    public function test_phone_only_registration_without_additional_details_is_allowed_when_email_verification_is_enabled(): void
    {
        Notification::fake();
        SiteSetting::create([
            'key' => 'require_email_verification',
            'value' => '1',
            'group' => 'general',
        ]);

        $response = $this->post('/register', [
            'name' => 'Mobile Customer',
            'phone' => '9876543299',
            'additional_details' => '0',
            'father_husband_name' => 'Should not be saved',
            'date_of_birth' => 'not-a-date',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('phone', '9876543299')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertNull($user->email);
        $this->assertNull($user->father_husband_name);
        $this->assertNull($user->date_of_birth);
        $this->assertDatabaseMissing('addresses', ['user_id' => $user->id]);
        $this->get(route('account'))->assertOk();
        Notification::assertNothingSent();
    }

    public function test_registration_requires_either_email_or_mobile_number(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'No Contact',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors(['email', 'phone']);

        $this->assertDatabaseMissing('users', ['name' => 'No Contact']);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Mismatch Customer',
            'phone' => '9876543212',
            'password' => 'password-one',
            'password_confirmation' => 'password-two',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['phone' => '9876543212']);
    }

    public function test_registration_requires_email_verification_when_enabled(): void
    {
        Notification::fake();
        SiteSetting::create([
            'key' => 'require_email_verification',
            'value' => '1',
            'group' => 'general',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'verified@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'verified@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('verification.notice'));
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
