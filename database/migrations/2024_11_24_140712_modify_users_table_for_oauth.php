<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modify provider_token to be a TEXT field instead of VARCHAR
            $table->text('provider_token')->nullable()->change();
            $table->text('provider_refresh_token')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider_token')->nullable()->change();
            $table->string('provider_refresh_token')->nullable()->change();
        });
    }
};
