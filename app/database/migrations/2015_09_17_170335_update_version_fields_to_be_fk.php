<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateVersionFieldsToBeFk extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('characters_abilities', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});

		Schema::table('characters_attributes', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});
		Schema::table('characters_attributes_lost', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_clan', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_derangements', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_disciplines', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_flaws', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});
		Schema::table('characters_merits', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});
		Schema::table('characters_nature', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_paths', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_rituals', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_sect', function(Blueprint $table) {
			$table->dropColumn("version");
			$table->integer("version_id")->unsigned();
			$table->foreign("version_id")->references("id")->on("characters_versions")->onDelete('cascade');
		});		
		Schema::table('characters_willpower', function(Blueprint $table) {
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
		Schema::table('characters_versions', function(Blueprint $table)
		{
			//
		});
	}

}
