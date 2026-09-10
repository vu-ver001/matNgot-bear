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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('gateway_response')->comment('Ảnh chụp bill / chứng từ thanh toán');
            $table->text('note')->nullable()->after('proof_image')->comment('Lý do xác nhận thủ công hoặc ghi chú giao dịch');
            $table->timestamp('cod_reconciled_at')->nullable()->after('paid_at')->comment('Thời gian đối soát COD');
            $table->foreignId('cod_reconciled_by')->nullable()->after('cod_reconciled_at')->constrained('users')->nullOnDelete();
            $table->timestamp('cod_settled_at')->nullable()->after('cod_reconciled_by')->comment('Thời gian admin nhận tiền COD về tài khoản');
            $table->foreignId('cod_settled_by')->nullable()->after('cod_settled_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['cod_reconciled_by']);
            $table->dropForeign(['cod_settled_by']);
            $table->dropColumn([
                'proof_image',
                'note',
                'cod_reconciled_at',
                'cod_reconciled_by',
                'cod_settled_at',
                'cod_settled_by',
            ]);
        });
    }
};
