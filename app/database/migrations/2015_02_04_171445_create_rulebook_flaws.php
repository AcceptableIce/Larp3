<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookFlaws extends Migration {

	public function up() {
		Schema::create('rulebook_flaws', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->integer('cost');
			$table->string('group');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_flaws');
	}

}
