<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreePointsToVirtues extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_paths', function(Blueprint $table)
		{
			$table->integer("free_points")->after("virtue4");
			$table->integer("lost_points")->after("free_points");

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_paths', function(Blueprint $table)
		{
			//
		});
	}

}
