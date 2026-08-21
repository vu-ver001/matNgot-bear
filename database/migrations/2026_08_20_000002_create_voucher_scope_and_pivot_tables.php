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
        // 1. Thêm cột apply_scope vào bảng vouchers
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('apply_scope', ['ALL', 'CATEGORY', 'PRODUCT'])->default('ALL')->after('voucher_type');
        });

        // 2. Bảng trung gian voucher - danh mục
        Schema::create('voucher_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->unique(['voucher_id', 'category_id']);
        });

        // 3. Bảng trung gian voucher - sản phẩm
        Schema::create('voucher_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->unique(['voucher_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_products');
        Schema::dropIfExists('voucher_categories');

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('apply_scope');
        });
    }
};
