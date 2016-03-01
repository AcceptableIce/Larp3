<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeRanksToAttributes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_attributes', function(Blueprint $table)
		{
			$table->text("free_ranks")->nullable()->after("socials");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_attributes', function(Blueprint $table)
		{
			//
		});
	}

}
