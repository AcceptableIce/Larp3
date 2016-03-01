<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfluenceTrackerTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('influence_actions', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('character_id')->unsigned()->nullable();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('SET NULL');
			$table->integer('parent_action')->unsigned()->nullable();
			$table->foreign('parent_action')->references('id')->on('influence_actions')->onDelete('CASCADE');
			$table->integer('background_id')->unsigned();
			$table->foreign('background_id')->references('id')->on('rulebook_backgrounds')->onDelete('CASCADE');
			$table->string('title');
			$table->text('description');
			$table->text('public_description');
			$table->string('link_request');
			$table->smallInteger('amount');
			$table->boolean('is_watch');
			$table->datetime('approved_at');
			$table->timestamps();
		});
		
		Schema::create('influence_conceals', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('action_id')->unsigned();
			$table->foreign('action_id')->references('id')->on('influence_actions')->onDelete('CASCADE');
			$table->integer('background_id')->unsigned();
			$table->foreign('background_id')->references('id')->on('rulebook_backgrounds')->onDelete('CASCADE');
			$table->smallInteger('amount');
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
		//
	}

}
