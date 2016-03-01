<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForumRequiresPermissionField extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('forums', function(Blueprint $table)
		{
			$table->dropColumn("requires_permission");
			$table->integer("required_permission")->nullable()->after("background_id");
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
