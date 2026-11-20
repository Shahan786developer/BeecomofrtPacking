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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'ntn')) {
                $table->string('ntn', 50)->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'strn')) {
                $table->string('strn', 50)->nullable()->after('ntn');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'strn')) {
                $table->dropColumn('strn');
            }
            if (Schema::hasColumn('customers', 'ntn')) {
                $table->dropColumn('ntn');
            }
        });
    }
};
