<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescriptionsToDerangements extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rulebook_derangements', function(Blueprint $table)
		{
			$table->boolean('requires_description')->after('requires_chop');
		});
		
		Schema::table('characters_derangements', function(Blueprint $table)
		{
			$table->text('description')->after('bought_off')->nullable();
		});	
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('rulebook_derangements', function(Blueprint $table)
		{
			//
		});
	}

}
