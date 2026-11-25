<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSee(route('register'))
            ->assertSee('Email or mobile number')
            ->assertSee('Come on in')
            ->assertSee('A few helpful links');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_their_mobile_number(): void
    {
        $user = User::factory()->create(['email' => null, 'phone' => '9876543210']);

        $response = $this->post('/login', [
            'login' => '9876543210',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_admins_cannot_authenticate_through_the_customer_login(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->assignRole('Admin');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_admins_can_authenticate_through_the_admin_login(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->assignRole('Admin');

        $this->get('/admin/login')->assertOk();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_customers_cannot_authenticate_through_the_admin_login(): void
    {
        $customer = User::factory()->create();

        $response = $this->post('/admin/login', [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_guests_are_redirected_to_the_admin_login_for_admin_pages(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_admins_can_log_out_from_the_admin_panel(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->post('/admin/logout');

        $this->assertGuest();
        $response->assertRedirect(route('admin.login'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
