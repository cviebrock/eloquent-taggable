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
        $connection = config('taggable.connection');

        if (!Schema::connection($connection)->hasTable('test_models')) {
            Schema::create('test_models', function(Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->softDeletes();
            });
        }

        if (!Schema::connection($connection)->hasTable('test_dummies')) {
            Schema::create('test_dummies', function(Blueprint $table) {
                $table->increments('id');
                $table->string('title');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('test_models');
        Schema::dropIfExists('test_dummies');
    }
}
