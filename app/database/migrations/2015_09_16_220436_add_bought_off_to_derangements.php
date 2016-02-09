<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoughtOffToDerangements extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_derangements', function(Blueprint $table)
		{
			$table->boolean('bought_off')->after('is_free');
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
