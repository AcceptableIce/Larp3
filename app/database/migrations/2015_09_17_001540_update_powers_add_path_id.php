<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePowersAddPathId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_discipline_ranks', function(Blueprint $table)
		{
			$table->integer("path_id")->after('rank');
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
