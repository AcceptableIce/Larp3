<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoreItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rulebook_lores', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('background_id')->unsigned();
			$table->foreign('background_id')->references('id')->on('rulebook_backgrounds')->onDelete('cascade');
			$table->smallInteger('level');
			$table->text('description');
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
		Schema::drop('rulebook_lores');
	}

}
