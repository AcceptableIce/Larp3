<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsCheckInTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sessions_check_in', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('session_id')->unsigned();
			$table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
			$table->integer('character_id')->unsigned();
			$table->foreign('character_id')->references('id')->on('characters')->onDelete('cascade');
			$table->boolean('costume');
			$table->boolean('nominated');
			$table->boolean('nominated_twice');
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
		Schema::drop('sessions_check_in');
	}

}
