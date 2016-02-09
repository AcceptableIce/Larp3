<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterStorytellerOptions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rulebook_storyteller_options', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->string('type');
			$table->text('options');
			$table->integer('position');
			$table->timestamps();
		});
		Schema::create('characters_storyteller_options', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('option_id')->unsigned();
			$table->foreign('option_id')->references('id')->on('rulebook_storyteller_options')->onDelete('cascade');
			$table->text('value');
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
		Schema::drop('rulebook_storyteller_options');
		Schema::drop('characters_storyteller_options');
		
	}

}
