<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('bill_no');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('dozen_id')->constrained('dozens')->restrictOnDelete();
            $table->string('product_code'); // e.g. AA-01 (uppercase)
            $table->foreignId('sku_id')->constrained('skus')->restrictOnDelete();
            $table->char('sku_code', 7)->unique()->index();
            $table->decimal('actual_price', 12, 2);
            $table->foreignId('pricing_rule_id')->constrained('pricing_rules')->restrictOnDelete();
            $table->decimal('selling_price', 12, 2);
            $table->integer('stock_quantity')->default(0);
            $table->timestamp('zero_stock_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
