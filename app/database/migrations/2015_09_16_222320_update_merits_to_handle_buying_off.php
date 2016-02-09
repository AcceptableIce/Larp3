<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMeritsToHandleBuyingOff extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_merits', function(Blueprint $table)
		{
			$table->dropColumn("is_free");
			$table->boolean("bought_off")->after("merit_id");
			$table->integer("lost_points")->after("bought_off");
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
		Schema::table('characters_merits', function(Blueprint $table)
		{
			//
		});
	}

}
