<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plantings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('crop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pivot_id')->constrained()->cascadeOnDelete();
            $table->float('size_ha');
            $table->date('date');
            $table->string('variety')->nullable();
            $table->tinyInteger('status')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('plantings');
    }
};