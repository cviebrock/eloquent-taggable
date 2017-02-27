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
        if (!Schema::hasTable('taggable_tags')) {
            Schema::create('taggable_tags', function(Blueprint $table) {
                $table->increments('tag_id');
                $table->string('name');
                $table->string('normalized');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('taggable_taggables')) {
            Schema::create('taggable_taggables', function(Blueprint $table) {
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
        if (Schema::hasTable('taggable_tags')) {
            Schema::drop('taggable_tags');
        }

        if (Schema::hasTable('taggable_taggables')) {
            Schema::drop('taggable_taggables');
        }
    }
}
