<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFreeFieldsForDisciplines extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_disciplines', function(Blueprint $table)
		{
			$table->dropColumn("free_ranks");
			$table->integer("free_points")->after("ranks");
			$table->integer("lost_points")->after("ranks");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_disciplines', function(Blueprint $table)
		{
			//
		});
	}

}
