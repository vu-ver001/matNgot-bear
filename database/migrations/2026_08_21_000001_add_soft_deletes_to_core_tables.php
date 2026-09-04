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
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropUnique(['email']);
                } catch (\Throwable $e) {}
                try {
                    $table->index('email');
                } catch (\Throwable $e) {}
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('vouchers', 'deleted_at')) {
            Schema::table('vouchers', function (Blueprint $table) {
                try {
                    $table->dropUnique(['code']);
                } catch (\Throwable $e) {}
                try {
                    $table->index('code');
                } catch (\Throwable $e) {}
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('reviews', 'deleted_at')) {
            Schema::table('reviews', function (Blueprint $table) {
                try {
                    $table->index(['user_id', 'product_id']);
                } catch (\Throwable $e) {}
                try {
                    $table->dropUnique(['user_id', 'product_id']);
                } catch (\Throwable $e) {}
                $table->softDeletes();
            });
        }
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
