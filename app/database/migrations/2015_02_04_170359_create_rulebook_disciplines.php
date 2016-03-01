<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookDisciplines extends Migration {

	public function up() {
		Schema::create('rulebook_disciplines', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->string('retest');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_disciplines');
	}

}
