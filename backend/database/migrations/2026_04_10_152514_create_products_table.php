<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('active_ingredient')->nullable();
            $table->string('unit'); // L or KG
            $table->string('action_mode')->nullable(); // Systemic / Contact
            $table->string('compatibility_restrictions')->nullable();
            
            // nutrients (%)
            $table->float('nitrogen')->nullable();
            $table->float('phosphorus')->nullable();
            $table->float('potassium')->nullable();
            $table->float('calcium')->nullable();
            $table->float('magnesium')->nullable();
            $table->float('sulfur')->nullable();
            $table->float('boron')->nullable();
            $table->float('copper')->nullable();
            $table->float('manganese')->nullable();
            $table->float('zinc')->nullable();
            $table->float('iron')->nullable();
            $table->float('molybdenum')->nullable();
            $table->float('silicon')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};