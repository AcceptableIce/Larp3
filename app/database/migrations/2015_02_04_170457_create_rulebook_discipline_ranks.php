<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookDisciplineRanks extends Migration {

	public function up() {
		Schema::create('rulebook_discipline_ranks', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->integer('cost');
			$table->integer('discipline_id');
			$table->integer('path_id');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_discipline_ranks');
	}

}
