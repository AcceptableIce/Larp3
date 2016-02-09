<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsMailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forums_mail', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('from_id')->unsigned();
			$table->foreign('from_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('to_id')->unsigned();
			$table->foreign('to_id')->references('id')->on('users')->onDelete('cascade');		
			$table->string('title');
			$table->text('body');
			$table->dateTime('received_at');	
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
		Schema::drop('forums_mail');
	}

}
