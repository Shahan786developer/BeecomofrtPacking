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
        // 1. Extend users table with IC Card, 3 phones, address, active status, and nullable email
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'ic_card')) {
                $table->string('ic_card', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'phone1')) {
                $table->string('phone1', 30)->nullable()->unique()->after('ic_card');
            }
            if (!Schema::hasColumn('users', 'phone2')) {
                $table->string('phone2', 30)->nullable()->after('phone1');
            }
            if (!Schema::hasColumn('users', 'phone3')) {
                $table->string('phone3', 30)->nullable()->after('phone2');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone3');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            // Allow email to be nullable
            $table->string('email')->nullable()->change();
        });

        // 2. Create user_permissions table
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('module', 50); // dashboard, products, orders, sales, returns, receipts, customers, barcode, settings
                $table->boolean('can_create')->default(false);
                $table->boolean('can_read')->default(false);
                $table->boolean('can_update')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->boolean('can_print')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'module']);
            });
        }

        // 3. Add user_id foreign key to sale_orders
        if (Schema::hasTable('sale_orders') && !Schema::hasColumn('sale_orders', 'user_id')) {
            Schema::table('sale_orders', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        // 4. Add user_id foreign key to sales
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'user_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        // 5. Add user_id foreign key to cash_receipts
        if (Schema::hasTable('cash_receipts') && !Schema::hasColumn('cash_receipts', 'user_id')) {
            Schema::table('cash_receipts', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        // 6. Add user_id foreign key to returns
        if (Schema::hasTable('returns') && !Schema::hasColumn('returns', 'user_id')) {
            Schema::table('returns', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('returns') && Schema::hasColumn('returns', 'user_id')) {
            Schema::table('returns', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('cash_receipts') && Schema::hasColumn('cash_receipts', 'user_id')) {
            Schema::table('cash_receipts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'user_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        if (Schema::hasTable('sale_orders') && Schema::hasColumn('sale_orders', 'user_id')) {
            Schema::table('sale_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        Schema::dropIfExists('user_permissions');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('users', 'phone3')) {
                $table->dropColumn('phone3');
            }
            if (Schema::hasColumn('users', 'phone2')) {
                $table->dropColumn('phone2');
            }
            if (Schema::hasColumn('users', 'phone1')) {
                $table->dropUnique(['phone1']);
                $table->dropColumn('phone1');
            }
            if (Schema::hasColumn('users', 'ic_card')) {
                $table->dropColumn('ic_card');
            }
        });
    }
};
