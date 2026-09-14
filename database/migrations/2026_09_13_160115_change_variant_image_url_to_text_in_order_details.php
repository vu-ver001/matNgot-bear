<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            $table->longText('variant_image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            $table->string('variant_image_url', 500)->nullable()->change();
        });
    }
};
