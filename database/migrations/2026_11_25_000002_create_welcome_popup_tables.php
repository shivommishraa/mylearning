<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welcome_popups', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('display_mode', 32)->default('image_content_buttons');
            $table->string('title', 180)->nullable();
            $table->text('description')->nullable();
            $table->string('display_frequency', 32)->default('once_per_session');
            $table->unsignedTinyInteger('display_delay')->default(0);
            $table->string('image_position', 16)->default('left');
            $table->string('content_alignment', 16)->default('left');
            $table->string('button_style', 16)->default('solid');
            $table->string('accent_color', 7)->default('#246b45');
            $table->boolean('show_close_button')->default(true);
            $table->timestamps();
        });

        Schema::create('welcome_popup_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welcome_popup_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('welcome_popup_buttons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welcome_popup_id')->constrained()->cascadeOnDelete();
            $table->string('text', 60);
            $table->string('url', 2048);
            $table->string('target', 10)->default('_self');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        foreach (['view', 'create', 'edit', 'delete', 'manage'] as $action) {
            Permission::firstOrCreate([
                'name' => "welcome_popup.{$action}",
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_popup_buttons');
        Schema::dropIfExists('welcome_popup_images');
        Schema::dropIfExists('welcome_popups');

        Permission::whereIn('name', [
            'welcome_popup.view',
            'welcome_popup.create',
            'welcome_popup.edit',
            'welcome_popup.delete',
            'welcome_popup.manage',
        ])->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
