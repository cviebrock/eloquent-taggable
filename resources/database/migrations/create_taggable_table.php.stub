<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateTaggableTable extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = config('taggable.connection');
        $taggableTagsTable = config('taggable.tables.taggable_tags', 'taggable_tags');
        $taggableTaggablesTable = config('taggable.tables.taggable_taggables', 'taggable_taggables');

        if (!Schema::connection($connection)->hasTable($taggableTagsTable)) {
            Schema::connection($connection)->create($taggableTagsTable, static function(Blueprint $table) {
                $table->bigIncrements('tag_id');
                $table->string('name');
                $table->string('normalized')->unique();
                $table->timestamps();

                $table->index('normalized');
            });
        }

        if (!Schema::connection($connection)->hasTable($taggableTaggablesTable)) {
            Schema::connection($connection)->create($taggableTaggablesTable, static function(Blueprint $table) {
                $table->unsignedBigInteger('tag_id');
                $table->unsignedBigInteger('taggable_id');
                $table->string('taggable_type');
                $table->timestamps();

                $table->unique(['tag_id', 'taggable_id', 'taggable_type']);

                $table->index(['tag_id', 'taggable_id'], 'i_taggable_fwd');
                $table->index(['taggable_id', 'tag_id'], 'i_taggable_rev');
                $table->index('taggable_type', 'i_taggable_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('taggable.connection');
        $taggableTagsTable = config('taggable.tables.taggable_tags', 'taggable_tags');
        $taggableTaggablesTable = config('taggable.tables.taggable_taggables', 'taggable_taggables');

        if (Schema::connection($connection)->hasTable($taggableTagsTable)) {
            Schema::connection($connection)->drop($taggableTagsTable);
        }

        if (Schema::connection($connection)->hasTable($taggableTaggablesTable)) {
            Schema::connection($connection)->drop($taggableTaggablesTable);
        }
    }
}
