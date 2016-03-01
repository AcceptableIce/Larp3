<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsPermittedCharacters extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forums_permitted_characters', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('forum_id')->unsigned();
			$table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('forums_permitted_characters');
	}

}
