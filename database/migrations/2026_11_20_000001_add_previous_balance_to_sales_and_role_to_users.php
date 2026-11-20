<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('superadmin')->after('email');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'show_previous_balance')) {
                $table->boolean('show_previous_balance')->default(false)->after('total_amount');
            }
            if (!Schema::hasColumn('sales', 'previous_balance')) {
                $table->decimal('previous_balance', 12, 2)->default(0.00)->after('show_previous_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'previous_balance')) {
                $table->dropColumn('previous_balance');
            }
            if (Schema::hasColumn('sales', 'show_previous_balance')) {
                $table->dropColumn('show_previous_balance');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
