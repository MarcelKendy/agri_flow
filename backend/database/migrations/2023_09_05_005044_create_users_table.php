<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('cpf', 11);
            $table->string('email', 100);
            $table->string('photo', 100)->nullable();
            $table->string('password', 500);
            $table->string('configs', 500)->default('{"theme": 1}');
            $table->tinyInteger('level')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->string('note', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
