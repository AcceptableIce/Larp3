<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShortformDescriptionsToMeritsFlaws extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_merits', function(Blueprint $table)
		{
			$table->text('short_description')->after('description')->nullable();
		});
		Schema::table('rulebook_flaws', function(Blueprint $table)
		{
			$table->text('short_description')->after('description')->nullable();
		});		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_merits', function(Blueprint $table)
		{
			//
		});
	}

}
