<?php

use App\Support\StoreCatalogRules;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 120)->nullable();
            $table->string('title', 180);
            $table->string('subtitle', 300)->nullable();
            $table->string('type', 30);
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('product_limit')->default(8);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        $legacySkus = [
            'SSG-MANGO-01', 'SSG-SPIN-01', 'SSG-TOMA-01', 'SSG-AVOC-01', 'SSG-EGGS-01',
            'SSG-YOGU-01', 'SSG-BREA-01', 'SSG-OIL-01', 'SSG-PAST-01', 'SSG-ALMO-01',
            'SSG-HONE-01', 'SSG-WATE-01',
        ];

        foreach (DB::table('products')->whereIn('sku', $legacySkus)->get(['id']) as $product) {
            $this->archiveProduct($product->id);
        }

        foreach (DB::table('products')->select('id', 'name', 'category_id')->get() as $product) {
            $category = DB::table('categories')->where('id', $product->category_id)->first(['name', 'slug']);

            if (StoreCatalogRules::isRestricted($product->name) || ($category && StoreCatalogRules::isRestricted($category->name.' '.$category->slug))) {
                $this->archiveProduct($product->id);
            }
        }

        foreach (DB::table('categories')->select('id', 'name', 'slug')->get() as $category) {
            $isLegacyAisle = in_array($category->slug, ['bakery', 'pantry-staples', 'snacks-drinks', 'household'], true);

            if (StoreCatalogRules::isRestricted($category->name.' '.$category->slug) || $isLegacyAisle) {
                DB::table('categories')->where('id', $category->id)->update([
                    'name' => 'Archived aisle '.$category->id,
                    'slug' => 'archived-aisle-'.$category->id,
                    'description' => null,
                    'image' => null,
                    'is_active' => false,
                ]);
            }
        }

        DB::table('brands')->whereIn('slug', ['market-fresh', 'farmstead', 'ssg-select'])->update(['is_active' => false]);

        foreach (DB::table('banners')->select('id', 'eyebrow', 'title', 'subtitle')->get() as $banner) {
            if ($banner->title === 'Good things are growing' || StoreCatalogRules::isRestricted($banner->eyebrow.' '.$banner->title.' '.$banner->subtitle)) {
                DB::table('banners')->where('id', $banner->id)->update([
                    'eyebrow' => null,
                    'title' => 'Archived homepage banner '.$banner->id,
                    'subtitle' => null,
                    'image' => null,
                    'is_active' => false,
                ]);
            }
        }

        DB::table('site_settings')->where('key', 'seo_title')
            ->where('value', 'SSG MART | Fresh groceries, delivered with care')
            ->update(['value' => 'SSG MART | Daily needs, delivered with care']);
        DB::table('site_settings')->where('key', 'seo_description')
            ->where('value', 'Shop farm-fresh produce, everyday pantry staples and local favourites at SSG MART, Greater Noida.')
            ->update(['value' => 'Shop pantry staples, snacks, personal care and home essentials at SSG MART, Greater Noida.']);

        if (! DB::table('coupons')->where('code', 'SSG10')->exists()) {
            DB::table('coupons')->where('code', 'FRESH10')->update([
                'code' => 'SSG10',
                'description' => 'Save on pantry, personal care and household essentials.',
            ]);
        } else {
            DB::table('coupons')->where('code', 'FRESH10')->update(['is_active' => false]);
        }

        DB::table('testimonials')->where('name', 'Ananya S.')->update([
            'quote' => 'My pantry staples arrive right on time, and the ordering experience is wonderfully simple.',
        ]);
        DB::table('testimonials')->where('name', 'Meera K.')->update([
            'quote' => 'It is easy to find the household essentials we need for the week, all in one place.',
        ]);
        DB::table('content_pages')->where('slug', 'about-us')->update([
            'body' => "SSG MART is your neighbourhood daily-needs store in Greater Noida. We bring together trusted pantry staples, packaged foods, personal care and household essentials.\n\nFind us at CH. Chiranji Square, Behind ACE CITY, Sector 1, Greater Noida, Bisrakh Jalalpur, Uttar Pradesh - 201318, India.\nCall us at +91 8178584340 or write to ssgmart9@gmail.com.",
        ]);

        foreach (DB::table('testimonials')->select('id', 'quote')->get() as $testimonial) {
            if (StoreCatalogRules::isRestricted($testimonial->quote)) {
                DB::table('testimonials')->where('id', $testimonial->id)->update([
                    'quote' => 'The daily essentials we need are easy to find and delivered right on time.',
                ]);
            }
        }

        foreach (DB::table('site_settings')->select('key', 'value')->get() as $setting) {
            if (! StoreCatalogRules::isRestricted($setting->value ?? '')) {
                continue;
            }

            $safeValues = [
                'announcement_text' => 'Your neighbourhood store for everyday essentials',
                'seo_title' => 'SSG MART | Daily needs, delivered with care',
                'seo_description' => 'Shop pantry staples, snacks, personal care and home essentials at SSG MART, Greater Noida.',
                'hero_title' => 'Everyday essentials, closer to home',
                'hero_subtitle' => 'Shop trusted Indian pantry, personal care and household essentials.',
            ];
            if (isset($safeValues[$setting->key])) {
                DB::table('site_settings')->where('key', $setting->key)->update(['value' => $safeValues[$setting->key]]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }

    private function archiveProduct(int $id): void
    {
        DB::table('products')->where('id', $id)->update([
            'name' => 'Archived catalogue item '.$id,
            'slug' => 'archived-catalogue-item-'.$id,
            'sku' => 'ARCHIVED-SSG-'.$id,
            'description' => null,
            'image' => null,
            'is_active' => false,
            'is_featured' => false,
        ]);
    }
};
