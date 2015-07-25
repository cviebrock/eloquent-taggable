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
	public function up() {
		Schema::create('taggable_tags', function (Blueprint $table) {
			$table->increments('tag_id');
			$table->string('name');
			$table->string('normalized');
			$table->timestamps();
		});

		Schema::create('taggable_taggables', function (Blueprint $table) {
			$table->integer('tag_id');
			$table->integer('taggable_id')->unsigned()->index();
			$table->string('taggable_type');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('taggable_tags');
		Schema::drop('taggable_taggables');
	}
}
