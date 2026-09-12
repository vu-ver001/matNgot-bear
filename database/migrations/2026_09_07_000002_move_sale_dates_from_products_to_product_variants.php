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
        // 1. Xóa 2 cột sale_start_at và sale_end_at khỏi bảng products (nếu tồn tại)
        Schema::table('products', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('products', 'sale_start_at')) {
                $colsToDrop[] = 'sale_start_at';
            }
            if (Schema::hasColumn('products', 'sale_end_at')) {
                $colsToDrop[] = 'sale_end_at';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });

        // 2. Thêm 2 cột sale_start_at và sale_end_at vào bảng product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'sale_start_at')) {
                $table->dateTime('sale_start_at')->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('product_variants', 'sale_end_at')) {
                $table->dateTime('sale_end_at')->nullable()->after('sale_start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Xóa khỏi product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('product_variants', 'sale_start_at')) {
                $cols[] = 'sale_start_at';
            }
            if (Schema::hasColumn('product_variants', 'sale_end_at')) {
                $cols[] = 'sale_end_at';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        // 2. Thêm lại vào products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sale_start_at')) {
                $table->dateTime('sale_start_at')->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'sale_end_at')) {
                $table->dateTime('sale_end_at')->nullable()->after('sale_start_at');
            }
        });
    }
};
