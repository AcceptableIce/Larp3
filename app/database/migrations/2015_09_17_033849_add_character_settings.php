<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCharacterSettings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters', function(Blueprint $table)
		{
			$table->boolean('is_npc')->after('in_review');
			$table->boolean('active')->after('is_npc');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters', function(Blueprint $table)
		{
			//
		});
	}

}
