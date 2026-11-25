<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WelcomePopup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WelcomePopupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_create_and_enable_a_multi_image_welcome_popup(): void
    {
        Storage::fake('public');
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();

        $this->actingAs($superAdmin)->get(route('admin.welcome-popup.index'))
            ->assertOk()
            ->assertSee('Only image')
            ->assertSee('Only content + buttons')
            ->assertSee('Add a button');

        $this->actingAs($superAdmin)->post(route('admin.welcome-popup.store'), [
            'display_mode' => 'image_content_buttons',
            'title' => 'Welcome to SSG MART',
            'description' => 'Shop neighbourhood favourites.',
            'image_position' => 'left',
            'content_alignment' => 'center',
            'button_style' => 'outline',
            'accent_color' => '#246b45',
            'show_close_button' => '1',
            'image_files' => [
                UploadedFile::fake()->image('welcome-one.png', 640, 480),
                UploadedFile::fake()->image('welcome-two.png', 640, 480),
            ],
            'buttons' => [
                ['text' => 'Shop now', 'url' => '/shop', 'target' => '_self'],
                ['text' => 'Explore offers', 'url' => 'https://example.com/offers', 'target' => '_blank'],
            ],
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        $this->get(route('admin.welcome-popup.index'))
            ->assertOk()
            ->assertSee('Only once on this browser (until site data is cleared)');

        $popup = WelcomePopup::with(['images', 'buttons'])->findOrFail(1);
        $this->assertFalse($popup->is_enabled);
        $this->assertCount(2, $popup->images);
        $this->assertCount(2, $popup->buttons);
        Storage::disk('public')->assertExists($popup->images[0]->path);
        $this->get('/media/'.$popup->images[0]->path)->assertOk();
        $this->get('/')->assertDontSee('data-welcome-popup', false);

        $removedImagePath = $popup->images[0]->path;
        $this->put(route('admin.welcome-popup.update'), [
            'display_mode' => 'image_content_buttons',
            'title' => 'Welcome to SSG MART, neighbour!',
            'description' => 'Shop neighbourhood favourites.',
            'image_position' => 'right',
            'content_alignment' => 'center',
            'button_style' => 'outline',
            'accent_color' => '#246b45',
            'show_close_button' => '1',
            'remove_images' => [$popup->images[0]->id],
            'image_files' => [UploadedFile::fake()->image('welcome-three.png', 640, 480)],
            'buttons' => [
                ['id' => $popup->buttons[0]->id, 'text' => 'Shop now', 'url' => '/shop', 'target' => '_self'],
                ['id' => $popup->buttons[1]->id, 'text' => 'Explore offers', 'url' => 'https://example.com/offers', 'target' => '_blank'],
            ],
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($removedImagePath);
        $popup = WelcomePopup::with(['images', 'buttons'])->findOrFail(1);
        $this->assertCount(2, $popup->images);
        $this->assertSame('Welcome to SSG MART, neighbour!', $popup->title);

        $this->put(route('admin.welcome-popup.display'), [
            'is_enabled' => '1',
            'display_frequency' => 'once_per_visitor',
            'display_delay' => '0',
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('data-page-loader', false)
            ->assertSee('data-back-to-top', false)
            ->assertSee('aria-label="Scroll to top"', false)
            ->assertSee('data-welcome-popup', false)
            ->assertSee('data-display-frequency="once_per_visitor"', false)
            ->assertSee('Welcome to SSG MART, neighbour!')
            ->assertSee('Shop neighbourhood favourites.')
            ->assertSee('Shop now')
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('welcome-popup-image-dot', false);

        $imagePaths = $popup->images->pluck('path')->all();
        $this->delete(route('admin.welcome-popup.destroy'))
            ->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('welcome_popups', ['id' => 1]);
        foreach ($imagePaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_welcome_popup_modes_hide_unselected_content_and_buttons(): void
    {
        Storage::fake('public');
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $this->actingAs($superAdmin);

        $this->post(route('admin.welcome-popup.store'), [
            'display_mode' => 'image_only',
            'image_position' => 'left',
            'content_alignment' => 'left',
            'button_style' => 'solid',
            'accent_color' => '#246b45',
            'show_close_button' => '1',
            'image_files' => [UploadedFile::fake()->image('welcome.png')],
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        $this->put(route('admin.welcome-popup.display'), [
            'is_enabled' => '1',
            'display_frequency' => 'every_visit',
            'display_delay' => '0',
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('welcome-popup--image-only', false)
            ->assertDontSee('welcome-popup-copy', false)
            ->assertDontSee('welcome-popup-actions', false);

        $popup = WelcomePopup::with('images')->findOrFail(1);
        $this->put(route('admin.welcome-popup.update'), [
            'display_mode' => 'image_content',
            'title' => 'Image and content mode',
            'description' => 'This mode does not show buttons.',
            'image_position' => 'top',
            'content_alignment' => 'left',
            'button_style' => 'solid',
            'accent_color' => '#246b45',
            'show_close_button' => '1',
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('welcome-popup--image-content', false)
            ->assertSee('Image and content mode')
            ->assertSee('welcome-popup-visual', false)
            ->assertDontSee('welcome-popup-actions', false);

        $this->put(route('admin.welcome-popup.update'), [
            'display_mode' => 'content_buttons',
            'title' => 'Content and buttons mode',
            'description' => 'No image in this mode.',
            'image_position' => 'left',
            'content_alignment' => 'center',
            'button_style' => 'outline',
            'accent_color' => '#246b45',
            'show_close_button' => '1',
            'remove_images' => [$popup->images[0]->id],
            'buttons' => [
                ['text' => 'Continue shopping', 'url' => '/shop', 'target' => '_self'],
            ],
        ])->assertRedirect(route('admin.welcome-popup.index'))
            ->assertSessionHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('welcome-popup--content-buttons', false)
            ->assertSee('Content and buttons mode')
            ->assertSee('Continue shopping')
            ->assertDontSee('welcome-popup-visual', false);
    }

    public function test_admin_requires_feature_permissions_for_menu_and_direct_routes(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $this->actingAs($superAdmin)->get(route('admin.admins.create'))
            ->assertOk()
            ->assertSee('welcome_popup.view')
            ->assertSee('welcome_popup.create')
            ->assertSee('welcome_popup.manage');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo(Permission::findByName('dashboard.view', 'web'));

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Welcome popup');
        $this->get(route('admin.welcome-popup.index'))->assertForbidden();
        $this->post(route('admin.welcome-popup.store'), [])->assertForbidden();

        $admin->givePermissionTo(Permission::findByName('welcome_popup.view', 'web'));
        $this->get(route('admin.welcome-popup.index'))
            ->assertOk()
            ->assertSee('Welcome popup')
            ->assertDontSee('Create your welcome popup');
        $this->post(route('admin.welcome-popup.store'), [])->assertForbidden();
        $this->put(route('admin.welcome-popup.update'), [])->assertForbidden();
        $this->put(route('admin.welcome-popup.display'), [])->assertForbidden();
        $this->delete(route('admin.welcome-popup.destroy'))->assertForbidden();
    }
}
