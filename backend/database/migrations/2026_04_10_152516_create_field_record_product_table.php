<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_record_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->float('dosage');

            $table->timestamps();

            $table->unique(['field_record_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_record_products');
    }
};