<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWritePermissionFieldToHandbook extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('handbook_pages', function(Blueprint $table)
		{
			$table->integer("write_permission")->unsigned()->nullable()->after("read_permission");
			$table->foreign("write_permission")->references('id')->on('user_permission_definitions')->onDelete('SET NULL');	
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
