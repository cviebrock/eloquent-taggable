<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateTaggableTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('taggable.connection');

        if (!Schema::connection($connection)->hasTable('taggable_tags')) {
            Schema::connection($connection)->create('taggable_tags', function(Blueprint $table) {
                $table->bigIncrements('tag_id');
                $table->string('name');
                $table->string('normalized');
                $table->timestamps();

                $table->index('normalized');
            });
        }

        if (!Schema::connection($connection)->hasTable('taggable_taggables')) {
            Schema::connection($connection)->create('taggable_taggables', function(Blueprint $table) {
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('taggable_id');
                $table->string('taggable_type');
                $table->timestamps();

                $table->index(['tag_id', 'taggable_id'], 'i_taggable_fwd');
                $table->index(['taggable_id', 'tag_id'], 'i_taggable_rev');
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
        $connection = config('taggable.connection');

        if (Schema::connection($connection)->hasTable('taggable_tags')) {
            Schema::connection($connection)->drop('taggable_tags');
        }

        if (Schema::connection($connection)->hasTable('taggable_taggables')) {
            Schema::connection($connection)->drop('taggable_taggables');
        }
    }
}
