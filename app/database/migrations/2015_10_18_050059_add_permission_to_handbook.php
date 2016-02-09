<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermissionToHandbook extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('handbook_pages', function(Blueprint $table)
		{
			$table->integer('read_permission')->unsigned()->nullable()->after('body');
			$table->foreign('read_permission')->references('id')->on('user_permission_definitions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('handbook_pages', function(Blueprint $table)
		{
			//
		});
	}

}
