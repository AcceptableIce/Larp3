<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFlawsToHandleBuyingOff extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_flaws', function(Blueprint $table)
		{
			$table->dropColumn("is_free");
			$table->boolean("bought_off")->after("flaw_id");
			$table->integer("free_points")->after("bought_off");
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
		Schema::table('characters_flaws', function(Blueprint $table)
		{
			//
		});
	}

}
