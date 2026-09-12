<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();
            $table->string('variant_sku', 100)->nullable()->after('product_name');
            $table->string('variant_size', 50)->nullable()->after('variant_sku');
            $table->string('variant_color', 50)->nullable()->after('variant_size');
            $table->string('variant_image_url', 500)->nullable()->after('variant_color');
            $table->decimal('original_unit_price', 12, 2)->nullable()->after('product_price');
            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            $table->dropForeign(['product_variant_id']);
            $table->dropIndex(['product_variant_id']);
            $table->dropColumn([
                'product_variant_id',
                'variant_sku',
                'variant_size',
                'variant_color',
                'variant_image_url',
                'original_unit_price',
            ]);
        });

    }
};
