<?php

class CharacterSect extends CharacterProperty {

	protected $table = 'characters_sect';
	protected $fillable = array('character_id', 'sect_id', 'hidden_id');

	public function definition() {
		return $this->hasOne('RulebookSect', 'id', 'sect_id');
	}

	public function hiddenDefinition() {
		return $this->hasOne('RulebookSect', 'id', 'hidden_id');
	}

}
