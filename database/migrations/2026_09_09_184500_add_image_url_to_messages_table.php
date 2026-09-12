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
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'image_url')) {
                $table->string('image_url', 1000)->nullable()->after('content');
            }
            if (Schema::hasColumn('messages', 'content')) {
                $table->text('content')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('messages', 'content')) {
                $table->text('content')->nullable(false)->change();
            }
        });
    }
};
