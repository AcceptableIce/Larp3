<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeColumnsToRelevantTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_abilities', function(Blueprint $table) {
			$table->boolean("is_free")->default(false)->after("amount");
		});

		Schema::table('characters_backgrounds', function(Blueprint $table) {
			$table->boolean("is_free")->default(false)->after("amount");
		});
		Schema::table('characters_derangements', function(Blueprint $table) {
			$table->boolean("is_free")->default(false)->after("derangement_id");
		});		
		Schema::table('characters_disciplines', function(Blueprint $table) {
			$table->text("free_ranks")->nullable()->after("ranks");
		});		
		Schema::table('characters_flaws', function(Blueprint $table) {
			$table->boolean("is_free")->default(false)->after("flaw_id");
		});	
		Schema::table('characters_merits', function(Blueprint $table) {
			$table->boolean("is_free")->default(false)->after("merit_id");
		});		
		Schema::table('characters_rituals', function(Blueprint $table) {
			$table->boolean("is_free")->default(false)->after("ritual_id");
		});
		Schema::table('characters_willpower', function(Blueprint $table) {
			$table->mediumInteger("amount_free")->default(false)->after("willpower_current");
			$table->mediumInteger("amount_lost")->default(false)->after("willpower_current");
		});														
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_abilities', function(Blueprint $table)
		{
			//
		});
	}

}
