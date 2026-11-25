<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailVerificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enable_customer_email_verification(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'settings.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'settings.edit', 'guard_name' => 'web']),
            Permission::findByName('email_verification.view', 'web'),
            Permission::findByName('email_verification.edit', 'web'),
        ]);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Require email verification')
            ->assertSee('Footer tagline')
            ->assertSee('Footer note')
            ->assertSee('Footer note URL (optional)');

        $response = $this->put('/admin/settings', [
            'business_name' => 'Test Store',
            'business_type' => 'Grocery Store',
            'phone' => '1234567890',
            'email' => 'store@example.com',
            'address' => 'Main Street',
            'whatsapp' => '1234567890',
            'delivery_fee' => '39',
            'free_delivery_minimum' => '499',
            'require_email_verification' => '1',
            'announcement_text' => 'Welcome to the store',
            'seo_title' => '',
            'seo_description' => '',
            'hero_title' => '',
            'hero_subtitle' => '',
            'footer_tagline' => 'Everyday shopping, made simple.',
            'footer_note' => 'Proudly serving Greater Noida',
            'footer_note_url' => 'https://example.com/neighbourhood',
        ]);

        $response->assertRedirect('/admin/settings');
        $this->assertDatabaseMissing('site_settings', ['key' => 'require_email_verification']);
        $this->put('/admin/settings/email-verification', ['require_email_verification' => '1'])
            ->assertRedirect('/admin/settings');
        $this->assertDatabaseHas('site_settings', [
            'key' => 'require_email_verification',
            'value' => '1',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer_tagline',
            'value' => 'Everyday shopping, made simple.',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer_note',
            'value' => 'Proudly serving Greater Noida',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer_note_url',
            'value' => 'https://example.com/neighbourhood',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Everyday shopping, made simple.')
            ->assertSee('<a href="https://example.com/neighbourhood">Proudly serving Greater Noida</a>', false);
    }

    public function test_email_verification_setting_requires_its_own_edit_permission(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'settings.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'settings.edit', 'guard_name' => 'web']),
            Permission::findByName('email_verification.view', 'web'),
        ]);
        $editPermission = Permission::findByName('email_verification.edit', 'web');
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Customer account verification')
            ->assertSee('Disabled')
            ->assertDontSee('name="require_email_verification"', false)
            ->assertDontSee('Save verification setting');
        $this->put('/admin/settings/email-verification', ['require_email_verification' => '1'])
            ->assertForbidden();

        $admin->givePermissionTo($editPermission);
        $this->put('/admin/settings/email-verification', ['require_email_verification' => '1'])
            ->assertRedirect('/admin/settings');
        $this->assertDatabaseHas('site_settings', [
            'key' => 'require_email_verification',
            'value' => '1',
        ]);
    }
}
