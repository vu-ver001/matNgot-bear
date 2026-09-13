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
        // 1. Thêm product_variant_id vào bảng cart_items
        Schema::table('cart_items', function (Blueprint $table) {
            // Tạo index riêng cho user_id trước để foreign key user_id không bị mất index
            $table->index('user_id');

            // Xóa unique index cũ trên [user_id, product_id]
            $table->dropUnique('cart_items_user_id_product_id_unique');

            if (!Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }

            // Thêm unique index mới cho [user_id, product_id, product_variant_id]
            $table->unique(['user_id', 'product_id', 'product_variant_id'], 'cart_user_product_variant_unique');
        });

        // 2. Thêm product_variant_id và variant_name vào bảng order_details
        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'product_variant_id')) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('order_details', 'variant_name')) {
                $table->string('variant_name', 150)
                    ->nullable()
                    ->after('product_name')
                    ->comment('Tên phân loại lúc mua, ví dụ: Vàng Bơ · 60cm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }
            if (Schema::hasColumn('order_details', 'variant_name')) {
                $table->dropColumn('variant_name');
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_user_product_variant_unique');

            if (Schema::hasColumn('cart_items', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }
            // Khôi phục unique cũ
            $table->unique(['user_id', 'product_id'], 'cart_items_user_id_product_id_unique');
            $table->dropIndex(['user_id']);
        });
    }
};
