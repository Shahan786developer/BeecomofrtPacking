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
        // Drop foreign keys if present, modify columns to nullable, re-add nullOnDelete foreign keys
        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->unsignedBigInteger('sale_id')->nullable()->change();
            $table->foreign('sale_id')->references('id')->on('sales')->nullOnDelete();
        });

        Schema::table('return_details', function (Blueprint $table) {
            $table->dropForeign(['sale_detail_id']);
            $table->unsignedBigInteger('sale_detail_id')->nullable()->change();
            $table->foreign('sale_detail_id')->references('id')->on('sale_details')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_details', function (Blueprint $table) {
            $table->dropForeign(['sale_detail_id']);
            $table->unsignedBigInteger('sale_detail_id')->nullable(false)->change();
            $table->foreign('sale_detail_id')->references('id')->on('sale_details')->cascadeOnDelete();
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->unsignedBigInteger('sale_id')->nullable(false)->change();
            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
        });
    }
};
