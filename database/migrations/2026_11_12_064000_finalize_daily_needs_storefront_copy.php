<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('banners')->where('title', 'The good stuff for every day')->update([
            'eyebrow' => 'TRUSTED INDIAN BRANDS · GREATER NOIDA',
            'title' => 'Everyday essentials for your home',
            'subtitle' => 'Atta, chawal, dal, chai and everyday household essentials, all close to home.',
            'image' => null,
            'button_text' => 'Shop daily essentials',
            'button_url' => '/shop',
        ]);

        DB::table('coupons')->where('code', 'FRESH10')->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('banners')->where('title', 'Everyday essentials for your home')->update([
            'eyebrow' => 'YOUR EVERYDAY, A LITTLE EASIER',
            'title' => 'The good stuff for every day',
            'subtitle' => 'The trusted brands your home runs on, thoughtfully brought together in one neighbourhood store.',
            'button_text' => 'Shop all essentials',
        ]);
    }
};
