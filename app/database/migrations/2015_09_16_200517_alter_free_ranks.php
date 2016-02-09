<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFreeRanks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_attributes', function(Blueprint $table)
		{
			$table->dropColumn('free_ranks');
			$table->integer('free_points')->after('socials');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_attributes', function(Blueprint $table)
		{
			//
		});
	}

}
