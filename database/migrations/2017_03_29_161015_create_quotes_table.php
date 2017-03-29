<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotesTable extends Migration
{
    public function up() {
        Schema::create('quotes', function (Blueprint $table){
            $table->increments('id');
            $table->timestamps();
            $table->text('content');

        }
        );
    }

    public function down(){
        Schema::drop('quotes');
    }
}
