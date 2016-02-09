<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('file_uploads', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('url');
			$table->integer('read_permission')->unsigned()->nullable();
			$table->foreign('read_permission')->references('id')->on('user_permission_definitions')->onDelete('SET NULL');
			$table->integer('created_by')->unsigned();
			$table->foreign('created_by')->references('id')->on('users');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('file_uploads');
	}

}
