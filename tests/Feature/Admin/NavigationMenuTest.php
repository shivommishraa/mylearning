<?php

namespace Tests\Feature\Admin;

use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_create_header_and_footer_submenus(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $this->actingAs($superAdmin);

        $this->post(route('admin.menus.store'), [
            'label' => 'Shop sections',
            'url' => '/shop',
            'location' => 'header',
            'sort_order' => 3,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $headerParent = NavigationItem::where('label', 'Shop sections')->firstOrFail();

        $this->post(route('admin.menus.store'), [
            'label' => 'Pantry essentials',
            'url' => '/shop?category=grocery-staples',
            'location' => 'header',
            'parent_id' => $headerParent->id,
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('admin.menus.store'), [
            'label' => 'Customer help',
            'url' => '/contact',
            'location' => 'footer',
            'sort_order' => 2,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $footerParent = NavigationItem::where('label', 'Customer help')->firstOrFail();

        $this->post(route('admin.menus.store'), [
            'label' => 'Delivery details',
            'url' => '/pages/delivery-information',
            'location' => 'footer',
            'parent_id' => $footerParent->id,
            'sort_order' => 0,
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('Shop sections')
            ->assertSee('Pantry essentials')
            ->assertSee('Customer help')
            ->assertSee('Delivery details')
            ->assertSee('data-nav-submenu-toggle', false)
            ->assertSee('footer-submenu', false);

        $headerChild = NavigationItem::where('label', 'Pantry essentials')->firstOrFail();
        $this->post(route('admin.menus.store'), [
            'label' => 'Nested too deeply',
            'url' => '/shop/deep',
            'location' => 'header',
            'parent_id' => $headerChild->id,
            'is_active' => '1',
        ])->assertSessionHasErrors('parent_id');

        $this->put(route('admin.menus.update', $headerChild), [
            'label' => 'Pantry essentials',
            'url' => '/shop?category=grocery-staples',
            'location' => 'header',
            'parent_id' => '',
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($headerChild->fresh()->parent_id);
    }

    public function test_only_enabled_menu_items_under_enabled_parents_are_rendered(): void
    {
        $disabledParent = NavigationItem::create([
            'label' => 'Disabled parent',
            'url' => '/hidden',
            'location' => 'header',
            'is_active' => false,
        ]);
        NavigationItem::create([
            'label' => 'Child of disabled parent',
            'url' => '/hidden/child',
            'location' => 'header',
            'parent_id' => $disabledParent->id,
            'is_active' => true,
        ]);

        $enabledParent = NavigationItem::create([
            'label' => 'Visible parent',
            'url' => '/visible',
            'location' => 'footer',
            'is_active' => true,
        ]);
        NavigationItem::create([
            'label' => 'Visible child',
            'url' => '/visible/child',
            'location' => 'footer',
            'parent_id' => $enabledParent->id,
            'is_active' => true,
        ]);
        NavigationItem::create([
            'label' => 'Disabled child',
            'url' => '/visible/hidden',
            'location' => 'footer',
            'parent_id' => $enabledParent->id,
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Visible parent')
            ->assertSee('Visible child')
            ->assertDontSee('Disabled parent')
            ->assertDontSee('Child of disabled parent')
            ->assertDontSee('Disabled child');
    }

    public function test_admin_menu_actions_require_the_matching_super_admin_granted_permission(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo(Permission::findByName('menus.view', 'web'));

        $this->actingAs($admin)->get(route('admin.menus.index'))
            ->assertOk()
            ->assertDontSee('Add a menu or submenu item');

        $payload = [
            'label' => 'Permission test',
            'url' => '/permission-test',
            'location' => 'header',
            'is_active' => '1',
        ];
        $this->post(route('admin.menus.store'), $payload)->assertForbidden();

        $item = NavigationItem::firstOrFail();
        $this->put(route('admin.menus.update', $item), $payload)->assertForbidden();
        $this->delete(route('admin.menus.destroy', $item))->assertForbidden();

        $admin->givePermissionTo(Permission::findByName('menus.create', 'web'));
        $this->post(route('admin.menus.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $createdItem = NavigationItem::where('label', 'Permission test')->firstOrFail();
        $admin->givePermissionTo(Permission::findByName('menus.edit', 'web'));
        $this->put(route('admin.menus.update', $createdItem), array_merge($payload, ['label' => 'Updated permission test']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $admin->givePermissionTo(Permission::findByName('menus.delete', 'web'));
        $this->delete(route('admin.menus.destroy', $createdItem))->assertRedirect();
        $this->assertDatabaseMissing('navigation_items', ['id' => $createdItem->id]);
    }
}
