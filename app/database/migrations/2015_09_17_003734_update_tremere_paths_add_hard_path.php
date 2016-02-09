<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTremerePathsAddHardPath extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_discipline_paths', function(Blueprint $table)
		{
			$table->boolean("hard_path")->after("description");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_discipline_paths', function(Blueprint $table)
		{
			//
		});
	}

}
