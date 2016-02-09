<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFreePointsForBackground extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_backgrounds', function(Blueprint $table)
		{
			$table->dropColumn("is_free");
			$table->integer("free_points")->after("amount");
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
		Schema::table('characters_backgrounds', function(Blueprint $table)
		{
			//
		});
	}

}
