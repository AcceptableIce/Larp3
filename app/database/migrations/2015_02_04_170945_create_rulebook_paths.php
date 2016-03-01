<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookPaths extends Migration {

	public function up() {
		Schema::create('rulebook_paths', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->text('sins');
			$table->text('stats');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_paths');
	}

}
