<?php

class CharacterElderPower extends CharacterProperty {

	protected $table = 'characters_elder_powers';
	protected $fillable = array('character_id', 'elder_id', 'version_id');

	public function definition() {
		return $this->hasOne('RulebookElderPower', 'id', 'elder_id');
	}

}
