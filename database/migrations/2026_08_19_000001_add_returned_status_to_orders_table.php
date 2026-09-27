<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('alter table "orders" drop constraint if exists "orders_order_status_check"');
            DB::statement('alter table "orders" add constraint "orders_order_status_check" check ("order_status" in (\'PENDING\', \'CONFIRMED\', \'PREPARING\', \'SHIPPING\', \'COMPLETED\', \'CANCELLED\', \'RETURNED\'))');
            DB::statement('alter table "orders" alter column "order_status" set default \'PENDING\'');

            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_status', ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED', 'CANCELLED', 'RETURNED'])->default('PENDING')->change();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('alter table "orders" drop constraint if exists "orders_order_status_check"');
            DB::statement('alter table "orders" add constraint "orders_order_status_check" check ("order_status" in (\'PENDING\', \'CONFIRMED\', \'PREPARING\', \'SHIPPING\', \'COMPLETED\', \'CANCELLED\'))');
            DB::statement('alter table "orders" alter column "order_status" set default \'PENDING\'');

            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_status', ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED', 'CANCELLED'])->default('PENDING')->change();
        });
    }
};
