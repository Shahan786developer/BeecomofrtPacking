<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Company Profile Table (Invoice Header & Business Identity)
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Bee Comfort');
            $table->string('tagline')->default('Packing & Retail Garment Solutions');
            $table->string('address')->default('Warehouse • Pakistan');
            $table->string('phone')->default('+92 300 0000000');
            $table->string('email')->default('orders@beecomfort.com');
            $table->text('terms_conditions')->nullable();
            $table->timestamps();
        });

        // 2. Cash Receipt Vouchers Table (Payments from Customers)
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique(); // e.g. CRV-0001
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, cheque, online
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_receipts');
        Schema::dropIfExists('company_profiles');
    }
};
