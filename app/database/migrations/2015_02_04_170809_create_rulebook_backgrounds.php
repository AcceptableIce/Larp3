<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookBackgrounds extends Migration {

	public function up() {
		Schema::create('rulebook_backgrounds', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->text('group');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_backgrounds');
	}

}
