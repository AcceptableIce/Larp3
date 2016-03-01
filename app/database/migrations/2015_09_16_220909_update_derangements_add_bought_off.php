<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDerangementsAddBoughtOff extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_derangements', function(Blueprint $table)
		{
			$table->boolean("bought_off")->after("derangement_id");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_derangements', function(Blueprint $table)
		{
			//
		});
	}

}
