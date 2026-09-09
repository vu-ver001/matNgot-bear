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
        Schema::create('payment_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete()->comment('Nhân viên tạo yêu cầu hoàn tiền');
            $table->decimal('amount', 12, 2);
            $table->text('reason')->comment('Lý do yêu cầu hoàn tiền');
            $table->string('proof_image')->nullable()->comment('Ảnh chứng từ lỗi / bill đối chiếu');
            $table->string('bank_name', 100)->nullable()->comment('Tên ngân hàng nhận hoàn tiền của khách');
            $table->string('bank_account', 50)->nullable()->comment('Số tài khoản nhận tiền');
            $table->string('account_holder', 100)->nullable()->comment('Tên chủ tài khoản');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('admin_note')->nullable()->comment('Ghi chú của admin khi duyệt hoặc lý do từ chối');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin phê duyệt');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_refund_requests');
    }
};
