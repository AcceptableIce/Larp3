<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElderPowerTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rulebook_elder_powers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('discipline_id')->unsigned();
			$table->foreign('discipline_id')->references('id')->on('rulebook_disciplines')->onDelete('cascade');
			$table->integer('owner_id')->unsigned();
			$table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
			$table->string('name');
			$table->text('description');			
			$table->timestamps();
		});
		Schema::create('characters_elder_powers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->integer('elder_id')->unsigned();
			$table->foreign('elder_id')->references('id')->on('rulebook_elder_powers')->onDelete('cascade');
			$table->timestamps();
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('characters_elder_powers');
	}

}
