<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_create_search_view_update_and_delete_customers(): void
    {
        Notification::fake();
        SiteSetting::updateOrCreate(
            ['key' => 'require_email_verification'],
            ['value' => '1', 'group' => 'general'],
        );
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('Search customers by name, email or phone')
            ->assertSee('Add a customer');
        $this->get(route('admin.customers.create'))
            ->assertOk()
            ->assertSee('Confirm password');

        $this->post(route('admin.customers.store'), [
            'name' => 'Asha Customer',
            'email' => 'asha.customer@example.com',
            'phone' => '9876543210',
            'password' => 'CustomerPass123!',
            'password_confirmation' => 'CustomerPass123!',
        ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $customer = User::where('email', 'asha.customer@example.com')->firstOrFail();
        $this->assertNull($customer->email_verified_at);
        Notification::assertSentTo($customer, VerifyEmail::class);

        $this->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('Asha Customer')
            ->assertSee('asha.customer@example.com')
            ->assertSee('Pending');

        $this->get(route('admin.customers.index', ['q' => 'asha.customer@example.com']))
            ->assertOk()
            ->assertSee('asha.customer@example.com')
            ->assertDontSee('No customers match your search.');

        $this->put(route('admin.customers.update', $customer), [
            'name' => 'Asha Sharma',
            'email' => 'asha.sharma@example.com',
            'phone' => '9876543211',
        ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Asha Sharma',
            'email' => 'asha.sharma@example.com',
            'phone' => '9876543211',
            'email_verified_at' => null,
        ]);
        $this->get(route('admin.customers.index', ['q' => '9876543211']))
            ->assertOk()
            ->assertSee('asha.sharma@example.com');

        $this->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }

    public function test_customer_actions_require_the_matching_permissions_and_staff_accounts_are_not_customers(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo(Permission::findByName('customers.view', 'web'));
        $customer = User::factory()->create();
        $staff = User::factory()->create();
        $staff->assignRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee($customer->email)
            ->assertDontSee($staff->email);
        $this->get(route('admin.customers.show', $customer))->assertOk();
        $this->get(route('admin.customers.show', $staff))->assertNotFound();
        $this->get(route('admin.customers.create'))->assertForbidden();
        $this->post(route('admin.customers.store'), [])->assertForbidden();
        $this->get(route('admin.customers.edit', $customer))->assertForbidden();
        $this->put(route('admin.customers.update', $customer), [])->assertForbidden();
        $this->delete(route('admin.customers.destroy', $customer))->assertForbidden();

        $admin->givePermissionTo(Permission::findByName('customers.create', 'web'));
        $this->get(route('admin.customers.create'))->assertOk();
        $this->post(route('admin.customers.store'), [
            'name' => 'Permission Customer',
            'email' => 'permission.customer@example.com',
            'password' => 'CustomerPass123!',
            'password_confirmation' => 'CustomerPass123!',
        ])->assertRedirect()
            ->assertSessionHasNoErrors();
        $created = User::where('email', 'permission.customer@example.com')->firstOrFail();

        $admin->givePermissionTo(Permission::findByName('customers.edit', 'web'));
        $this->get(route('admin.customers.edit', $created))->assertOk();
        $this->put(route('admin.customers.update', $created), [
            'name' => 'Updated Permission Customer',
            'email' => $created->email,
        ])->assertRedirect()
            ->assertSessionHasNoErrors();

        $admin->givePermissionTo(Permission::findByName('customers.delete', 'web'));
        $this->delete(route('admin.customers.destroy', $created))->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $created->id]);
    }

    public function test_customers_with_order_history_cannot_be_deleted(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $customer = User::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'TEST-CUSTOMER-ORDER',
            'delivery_address' => ['recipient_name' => $customer->name],
            'subtotal' => 100,
            'total' => 100,
        ]);

        $this->actingAs($superAdmin)
            ->from(route('admin.customers.show', $customer))
            ->delete(route('admin.customers.destroy', $customer))
            ->assertRedirect(route('admin.customers.show', $customer))
            ->assertSessionHas('error', 'This customer has order history and cannot be deleted.');

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $customer->id]);
    }
}
