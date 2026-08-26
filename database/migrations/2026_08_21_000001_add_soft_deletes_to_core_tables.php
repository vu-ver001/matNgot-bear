<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung xóa mềm (deleted_at) cho các bảng nghiệp vụ chính.
     *
     * Lưu ý: drop unique trên users.email, vouchers.code và
     * reviews(user_id, product_id) vì unique thường sẽ chặn tái sử dụng
     * email/mã sau khi bản ghi cũ bị xóa mềm. Tính duy nhất được đảm bảo
     * ở tầng validation với điều kiện whereNull('deleted_at').
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->index('email');
            $table->softDeletes();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->index('code');
            $table->softDeletes();
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Index thường thay thế cho unique bị xóa, giữ index cho FK user_id
            $table->index(['user_id', 'product_id']);
            $table->dropUnique(['user_id', 'product_id']);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
            $table->dropIndex(['user_id', 'product_id']);
            $table->dropSoftDeletes();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->unique('code');
            $table->dropSoftDeletes();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->unique('email');
            $table->dropSoftDeletes();
        });
    }
};
