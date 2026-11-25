<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('navigation_items', function (Blueprint $table) {
            $table->string('location', 16)->default('header')->change();
        });

        $now = now();
        foreach ([
            ['Your overview', '/account', 0],
            ['Order history', '/account/orders', 10],
            ['Delivery addresses', '/account/addresses', 20],
            ['Saved favourites', '/account/wishlist', 30],
            ['Personal details', '/profile', 40],
        ] as [$label, $url, $sortOrder]) {
            $exists = DB::table('navigation_items')
                ->where('location', 'account')
                ->where('url', $url)
                ->exists();

            if (! $exists) {
                DB::table('navigation_items')->insert([
                    'label' => $label,
                    'url' => $url,
                    'location' => 'account',
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Cache::forget('ssg.navigation-items');
    }

    public function down(): void
    {
        DB::table('navigation_items')->where('location', 'account')->delete();

        Schema::table('navigation_items', function (Blueprint $table) {
            $table->enum('location', ['header', 'footer'])->default('header')->change();
        });

        Cache::forget('ssg.navigation-items');
    }
};
