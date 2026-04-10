<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('field_records', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->string('service'); // Spray, Fertilization, Harvest, etc.
            $table->date('date');
            $table->foreignId('planting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tractor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('implement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->float('dosage');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('field_records');
    }
};