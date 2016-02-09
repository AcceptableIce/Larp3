<?php

class CharacterWillpower extends CharacterProperty {

	protected $table = 'characters_willpower';
	protected $fillable = array('character_id', 'willpower_total', 'willpower_current', 'version');


}
