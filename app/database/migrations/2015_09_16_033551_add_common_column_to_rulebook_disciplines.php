<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommonColumnToRulebookDisciplines extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_disciplines', function(Blueprint $table)
		{
			$table->boolean('common')->after('retest');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_disciplines', function(Blueprint $table)
		{
			//
		});
	}

}
