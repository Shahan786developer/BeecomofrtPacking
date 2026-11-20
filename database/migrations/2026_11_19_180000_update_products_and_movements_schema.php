<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. skus table: expand sku_code length to 50
        Schema::table('skus', function (Blueprint $table) {
            $table->string('sku_code', 50)->change();
        });

        // 2. products table: make pricing fields nullable and expand sku_code length to 50
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('actual_price', 12, 2)->nullable()->change();
            $table->unsignedBigInteger('pricing_rule_id')->nullable()->change();
            $table->decimal('selling_price', 12, 2)->nullable()->change();
            $table->string('sku_code', 50)->change();
        });

        // 3. sale_details and return_details: expand sku_code length to 50
        Schema::table('sale_details', function (Blueprint $table) {
            $table->string('sku_code', 50)->change();
        });

        Schema::table('return_details', function (Blueprint $table) {
            $table->string('sku_code', 50)->change();
        });

        // 4. stock_movements table: add customer_id foreign key
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('product_id')->constrained('customers')->nullOnDelete();
            }
        });

        // 5. Backfill customer_id for existing sale stock movements
        $saleMovements = DB::table('stock_movements')
            ->where('type', 'sale')
            ->whereNull('customer_id')
            ->get();

        foreach ($saleMovements as $movement) {
            $customerId = null;

            // Try resolving via reference_id if reference_type is Sale
            if ($movement->reference_id && (str_contains((string)$movement->reference_type, 'Sale') || empty($movement->reference_type))) {
                $sale = DB::table('sales')->where('id', $movement->reference_id)->first();
                if ($sale) {
                    $customerId = $sale->customer_id;
                }
            }

            // Fallback: extract invoice_no or customer ID from notes
            if (!$customerId && !empty($movement->notes)) {
                if (preg_match('/customer ID\s+(\d+)/i', $movement->notes, $m)) {
                    $customerId = (int) $m[1];
                } elseif (preg_match('/#([A-Za-z0-9\-]+)/', $movement->notes, $m)) {
                    $sale = DB::table('sales')->where('invoice_no', $m[1])->first();
                    if ($sale) {
                        $customerId = $sale->customer_id;
                    }
                }
            }

            if ($customerId) {
                DB::table('stock_movements')
                    ->where('id', $movement->id)
                    ->update(['customer_id' => $customerId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }
        });

        Schema::table('return_details', function (Blueprint $table) {
            $table->char('sku_code', 7)->change();
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->char('sku_code', 7)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('actual_price', 12, 2)->nullable(false)->change();
            $table->unsignedBigInteger('pricing_rule_id')->nullable(false)->change();
            $table->decimal('selling_price', 12, 2)->nullable(false)->change();
            $table->char('sku_code', 7)->change();
        });

        Schema::table('skus', function (Blueprint $table) {
            $table->char('sku_code', 7)->change();
        });
    }
};
