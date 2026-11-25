<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();
        $popup = DB::table('welcome_popups')->where('id', 1)->first();

        if ($popup) {
            DB::table('welcome_popups')->where('id', 1)->update([
                'is_enabled' => true,
                'display_frequency' => 'every_visit',
                'updated_at' => $timestamp,
            ]);

            return;
        }

        DB::table('welcome_popups')->insert([
            'id' => 1,
            'is_enabled' => true,
            'display_mode' => 'content_buttons',
            'title' => 'Welcome to SSG MART',
            'description' => 'Your neighbourhood store for everyday essentials, trusted favourites and thoughtful service.',
            'display_frequency' => 'every_visit',
            'display_delay' => 0,
            'image_position' => 'left',
            'content_alignment' => 'center',
            'button_style' => 'solid',
            'accent_color' => '#246b45',
            'show_close_button' => true,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::table('welcome_popup_buttons')->insert([
            'welcome_popup_id' => 1,
            'text' => 'Start shopping',
            'url' => '/shop',
            'target' => '_self',
            'sort_order' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    public function down(): void {}
};
