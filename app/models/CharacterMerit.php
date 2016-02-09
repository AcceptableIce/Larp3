<?php

class CharacterMerit extends CharacterProperty {

	protected $table = 'characters_merits';
	protected $fillable = array('character_id', 'merit_id', 'version');

	public function definition() {
		return $this->hasOne('RulebookMerit', 'id', 'merit_id');
	}

}
