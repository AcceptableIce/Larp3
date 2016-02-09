<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookSects extends Migration {

	public function up() {
		Schema::create('rulebook_sects', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->text('clans');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_sects');
	}

}
