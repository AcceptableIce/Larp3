<?php

class CharacterFlaw extends CharacterProperty {

	protected $table = 'characters_flaws';
	protected $fillable = array('character_id', 'flaw_id', 'version');

	public function definition() {
		return $this->hasOne('RulebookFlaw', 'id', 'flaw_id');
	}

}
