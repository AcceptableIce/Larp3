<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forums', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('category_id')->unsigned();
			$table->foreign('category_id')->references('id')->on('forums_categories')->onDelete('cascade');
			$table->integer('sect_id')->unsigned()->nullable();
			$table->foreign('sect_id')->references('id')->on('rulebook_sects');			
			$table->integer('clan_id')->unsigned()->nullable();
			$table->foreign('clan_id')->references('id')->on('rulebook_clans');	
			$table->integer('background_id')->unsigned()->nullable();
			$table->foreign('background_id')->references('id')->on('rulebook_backgrounds');										
			$table->boolean('requires_permission')->default(false);
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
		Schema::drop('forums');
	}

}
