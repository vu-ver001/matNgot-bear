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
            $table->string('cancel_request_status', 20)->nullable()->after('cancel_reason')->comment('PENDING, APPROVED, REJECTED');
            $table->text('cancel_request_reason')->nullable()->after('cancel_request_status');
            $table->timestamp('cancel_requested_at')->nullable()->after('cancel_request_reason');
            $table->text('cancel_rejection_reason')->nullable()->after('cancel_requested_at');
            $table->string('refund_bank_name', 100)->nullable()->after('cancel_rejection_reason');
            $table->string('refund_bank_account', 50)->nullable()->after('refund_bank_name');
            $table->string('refund_account_holder', 100)->nullable()->after('refund_bank_account');
            $table->text('refund_note')->nullable()->after('refund_account_holder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_request_status',
                'cancel_request_reason',
                'cancel_requested_at',
                'cancel_rejection_reason',
                'refund_bank_name',
                'refund_bank_account',
                'refund_account_holder',
                'refund_note',
            ]);
        });
    }
};
