<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table){
        $table->increments('id');
        $table->timestamps();
        $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('provider');
            $table->string('provider_id')->unique();
            $table->string('role');
            $table->rememberToken();

    }
);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
        //
    }
}
