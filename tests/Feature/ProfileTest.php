<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_update_profile_photo_and_primary_address(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('profiles/previous.jpg', 'previous photo');
        $user = User::factory()->create(['profile_photo_path' => 'profiles/previous.jpg']);
        $address = $user->addresses()->create([
            'label' => 'Home',
            'recipient_name' => $user->name,
            'phone' => '1111111111',
            'address_line' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'postal_code' => '100000',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '9876543210',
            'profile_photo' => UploadedFile::fake()->image('new-photo.jpg'),
            'address_line' => '22 New Road',
            'address_phone' => '9876543211',
            'city' => 'Greater Noida',
            'state' => 'Uttar Pradesh',
            'postal_code' => '201318',
            'landmark' => 'Near the market',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $user->refresh();
        $this->assertNotSame('profiles/previous.jpg', $user->profile_photo_path);
        Storage::disk('local')->assertExists($user->profile_photo_path);
        Storage::disk('local')->assertMissing('profiles/previous.jpg');
        $this->get(route('profile.photo', $user))->assertOk();
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->get(route('profile.photo', $user))->assertForbidden();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'address_line' => '22 New Road',
            'phone' => '9876543211',
            'city' => 'Greater Noida',
            'is_default' => true,
        ]);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
