<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVersionToLostAttributeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_attributes_lost', function(Blueprint $table)
		{
			$table->mediumInteger('version')->after('rank_lost');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_attributes_lost', function(Blueprint $table)
		{
			//
		});
	}

}
