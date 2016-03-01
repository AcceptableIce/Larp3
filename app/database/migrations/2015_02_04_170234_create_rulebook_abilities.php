<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookAbilities extends Migration {

	public function up() {
		Schema::create('rulebook_abilities', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->string('group');
			$table->boolean('isCustom');
			$table->integer('owner');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('rulebook_abilities');
	}

}
