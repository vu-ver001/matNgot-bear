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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 30)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 20);
            $table->string('recipient_address', 255);
            $table->text('note')->nullable();
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(30000);
            $table->decimal('total_amount', 12, 2);
            $table->enum('order_status', ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->enum('payment_method', ['COD', 'BANK_TRANSFER', 'E_WALLET', 'CARD']);
            $table->enum('payment_status', ['UNPAID', 'PENDING', 'PAID', 'FAILED', 'REFUNDED'])->default('UNPAID');
            $table->string('cancel_reason', 255)->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('stock_restored')->default(false);
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
