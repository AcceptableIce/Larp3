<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumTopicsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forums_topics', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('forum_id')->unsigned();
			$table->foreign('forum_id')->references('id')->on('forums')->onDelete('cascade');
			$table->string('title');
			$table->integer('first_post')->unsigned();
			$table->boolean('is_complete');
			$table->boolean('is_sticky');
			$table->timestamps();
		});
		Schema::create('forums_posts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('topic_id')->unsigned();
			$table->foreign('topic_id')->references('id')->on('forums_topics')->onDelete('cascade');
			$table->text('body');
			$table->integer('posted_by')->unsigned();
			$table->foreign('posted_by')->references('id')->on('users')->onDelete('cascade');
			$table->boolean('is_storyteller_reply');
			$table->boolean('is_sticky');
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
		Schema::drop('forums_topics');
	}

}
