<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfluenceCapsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('influence_caps', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('background_id')->unsigned();
			$table->foreign('background_id')->references('id')->on('rulebook_backgrounds')->onDelete('cascade');
			$table->smallInteger('capacity');
			$table->enum('delta', ['-','','+']);					
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
		Schema::drop('influence_caps');
	}

}
