<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookMerits extends Migration {

	public function up() {
		Schema::create('rulebook_merits', function($table) {
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
		Schema::drop('rulebook_merits');
	}

}
