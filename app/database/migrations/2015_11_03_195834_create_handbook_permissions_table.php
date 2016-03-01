<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHandbookPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('handbook_pages_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('page_id')->unsigned();
			$table->foreign('page_id')->references('id')->on('handbook_pages')->onDelete('cascade');
			$table->enum('type', ['read', 'write']);
			$table->integer('permission_id')->unsigned()->nullable();
			$table->foreign('permission_id')->references('id')->on('user_permission_definitions')->onDelete('set null');
			$table->integer('sect_id')->unsigned()->nullable();
			$table->foreign('sect_id')->references('id')->on('rulebook_sects')->onDelete('set null');					
			$table->integer('clan_id')->unsigned()->nullable();
			$table->foreign('clan_id')->references('id')->on('rulebook_clans')->onDelete('set null');				
			$table->integer('background_id')->unsigned()->nullable();
			$table->foreign('background_id')->references('id')->on('rulebook_backgrounds')->onDelete('set null');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('handbook_pages_permissions');
	}

}
