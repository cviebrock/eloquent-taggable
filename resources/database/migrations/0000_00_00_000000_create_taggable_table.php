<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;


class CreateTaggableTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('taggable.db_connection');

        if (!Schema::connection($connection)->hasTable('taggable_tags')) {
            Schema::connection($connection)->create('taggable_tags', function(Blueprint $table) {
                $table->increments('tag_id');
                $table->string('name');
                $table->string('normalized');
                $table->timestamps();
            });
        }

        if (!Schema::connection($connection)->hasTable('taggable_taggables')) {
            Schema::connection($connection)->create('taggable_taggables', function(Blueprint $table) {
                $table->unsignedInteger('tag_id');
                $table->unsignedInteger('taggable_id');
                $table->string('taggable_type');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('taggable.db_connection');

        if (Schema::connection($connection)->hasTable('taggable_tags')) {
            Schema::connection($connection)->drop('taggable_tags');
        }

        if (Schema::connection($connection)->hasTable('taggable_taggables')) {
            Schema::connection($connection)->drop('taggable_taggables');
        }
    }
}
