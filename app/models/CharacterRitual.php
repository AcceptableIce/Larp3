<?php

class CharacterRitual extends CharacterProperty {

	protected $table = 'characters_rituals';
	protected $fillable = array('character_id', 'ritual_id', 'version');

	public function definition() {
		return $this->hasOne('RulebookRitual', 'id', 'ritual_id');
	}

}
