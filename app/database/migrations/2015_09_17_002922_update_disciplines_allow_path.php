<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDisciplinesAllowPath extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_disciplines', function(Blueprint $table)
		{
			$table->integer("path_id")->nullable()->unsigned();
			
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
