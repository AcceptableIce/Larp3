<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForumsAddAppearOnStTodo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('forums', function(Blueprint $table) {
			$table->boolean('show_on_st_todo_list')->after('is_private');
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
