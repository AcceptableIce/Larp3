<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTremerePathsToAllowNecromancy extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_tremere_paths', function(Blueprint $table)
		{
			$table->integer("discipline_id")->unsigned()->after("id");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_tremere_paths', function(Blueprint $table)
		{
			//
		});
	}

}
