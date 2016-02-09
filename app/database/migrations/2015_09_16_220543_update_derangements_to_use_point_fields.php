<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDerangementsToUsePointFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_derangements', function(Blueprint $table)
		{
			$table->dropColumn("is_free");
			$table->dropColumn("bought_off");
			$table->integer("lost_points")->after("derangement_id");
			$table->integer("free_points")->after("lost_points");
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
