<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\ContentPageController as AdminContentPageController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductDisplaySettingsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\WelcomePopupController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\PublicImageController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/media/{path}', PublicImageController::class)->where('path', '.*')->name('media.show');
Route::get('/profile-photos/{user}', ProfilePhotoController::class)->middleware('auth')->name('profile.photo');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('shop.index');
Route::get('/search/suggestions', [StorefrontController::class, 'suggestions'])->name('search.suggest');
Route::get('/products/{product:slug}', [StorefrontController::class, 'product'])->name('products.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/pages/{page:slug}', [ContentPageController::class, 'show'])->name('pages.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

Route::get('/dashboard', function () {
    return auth()->user()->hasAnyRole(['Super Admin', 'Admin'])
        ? redirect()->route('admin.dashboard')
        : redirect()->route('account');
})->middleware(['auth', 'email.verified.when.required'])->name('dashboard');

Route::middleware(['auth', 'email.verified.when.required'])->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/wishlist', [AccountController::class, 'wishlist'])->name('account.wishlist');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/account/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/account/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/account/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/account/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'placeOrder'])->name('checkout.place');
    Route::get('/orders/{order}', [CartController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [CartController::class, 'invoice'])->name('orders.invoice');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Super Admin|Admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::get('/products/settings', [ProductDisplaySettingsController::class, 'edit'])->middleware('permission:products.settings')->name('products.settings');
    Route::put('/products/settings', [ProductDisplaySettingsController::class, 'update'])->middleware('permission:products.settings')->name('products.settings.update');

    Route::get('/products', [AdminProductController::class, 'index'])->middleware('permission:products.view')->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->middleware('permission:products.create')->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->middleware('permission:products.create')->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->middleware('permission:products.edit')->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->middleware('permission:products.edit')->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->middleware('permission:products.delete')->name('products.destroy');

    Route::get('/categories', [AdminCategoryController::class, 'index'])->middleware('permission:categories.view')->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->middleware('permission:categories.create')->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->middleware('permission:categories.edit')->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('categories.destroy');

    Route::get('/brands', [BrandController::class, 'index'])->middleware('permission:brands.view')->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->middleware('permission:brands.create')->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->middleware('permission:brands.edit')->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->middleware('permission:brands.delete')->name('brands.destroy');

    Route::get('/menus', [NavigationController::class, 'index'])->middleware('permission:menus.view')->name('menus.index');
    Route::post('/menus', [NavigationController::class, 'store'])->middleware('permission:menus.create')->name('menus.store');
    Route::put('/menus/{navigationItem}', [NavigationController::class, 'update'])->middleware('permission:menus.edit')->name('menus.update');
    Route::delete('/menus/{navigationItem}', [NavigationController::class, 'destroy'])->middleware('permission:menus.delete')->name('menus.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->middleware('permission:orders.view')->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->middleware('permission:orders.view')->name('orders.show');
    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->middleware('permission:orders.edit')->name('orders.update');

    Route::get('/coupons', [AdminCouponController::class, 'index'])->middleware('permission:coupons.view')->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->middleware('permission:coupons.create')->name('coupons.store');
    Route::put('/coupons/{coupon}', [AdminCouponController::class, 'update'])->middleware('permission:coupons.edit')->name('coupons.update');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->middleware('permission:coupons.delete')->name('coupons.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:customers.create')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view')->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:customers.edit')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.edit')->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');
    Route::get('/enquiries', [CustomerController::class, 'enquiries'])->middleware('permission:enquiries.view')->name('enquiries.index');
    Route::patch('/enquiries/{enquiry}/read', [CustomerController::class, 'markEnquiryRead'])->middleware('permission:enquiries.edit')->name('enquiries.read');
    Route::get('/activity', [CustomerController::class, 'activity'])->middleware('permission:activity.view')->name('activity.index');

    Route::get('/banners', [ContentController::class, 'banners'])->middleware('permission:content.view')->name('banners.index');
    Route::post('/banners', [ContentController::class, 'storeBanner'])->middleware('permission:content.create')->name('banners.store');
    Route::put('/banners/{banner}', [ContentController::class, 'updateBanner'])->middleware('permission:content.edit')->name('banners.update');
    Route::delete('/banners/{banner}', [ContentController::class, 'deleteBanner'])->middleware('permission:content.delete')->name('banners.destroy');
    Route::get('/sections', [HomeSectionController::class, 'index'])->middleware('permission:content.view')->name('sections.index');
    Route::post('/sections', [HomeSectionController::class, 'store'])->middleware('permission:content.create')->name('sections.store');
    Route::put('/sections/{section}', [HomeSectionController::class, 'update'])->middleware('permission:content.edit')->name('sections.update');
    Route::delete('/sections/{section}', [HomeSectionController::class, 'destroy'])->middleware('permission:content.delete')->name('sections.destroy');
    Route::get('/testimonials', [ContentController::class, 'testimonials'])->middleware('permission:content.view')->name('testimonials.index');
    Route::post('/testimonials', [ContentController::class, 'storeTestimonial'])->middleware('permission:content.create')->name('testimonials.store');
    Route::put('/testimonials/{testimonial}', [ContentController::class, 'updateTestimonial'])->middleware('permission:content.edit')->name('testimonials.update');
    Route::delete('/testimonials/{testimonial}', [ContentController::class, 'deleteTestimonial'])->middleware('permission:content.delete')->name('testimonials.destroy');
    Route::get('/pages', [AdminContentPageController::class, 'index'])->middleware('permission:pages.view')->name('pages.index');
    Route::post('/pages', [AdminContentPageController::class, 'store'])->middleware('permission:pages.create')->name('pages.store');
    Route::put('/pages/{page}', [AdminContentPageController::class, 'update'])->middleware('permission:pages.edit')->name('pages.update');
    Route::delete('/pages/{page}', [AdminContentPageController::class, 'destroy'])->middleware('permission:pages.delete')->name('pages.destroy');

    Route::get('/settings', [SettingsController::class, 'edit'])->middleware('permission:settings.view')->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->middleware('permission:settings.edit')->name('settings.update');
    Route::put('/settings/email-verification', [SettingsController::class, 'updateEmailVerification'])->middleware('permission:email_verification.edit')->name('settings.email-verification.update');

    Route::get('/welcome-popup', [WelcomePopupController::class, 'index'])->middleware('permission:welcome_popup.view')->name('welcome-popup.index');
    Route::post('/welcome-popup', [WelcomePopupController::class, 'store'])->middleware(['permission:welcome_popup.view', 'permission:welcome_popup.create'])->name('welcome-popup.store');
    Route::put('/welcome-popup', [WelcomePopupController::class, 'update'])->middleware(['permission:welcome_popup.view', 'permission:welcome_popup.edit'])->name('welcome-popup.update');
    Route::put('/welcome-popup/display', [WelcomePopupController::class, 'updateDisplaySettings'])->middleware(['permission:welcome_popup.view', 'permission:welcome_popup.manage'])->name('welcome-popup.display');
    Route::delete('/welcome-popup', [WelcomePopupController::class, 'destroy'])->middleware(['permission:welcome_popup.view', 'permission:welcome_popup.delete'])->name('welcome-popup.destroy');

    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/admins', [AdminUserController::class, 'index'])->middleware('permission:admins.view')->name('admins.index');
        Route::get('/admins/create', [AdminUserController::class, 'create'])->middleware('permission:admins.create')->name('admins.create');
        Route::post('/admins', [AdminUserController::class, 'store'])->middleware('permission:admins.create')->name('admins.store');
        Route::get('/admins/{admin}/edit', [AdminUserController::class, 'edit'])->middleware('permission:admins.edit')->name('admins.edit');
        Route::put('/admins/{admin}', [AdminUserController::class, 'update'])->middleware('permission:admins.edit')->name('admins.update');
        Route::delete('/admins/{admin}', [AdminUserController::class, 'destroy'])->middleware('permission:admins.delete')->name('admins.destroy');
    });
});

require __DIR__.'/auth.php';
