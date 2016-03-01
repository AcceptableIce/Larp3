<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdatedByToHandbook extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('handbook_pages', function(Blueprint $table)
		{
			$table->integer('created_by')->unsigned()->nullable()->after('write_permission');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
			$table->integer('updated_by')->unsigned()->nullable()->after('created_by');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');			
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
