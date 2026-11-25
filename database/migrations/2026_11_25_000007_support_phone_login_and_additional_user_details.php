<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('father_husband_name', 120)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('users')->whereNull('email')->exists()) {
            throw new RuntimeException('Cannot require email while users without an email address still exist.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['father_husband_name', 'date_of_birth', 'gender']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
