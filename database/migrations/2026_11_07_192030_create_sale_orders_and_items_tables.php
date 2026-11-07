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
        // 1. Add optional customer code column if not present
        if (!Schema::hasColumn('customers', 'code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('code', 50)->nullable()->after('name')->index();
            });
        }

        // 2. Create sale_orders table
        if (!Schema::hasTable('sale_orders')) {
            Schema::create('sale_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_no', 64)->unique();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->date('order_date');
                $table->string('status', 32)->default('Pending'); // Pending, Completed, Rejected
                $table->unsignedInteger('total_quantity')->default(0);
                $table->decimal('total_amount', 12, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Create sale_order_items table
        if (!Schema::hasTable('sale_order_items')) {
            Schema::create('sale_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_order_id')->constrained('sale_orders')->cascadeOnDelete();
                $table->string('article_name');
                $table->string('cup', 20)->nullable();
                $table->string('color', 64)->nullable();

                // 10 size breakdown matrix: 32, 34, 36, 38, 40, 42, 44, 46, 48, 50
                $table->unsignedInteger('size_32')->default(0);
                $table->unsignedInteger('size_34')->default(0);
                $table->unsignedInteger('size_36')->default(0);
                $table->unsignedInteger('size_38')->default(0);
                $table->unsignedInteger('size_40')->default(0);
                $table->unsignedInteger('size_42')->default(0);
                $table->unsignedInteger('size_44')->default(0);
                $table->unsignedInteger('size_46')->default(0);
                $table->unsignedInteger('size_48')->default(0);
                $table->unsignedInteger('size_50')->default(0);

                // Optional rates (Standard rate for 32-42, Plus rate for 44-50)
                $table->decimal('rate_std', 10, 2)->nullable();
                $table->decimal('rate_plus', 10, 2)->nullable();

                // Calculated totals
                $table->unsignedInteger('total_qty')->default(0);
                $table->decimal('line_total', 12, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_order_items');
        Schema::dropIfExists('sale_orders');

        if (Schema::hasColumn('customers', 'code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
