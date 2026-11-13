<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContentPage;
use App\Models\NavigationItem;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\StoreCatalogRules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Gate::before(fn (?User $user) => $user?->hasRole('Super Admin') ? true : null);

        View::composer('*', function ($view) {
            $values = Cache::remember('ssg.site-settings', 300, function () {
                return Schema::hasTable('site_settings')
                    ? SiteSetting::query()->pluck('value', 'key')->all()
                    : [];
            });

            $site = array_replace(config('ssg.defaults'), $values);
            $view->with('site', $site);
            $view->with('storefrontFeatures', [
                'show_product_prices' => $site['show_product_prices'] === '1',
                'enable_add_to_cart' => $site['enable_add_to_cart'] === '1',
            ]);
            $view->with('publishedPages', Cache::remember('ssg.published-pages', 300, fn () => Schema::hasTable('content_pages')
                ? ContentPage::where('is_published', true)->orderBy('title')->get()
                : collect()));
            $view->with('storeCategories', Cache::remember('ssg.store-categories', 300, fn () => Schema::hasTable('categories')
                ? Category::where('is_active', true)->orderBy('sort_order')->get()
                    ->reject(fn (Category $category) => StoreCatalogRules::isRestricted($category->name.' '.$category->slug))
                : collect()));
            $view->with('navigationItems', Cache::remember('ssg.navigation-items', 300, fn () => Schema::hasTable('navigation_items')
                ? NavigationItem::where('is_active', true)->orderBy('location')->orderBy('sort_order')->get()->groupBy('location')
                : collect()));
        });
    }
}
