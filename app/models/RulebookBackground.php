<?php

class RulebookBackground extends Eloquent {
	protected $table = 'rulebook_backgrounds';
	protected $fillable = ['name', 'description', 'group'];
}

?>