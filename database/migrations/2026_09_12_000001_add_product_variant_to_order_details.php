<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_details', 'product_variant_id')) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('order_details', 'variant_sku')) {
                $table->string('variant_sku', 100)->nullable()->after('product_name');
            }
            if (! Schema::hasColumn('order_details', 'variant_size')) {
                $table->string('variant_size', 50)->nullable()->after('variant_sku');
            }
            if (! Schema::hasColumn('order_details', 'variant_color')) {
                $table->string('variant_color', 50)->nullable()->after('variant_size');
            }
            if (! Schema::hasColumn('order_details', 'variant_image_url')) {
                $table->string('variant_image_url', 500)->nullable()->after('variant_color');
            }
            if (! Schema::hasColumn('order_details', 'original_unit_price')) {
                $table->decimal('original_unit_price', 12, 2)->nullable()->after('product_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table): void {
            $table->dropColumn([
                'variant_sku',
                'variant_size',
                'variant_color',
                'variant_image_url',
                'original_unit_price',
            ]);
        });
    }
};
