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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('voucher_type', ['ORDER', 'SHIPPING'])->default('ORDER')->after('code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_voucher_id')->nullable()->after('voucher_id')->constrained('vouchers')->nullOnDelete();
            $table->decimal('shipping_discount_amount', 12, 2)->default(0)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_voucher_id']);
            $table->dropColumn(['shipping_voucher_id', 'shipping_discount_amount']);
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('voucher_type');
        });
    }
};
