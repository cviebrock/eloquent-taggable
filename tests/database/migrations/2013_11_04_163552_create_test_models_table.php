<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateTestModelsTable extends Migration
{

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('test_models', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        Schema::create('test_dummies', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('test_models');
        Schema::drop('test_dummies');
    }
}
