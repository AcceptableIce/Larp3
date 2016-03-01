<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookClans extends Migration {

	public function up() {
		Schema::create('rulebook_clans', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->text('disciplines');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_clans');
	}

}
