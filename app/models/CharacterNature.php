<?php

class CharacterNature extends CharacterProperty {

	protected $table = 'characters_nature';
	protected $fillable = array('character_id', 'nature_id', 'version');

	public function definition() {
		return $this->hasOne('RulebookNature', 'id', 'nature_id');
	}


}
