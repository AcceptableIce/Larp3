<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForumsAddAsymmetricField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('forums', function(Blueprint $table)
		{
			$table->boolean('asymmetric_replies')->after('show_on_st_todo_list');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('forums', function(Blueprint $table)
		{
			//
		});
	}

}
