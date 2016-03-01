<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePowersRemoveCost extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_discipline_ranks', function(Blueprint $table)
		{
			$table->dropColumn("cost");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_discipline_ranks', function(Blueprint $table)
		{
			//
		});
	}

}
