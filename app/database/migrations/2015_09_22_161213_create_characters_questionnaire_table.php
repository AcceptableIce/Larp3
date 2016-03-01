<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersQuestionnaireTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('characters_questionnaire', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->integer('questionnaire_id')->unsigned();
			$table->foreign('questionnaire_id')->references('id')->on('rulebook_questionnaire')->onDelete('cascade');
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
		Schema::drop('characters_questionnaire');
	}

}
