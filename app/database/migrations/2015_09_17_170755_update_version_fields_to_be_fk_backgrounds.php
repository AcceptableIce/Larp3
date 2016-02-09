<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateVersionFieldsToBeFkBackgrounds extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('characters_backgrounds', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('characters_backgrounds', function(Blueprint $table)
		{
			//
		});
	}

}
