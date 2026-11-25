<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_storefront_renders_seeded_catalogue_and_search_metadata(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SSG MART')
            ->assertSee('Everyday essentials for your home')
            ->assertSee('Everyday essentials, picked for you')
            ->assertSee('Aashirvaad Shudh Chakki Atta')
            ->assertSee('Shakti Bhog Chakki Atta')
            ->assertSee('data-testimonial-carousel', false)
            ->assertSee('aria-roledescription="slide"', false)
            ->assertSee('data-testimonial-next', false)
            ->assertDontSee('FRESH10')
            ->assertDontSee('Fresh Produce')
            ->assertSee('application/ld+json');

        $this->get('/shop?q=Gur')
            ->assertOk()
            ->assertSee('Muzaffarnagar Gur Powder');

        $this->get('/shop?q=noodles')
            ->assertOk()
            ->assertSee('Maggi 2-Minute Masala Noodles')
            ->assertDontSee('Harpic Power Plus Toilet Cleaner');
    }

    public function test_global_search_suggestions_return_matching_active_products(): void
    {
        $product = Product::where('name', 'like', '%Maggi 2-Minute%')->firstOrFail();

        $this->getJson(route('search.suggest', ['q' => 'Maggi']))
            ->assertOk()
            ->assertJsonPath('search_url', route('shop.index', ['q' => 'Maggi']))
            ->assertJsonFragment([
                'name' => $product->name,
                'url' => route('products.show', $product->slug),
            ]);

        $this->getJson(route('search.suggest', ['q' => 'x']))
            ->assertOk()
            ->assertJsonCount(0, 'products');
    }

    public function test_multiple_active_banners_render_as_ordered_slides(): void
    {
        Banner::query()->delete();

        foreach ([
            ['title' => 'First carousel banner', 'sort_order' => 1, 'is_active' => true],
            ['title' => 'Second carousel banner', 'sort_order' => 2, 'is_active' => true],
            ['title' => 'Third carousel banner', 'sort_order' => 3, 'is_active' => true],
            ['title' => 'Hidden carousel banner', 'sort_order' => 0, 'is_active' => false],
        ] as $banner) {
            Banner::create($banner);
        }

        $response = $this->get('/');
        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('data-banner-slider')
            ->assertSee('data-banner-prev')
            ->assertSee('data-banner-next')
            ->assertSee('First carousel banner')
            ->assertSee('Second carousel banner')
            ->assertSee('Third carousel banner')
            ->assertDontSee('Hidden carousel banner');

        $this->assertSame(3, substr_count($content, ' data-banner-slide '));
        $this->assertLessThan(
            strpos($content, 'Second carousel banner'),
            strpos($content, 'First carousel banner'),
        );
        $this->assertLessThan(
            strpos($content, 'Third carousel banner'),
            strpos($content, 'Second carousel banner'),
        );
    }

    public function test_banner_content_takes_precedence_over_homepage_fallback_settings(): void
    {
        SiteSetting::updateOrCreate(
            ['key' => 'hero_title'],
            ['value' => 'Global fallback headline', 'group' => 'homepage'],
        );
        SiteSetting::updateOrCreate(
            ['key' => 'hero_subtitle'],
            ['value' => 'Global fallback introduction', 'group' => 'homepage'],
        );

        Banner::query()->update([
            'title' => 'Banner-specific headline',
            'subtitle' => 'Banner-specific introduction',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Banner-specific headline')
            ->assertSee('Banner-specific introduction')
            ->assertSee('hero-slide--content', false)
            ->assertDontSee('Global fallback headline')
            ->assertDontSee('Global fallback introduction');
    }

    public function test_banner_image_is_required_but_content_is_optional_and_banner_link_supports_new_tab(): void
    {
        Storage::fake('public');
        Banner::query()->delete();

        $admin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.banners.index'))
            ->post(route('admin.banners.store'), [
                'is_active' => 1,
                'button_url' => '/shop',
                'link_target' => '_blank',
            ])
            ->assertRedirect(route('admin.banners.index'))
            ->assertSessionHasErrors(['image_file', 'image_url']);

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'image_file' => UploadedFile::fake()->image('image-only-banner.png'),
                'image_url' => '',
                'is_active' => 1,
                'button_url' => '/shop',
                'link_target' => '_blank',
            ])
            ->assertRedirect(route('admin.banners.index'))
            ->assertSessionHasNoErrors();

        $banner = Banner::firstOrFail();
        $this->assertSame('', $banner->title);
        $this->assertSame('_blank', $banner->link_target);

        $this->get('/')
            ->assertOk()
            ->assertSee($banner->image_url, false)
            ->assertSee('target="_blank" rel="noopener"', false)
            ->assertSee('hero-slide--image-only', false)
            ->assertDontSee('hero-copy', false);
    }

    public function test_public_storage_images_are_served_without_a_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('banners/banner.png', 'image contents');

        $this->get('/storage/banners/banner.png')
            ->assertOk()
            ->assertStreamedContent('image contents');

        $this->get('/media/banners/banner.png')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertStreamedContent('image contents');

        $this->get('/storage/banners/missing.png')->assertNotFound();
        $this->get('/storage/../.env')->assertNotFound();
        $this->get('/media/products/../.env')->assertNotFound();
        $this->get('/media/private/document.png')->assertNotFound();
    }

    public function test_product_storefront_settings_hide_prices_and_prevent_cart_additions(): void
    {
        $product = Product::where('is_active', true)->firstOrFail();
        SiteSetting::updateOrCreate(
            ['key' => 'show_product_prices'],
            ['value' => '0', 'group' => 'products'],
        );
        SiteSetting::updateOrCreate(
            ['key' => 'enable_add_to_cart'],
            ['value' => '0', 'group' => 'products'],
        );

        $this->get(route('shop.index'))
            ->assertOk()
            ->assertDontSee('₹'.number_format((float) $product->price, 2))
            ->assertDontSee('Price: low to high')
            ->assertDontSee('aria-label="Add '.$product->name.' to basket"');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('₹'.number_format((float) $product->price, 2))
            ->assertDontSee('Add to basket');

        $this->post(route('cart.add', $product), ['quantity' => 1])->assertForbidden();
        $this->patch(route('cart.update'), [
            'quantities' => [$product->id => 1],
        ])->assertSessionHasErrors('cart');
    }

    public function test_hiding_product_prices_does_not_disable_add_to_cart(): void
    {
        $product = Product::where('is_active', true)->firstOrFail();
        SiteSetting::updateOrCreate(
            ['key' => 'show_product_prices'],
            ['value' => '0', 'group' => 'products'],
        );

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('₹'.number_format((float) $product->price, 2))
            ->assertSee('Add to basket');

        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_product_card_cart_add_returns_product_specific_feedback_without_redirecting(): void
    {
        $product = Product::where('is_active', true)->firstOrFail();

        $this->postJson(route('cart.add', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('message', $product->name.' added to your basket.')
            ->assertJsonPath('cart_count', 1)
            ->assertSessionHas('cart', [$product->id => 1]);
    }

    public function test_cart_quantity_updates_reject_non_positive_values_and_return_recalculated_totals(): void
    {
        $product = Product::where('is_active', true)->firstOrFail();
        $cart = [$product->id => 1];
        $subtotal = round((float) $product->price * 2, 2);
        $deliveryFee = $subtotal < (float) SiteSetting::getValue('free_delivery_minimum', 499)
            ? (float) SiteSetting::getValue('delivery_fee', 39)
            : 0;

        foreach ([0, -1] as $quantity) {
            $this->withSession(['cart' => $cart])
                ->patch(route('cart.update'), [
                    'quantities' => [$product->id => $quantity],
                ])
                ->assertSessionHasErrors(["quantities.{$product->id}"]);
        }

        $response = $this->withSession(['cart' => $cart])
            ->patchJson(route('cart.update'), [
                'quantities' => [$product->id => 2],
            ]);

        $response->assertOk()
            ->assertJsonPath('cart_count', 2)
            ->assertJsonPath('lines.0.quantity', 2);
        $this->assertEquals($subtotal, $response->json('subtotal'));
        $this->assertEquals($deliveryFee, $response->json('delivery_fee'));
        $this->assertEquals(round($subtotal + $deliveryFee, 2), $response->json('total'));
    }

    public function test_wishlist_toggle_returns_saved_state_and_renders_highlighted_product(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::where('is_active', true)->firstOrFail();

        $this->actingAs($customer)
            ->postJson(route('wishlist.toggle', $product))
            ->assertOk()
            ->assertJsonPath('message', $product->name.' Saved to your wishlist.')
            ->assertJsonPath('is_wishlisted', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
        $this->get(route('shop.index'))
            ->assertOk()
            ->assertSee('product-wish is-saved')
            ->assertSee('aria-pressed="true"', false);

        $this->actingAs($customer)
            ->postJson(route('wishlist.toggle', $product))
            ->assertOk()
            ->assertJsonPath('is_wishlisted', false);
    }

    public function test_admin_product_display_settings_are_applied_to_storefront(): void
    {
        $admin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $product = Product::where('is_active', true)->firstOrFail();

        $this->get(route('products.show', $product))->assertOk();

        $this->actingAs($admin)->put(route('admin.products.settings.update'), [
            'enable_add_to_cart' => '1',
            'show_product_prices' => '0',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'enable_add_to_cart', 'value' => '1']);
        $this->assertDatabaseHas('site_settings', ['key' => 'show_product_prices', 'value' => '0']);
        $this->actingAs($admin)->get(route('admin.products.settings'))
            ->assertOk()
            ->assertSee('enable_add_to_cart')
            ->assertSee('ENABLED')
            ->assertSee('DISABLED');
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Add to basket')
            ->assertDontSee('₹'.number_format((float) $product->price, 2));

        $this->actingAs($admin)->put(route('admin.products.settings.update'), [
            'enable_add_to_cart' => '0',
            'show_product_prices' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($admin)->get(route('admin.products.settings'))
            ->assertOk()
            ->assertSee('ENABLED')
            ->assertSee('DISABLED');
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Add to basket')
            ->assertSee('₹'.number_format((float) $product->price, 2));

        $this->actingAs($admin)->put(route('admin.products.settings.update'), [
            'enable_add_to_cart' => '1',
            'show_product_prices' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'enable_add_to_cart', 'value' => '1']);
        $this->assertDatabaseHas('site_settings', ['key' => 'show_product_prices', 'value' => '1']);
        $settingsPage = $this->actingAs($admin)->get(route('admin.products.settings'))->assertOk();
        $this->assertSame(2, substr_count($settingsPage->getContent(), 'product-setting-status is-enabled'));
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Add to basket')
            ->assertSee('₹'.number_format((float) $product->price, 2));
    }

    public function test_customer_can_checkout_with_coupon_and_print_an_invoice(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $address = $customer->addresses()->create([
            'label' => 'Home',
            'recipient_name' => $customer->name,
            'phone' => '8178584340',
            'address_line' => '1 Market Road',
            'city' => 'Greater Noida',
            'state' => 'Uttar Pradesh',
            'postal_code' => '201318',
            'is_default' => true,
        ]);
        $product = Product::where('sku', 'SSG-OIL-02')->firstOrFail();
        $this->actingAs($customer)->withSession([
            'cart' => [$product->id => 2],
            'coupon_code' => 'SSG10',
        ])->post(route('checkout.place'), [
            'address_id' => $address->id,
            'payment_method' => 'cod',
        ])->assertSessionHasNoErrors();

        $order = Order::with('items')->firstOrFail();
        $this->assertSame(325.20, (float) $order->total);
        $this->assertSame(28, $product->fresh()->stock);

        $this->get(route('orders.show', $order))->assertOk()->assertSee($order->order_number);
        $this->get(route('orders.invoice', $order))->assertOk()->assertSee('INVOICE')->assertSee($order->items->first()->product_name);
    }

    public function test_admin_permissions_are_limited_to_explicit_grants(): void
    {
        $role = Role::findByName('Admin');
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin)->get(route('admin.products.index'))->assertForbidden();
        $this->assertFalse($admin->can('products.view'));

        $admin->givePermissionTo('products.view');
        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.products.settings'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.admins.index'))->assertForbidden();

        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $this->actingAs($superAdmin)->get(route('admin.admins.index'))->assertOk();
    }

    public function test_super_admin_can_create_an_admin_with_only_selected_permissions(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();

        $this->actingAs($superAdmin)->post(route('admin.admins.store'), [
            'name' => 'Catalogue Admin',
            'email' => 'catalogue-admin@example.test',
            'password' => 'SecurePass!234',
            'password_confirmation' => 'SecurePass!234',
            'permissions' => ['products.view', 'products.settings'],
        ])->assertRedirect(route('admin.admins.index'));

        $admin = User::where('email', 'catalogue-admin@example.test')->firstOrFail();
        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertTrue($admin->can('products.view'));
        $this->assertTrue($admin->can('products.settings'));
        $this->assertFalse($admin->can('orders.view'));

        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.settings'))->assertOk();
        $this->actingAs($admin)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.admins.index'))->assertForbidden();
    }

    public function test_super_admin_workspaces_render_and_website_settings_are_live(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $this->actingAs($superAdmin);

        foreach ([
            'admin.dashboard',
            'admin.products.index',
            'admin.products.create',
            'admin.categories.index',
            'admin.sections.index',
            'admin.brands.index',
            'admin.menus.index',
            'admin.orders.index',
            'admin.coupons.index',
            'admin.customers.index',
            'admin.enquiries.index',
            'admin.banners.index',
            'admin.testimonials.index',
            'admin.pages.index',
            'admin.settings.edit',
            'admin.admins.index',
            'admin.admins.create',
            'admin.activity.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }

        $this->put(route('admin.settings.update'), [
            'business_name' => 'Neighbourhood Pantry',
            'business_type' => 'Grocery Store',
            'phone' => '+91 8178584340',
            'email' => 'ssgmart9@gmail.com',
            'address' => 'CH. Chiranji Square, Greater Noida',
            'whatsapp' => '918178584340',
            'delivery_fee' => 29,
            'free_delivery_minimum' => 399,
            'announcement_text' => 'Hello, Greater Noida!',
            'hero_title' => 'Fresh from our shelves',
            'hero_subtitle' => 'Neighbourhood goodness.',
            'seo_title' => 'Neighbourhood Pantry SEO',
            'seo_description' => 'Fresh groceries nearby.',
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('Neighbourhood Pantry')->assertSee('Hello, Greater Noida!')->assertSee('Fresh from our shelves');
    }

    public function test_super_admin_can_create_a_database_driven_product(): void
    {
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $category = Category::where('slug', 'atta-rice')->firstOrFail();
        $brand = Brand::where('slug', 'aashirvaad')->firstOrFail();

        $this->actingAs($superAdmin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Local Test Pantry Rice',
            'sku' => 'SSG-TEST-RICE',
            'description' => 'A test product.',
            'unit' => '500 g',
            'price' => 123.50,
            'stock' => 8,
            'image_url' => 'https://example.com/rice.jpg',
            'is_featured' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'SSG-TEST-RICE',
            'name' => 'Local Test Pantry Rice',
            'brand_id' => $brand->id,
            'stock' => 8,
        ]);
    }

    public function test_admin_can_upload_and_replace_store_images(): void
    {
        Storage::fake('public');
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $category = Category::where('slug', 'atta-rice')->firstOrFail();
        $brand = Brand::where('slug', 'aashirvaad')->firstOrFail();

        $this->actingAs($superAdmin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Uploaded Daily Rice',
            'sku' => 'SSG-UPLOAD-RICE',
            'unit' => '1 kg',
            'price' => 99,
            'stock' => 10,
            'image_file' => UploadedFile::fake()->image('rice.png'),
            'is_active' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'SSG-UPLOAD-RICE')->firstOrFail();
        $oldImage = $product->image;
        Storage::disk('public')->assertExists($oldImage);

        $this->put(route('admin.products.update', $product), [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'price' => $product->price,
            'stock' => $product->stock,
            'image_file' => UploadedFile::fake()->image('rice-replacement.png'),
            'is_active' => 1,
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertNotSame($oldImage, $product->image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($product->image);

        $this->from(route('admin.banners.index'))->post(route('admin.banners.store'), [
            'title' => 'Local essentials offer',
            'image_file' => UploadedFile::fake()->image('banner.png')->size(6 * 1024),
            'button_text' => 'Shop',
            'button_url' => '/shop',
            'is_active' => 1,
        ])->assertRedirect(route('admin.banners.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('banners', ['title' => 'Local essentials offer']);
        $banner = Banner::where('title', 'Local essentials offer')->firstOrFail();
        $oldBannerImage = $banner->image;
        Storage::disk('public')->assertExists($oldBannerImage);

        $this->from(route('admin.banners.index'))->put(route('admin.banners.update', $banner), [
            'title' => 'Updated local essentials offer',
            'image_file' => UploadedFile::fake()->image('banner-replacement.png'),
            'button_text' => 'Browse',
            'button_url' => '/shop',
            'is_active' => 1,
        ])->assertRedirect(route('admin.banners.index'))->assertSessionHasNoErrors();

        $banner->refresh();
        $this->assertSame('Updated local essentials offer', $banner->title);
        $this->assertNotSame($oldBannerImage, $banner->image);
        Storage::disk('public')->assertMissing($oldBannerImage);
        Storage::disk('public')->assertExists($banner->image);

        URL::forceRootUrl('http://localhost/mylearning/public');
        $this->assertSame('http://localhost/mylearning/public/storage/'.$banner->image, $banner->image_url);
        URL::forceRootUrl(null);
    }

    public function test_restricted_categories_and_products_are_blocked_and_never_listed(): void
    {
        $admin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();
        $category = Category::create([
            'name' => 'Vegetable Market',
            'slug' => 'vegetable-market',
            'is_active' => true,
        ]);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Fresh Spinach Bundle',
            'slug' => 'fresh-spinach-bundle',
            'sku' => 'LEGACY-SPINACH',
            'description' => 'Old listing',
            'unit' => '250 g',
            'price' => 29,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->get('/')->assertDontSee('Vegetable Market')->assertDontSee('Fresh Spinach Bundle');
        $this->get('/shop')->assertDontSee('Vegetable Market')->assertDontSee('Fresh Spinach Bundle');
        $this->get('/products/fresh-spinach-bundle')->assertNotFound();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Fresh Fruits',
            'is_active' => 1,
        ])->assertSessionHasErrors('name');

        $allowedCategory = Category::where('slug', 'packaged-foods')->firstOrFail();
        $this->post(route('admin.products.store'), [
            'category_id' => $allowedCategory->id,
            'name' => 'Chicken Snack Pack',
            'unit' => '1 pack',
            'price' => 99,
            'stock' => 4,
        ])->assertSessionHasErrors('name');
    }

    public function test_cancelling_an_order_returns_stock_once_and_order_cannot_be_reopened(): void
    {
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $address = $customer->addresses()->create([
            'label' => 'Home',
            'recipient_name' => $customer->name,
            'phone' => '8178584340',
            'address_line' => '1 Market Road',
            'city' => 'Greater Noida',
            'state' => 'Uttar Pradesh',
            'postal_code' => '201318',
        ]);
        $product = Product::where('sku', 'SSG-OIL-02')->firstOrFail();
        $initialStock = $product->stock;

        $this->actingAs($customer)->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.place'), ['address_id' => $address->id, 'payment_method' => 'cod'])
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame($initialStock - 2, $product->fresh()->stock);
        $superAdmin = User::where('email', env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local'))->firstOrFail();

        $this->actingAs($superAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'cancelled',
            'payment_status' => 'pay_on_delivery',
        ])->assertSessionHasNoErrors();
        $this->assertSame($initialStock, $product->fresh()->stock);

        $this->actingAs($superAdmin)->put(route('admin.orders.update', $order), [
            'status' => 'packing',
            'payment_status' => 'pay_on_delivery',
        ])->assertSessionHasErrors('status');
        $this->assertSame($initialStock, $product->fresh()->stock);
    }
}
