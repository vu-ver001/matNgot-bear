<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->longText('image_url')->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->longText('image_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('image_url', 500)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('image_url', 255)->nullable()->change();
        });
    }
};
