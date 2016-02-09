<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookTremerePaths extends Migration {

	public function up() {
		Schema::create('rulebook_tremere_paths', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_tremere_paths');
	}

}
