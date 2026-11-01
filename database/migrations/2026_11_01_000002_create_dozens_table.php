<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dozens', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // e.g. "12 pieces", "6 pieces"
            $table->unsignedInteger('quantity')->default(12);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dozens');
    }
};
