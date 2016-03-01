<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersClanTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('characters_clan', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->integer('clan_id')->unsigned();
			$table->foreign('clan_id')->references('id')->on('rulebook_clans');
			$table->integer('hidden_id')->unsigned();
			$table->foreign('hidden_id')->references('id')->on('rulebook_clans');
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
		Schema::drop('characters_clan');
	}

}
