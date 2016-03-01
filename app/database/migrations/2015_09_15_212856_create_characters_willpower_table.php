<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersWillpowerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('characters_willpower', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->integer('willpower_total');
			$table->integer('willpower_current');
			$table->mediumInteger('version')->unsigned();
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
		Schema::drop('characters_willpower');
	}

}
