<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcode_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('barcode_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->default('BEE COMFORT');
            $table->decimal('label_width_mm', 5, 2)->default(32.00);
            $table->decimal('label_height_mm', 5, 2)->default(22.00);
            $table->string('barcode_type')->default('Code39');
            $table->unsignedInteger('font_size')->default(8);
            $table->string('font_family')->default('Arabic Transparent');
            $table->string('text_align')->default('center');
            $table->integer('rotation')->default(0);
            $table->boolean('auto_resize')->default(true);
            $table->foreignId('default_description_id')->nullable()->constrained('barcode_descriptions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_configurations');
        Schema::dropIfExists('barcode_descriptions');
    }
};
