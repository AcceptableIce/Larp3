<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComboDisciplineTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rulebook_combo_disciplines', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('owner_id')->unsigned();
			$table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
			$table->string('name');
			$table->text('description');
			$table->integer('option1')->unsigned();
			$table->foreign('option1')->references('id')->on('rulebook_discipline_ranks')->onDelete('cascade');
			$table->integer('option2')->unsigned();
			$table->foreign('option2')->references('id')->on('rulebook_discipline_ranks')->onDelete('cascade');
			$table->integer('option3')->unsigned()->nullable();
			$table->foreign('option3')->references('id')->on('rulebook_discipline_ranks')->onDelete('cascade');						
			$table->timestamps();
		});
		Schema::create('characters_combo_disciplines', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->integer('combo_id')->unsigned();
			$table->foreign('combo_id')->references('id')->on('rulebook_combo_disciplines')->onDelete('cascade');
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
		Schema::drop('rulebook_combo_disciplines');
		Schema::drop('characters_combo_disciplines');
	}

}
