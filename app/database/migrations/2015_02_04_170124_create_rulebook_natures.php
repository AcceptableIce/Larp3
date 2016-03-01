<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRulebookNatures extends Migration {

	public function up() {
		Schema::create('rulebook_natures', function($table) {
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
		Schema::drop('rulebook_natures');
	}

}
