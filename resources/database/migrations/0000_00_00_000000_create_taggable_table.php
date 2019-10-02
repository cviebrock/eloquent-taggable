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

        $taggable_tags = config('taggable.tables.taggable_tags', 'taggable_tags');

        if (!Schema::connection($connection)->hasTable($taggable_tags)) {
            Schema::connection($connection)->create($taggable_tags, function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->unique();
                $table->string('normalized')->unique();
                $table->timestamps();

                $table->index('normalized');
            });
        }

        $taggable_taggables = config('taggable.tables.taggable_taggables', 'taggable_taggables');

        if (!Schema::connection($connection)->hasTable($taggable_taggables)) {
            Schema::connection($connection)->create($taggable_taggables, function(Blueprint $table) use ($taggable_tags) {
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('taggable_id');
                $table->string('taggable_type');
                $table->timestamps();
                $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
                $table->index(['tag_id', 'taggable_id'], 'i_taggable_fwd');
                $table->index(['taggable_id', 'tag_id'], 'i_taggable_rev');

                $table->foreign('tag_id')
                    ->references('id')
                    ->on($taggable_tags)
                    ->onDelete('cascade');
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
        $taggable_tags = config('taggable.tables.taggable_tags', 'taggable_tags');
        $taggable_taggables = config('taggable.tables.taggable_taggables', 'taggable_taggables');

        if (Schema::connection($connection)->hasTable($taggable_tags)) {
            Schema::connection($connection)->drop($taggable_tags);
        }

        if (Schema::connection($connection)->hasTable($taggable_taggables)) {
            Schema::connection($connection)->drop($taggable_taggables);
        }
    }
}
