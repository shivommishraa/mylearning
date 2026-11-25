<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('navigation_items')
            ->where('location', 'account')
            ->where('url', '/account')
            ->delete();

        DB::table('navigation_items')
            ->where('location', 'account')
            ->where('url', '/profile')
            ->update([
                'sort_order' => 0,
                'updated_at' => now(),
            ]);

        Cache::forget('ssg.navigation-items');
    }

    public function down(): void
    {
        $now = now();

        DB::table('navigation_items')
            ->where('location', 'account')
            ->where('url', '/profile')
            ->update([
                'sort_order' => 40,
                'updated_at' => $now,
            ]);

        if (! DB::table('navigation_items')->where('location', 'account')->where('url', '/account')->exists()) {
            DB::table('navigation_items')->insert([
                'label' => 'Your overview',
                'url' => '/account',
                'location' => 'account',
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Cache::forget('ssg.navigation-items');
    }
};
