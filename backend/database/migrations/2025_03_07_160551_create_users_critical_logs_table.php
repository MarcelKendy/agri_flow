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
        Schema::create('users_critical_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('user_ip');
            $table->tinyInteger('type'); // 0 -> create, 1 -> update, 2 -> delete 
            $table->string('table');
            $table->string('column');
            $table->unsignedBigInteger('register_id');  
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_critical_logs');
    }
};
