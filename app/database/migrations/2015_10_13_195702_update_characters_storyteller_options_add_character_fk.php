<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCharactersStorytellerOptionsAddCharacterFk extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_storyteller_options', function(Blueprint $table)
		{
			$table->integer('character_id')->unsigned()->after('id');
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_storyteller_options', function(Blueprint $table)
		{
			//
		});
	}

}
