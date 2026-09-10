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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('customer_confirmed_at')->nullable()->after('completed_at')->comment('Thời điểm khách hàng bấm xác nhận đã nhận hàng');
            $table->string('return_request_status', 20)->nullable()->after('customer_confirmed_at')->comment('PENDING, APPROVED, REJECTED');
            $table->text('return_request_reason')->nullable()->after('return_request_status');
            $table->timestamp('return_requested_at')->nullable()->after('return_request_reason');
            $table->text('return_rejection_reason')->nullable()->after('return_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_confirmed_at',
                'return_request_status',
                'return_request_reason',
                'return_requested_at',
                'return_rejection_reason',
            ]);
        });
    }
};
