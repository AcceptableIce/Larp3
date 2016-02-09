<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescriptionToBackgroundsAndMeritsAndFlaws extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_backgrounds', function(Blueprint $table)
		{
			$table->string('description')->after('amount');
		});
		Schema::table('characters_merits', function(Blueprint $table)
		{
			$table->string('description')->after('bought_off');
		});
		Schema::table('characters_flaws', function(Blueprint $table)
		{
			$table->string('description')->after('bought_off');
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
