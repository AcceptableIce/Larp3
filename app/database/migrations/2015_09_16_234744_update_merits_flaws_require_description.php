<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMeritsFlawsRequireDescription extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_merits', function(Blueprint $table)
		{
			$table->boolean("requires_description")->after("group");
		});

		Schema::table('rulebook_flaws', function(Blueprint $table)
		{
			$table->boolean("requires_description")->after("group");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_merits', function(Blueprint $table)
		{
			//
		});
	}

}
