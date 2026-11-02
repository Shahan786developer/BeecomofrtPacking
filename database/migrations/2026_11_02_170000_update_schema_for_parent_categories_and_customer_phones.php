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
        // 1. Add parent_category to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'parent_category')) {
                $table->string('parent_category', 50)->default('Bee Comfort')->after('bill_no');
            }
        });

        // 2. Add owner_name and phone 2, 3, 4 to customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'owner_name')) {
                $table->string('owner_name', 150)->nullable()->after('name');
            }
            if (!Schema::hasColumn('customers', 'phone_2')) {
                $table->string('phone_2', 50)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('customers', 'phone_3')) {
                $table->string('phone_3', 50)->nullable()->after('phone_2');
            }
            if (!Schema::hasColumn('customers', 'phone_4')) {
                $table->string('phone_4', 50)->nullable()->after('phone_3');
            }
        });

        // 3. Add bill_type to sales table
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'bill_type')) {
                $table->string('bill_type', 50)->default('bee_comfort')->after('invoice_no');
            }
        });

        // 4. Add return_type to returns table
        Schema::table('returns', function (Blueprint $table) {
            if (!Schema::hasColumn('returns', 'return_type')) {
                $table->string('return_type', 50)->default('bee_comfort')->after('return_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'parent_category')) {
                $table->dropColumn('parent_category');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['owner_name', 'phone_2', 'phone_3', 'phone_4']);
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'bill_type')) {
                $table->dropColumn('bill_type');
            }
        });

        Schema::table('returns', function (Blueprint $table) {
            if (Schema::hasColumn('returns', 'return_type')) {
                $table->dropColumn('return_type');
            }
        });
    }
};
