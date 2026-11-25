<?php

namespace Tests\Feature\Admin;

use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EnquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_enquiry_deletion_requires_the_granted_permission(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $this->actingAs($superAdmin)
            ->get(route('admin.admins.create'))
            ->assertOk()
            ->assertSee('value="enquiries.delete"', false);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo(Permission::findByName('enquiries.view', 'web'));
        $enquiry = Enquiry::create([
            'name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'phone' => '9876543210',
            'subject' => 'Delivery question',
            'message' => 'Please share delivery timings.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSee('Asha Sharma')
            ->assertDontSee('action="'.route('admin.enquiries.destroy', $enquiry).'"', false);
        $this->delete(route('admin.enquiries.destroy', $enquiry))->assertForbidden();

        $admin->givePermissionTo(Permission::findByName('enquiries.delete', 'web'));
        $this->get(route('admin.enquiries.index'))
            ->assertOk()
            ->assertSee('action="'.route('admin.enquiries.destroy', $enquiry).'"', false);

        $this->from(route('admin.enquiries.index'))
            ->delete(route('admin.enquiries.destroy', $enquiry))
            ->assertRedirect(route('admin.enquiries.index'))
            ->assertSessionHas('success', 'Enquiry deleted.');

        $this->assertDatabaseMissing('enquiries', ['id' => $enquiry->id]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'enquiry.deleted',
            'subject_type' => Enquiry::class,
            'subject_id' => $enquiry->id,
        ]);
    }
}
