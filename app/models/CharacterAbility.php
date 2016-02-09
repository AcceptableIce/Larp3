<?php

class CharacterAbility extends CharacterProperty {

	protected $table = 'characters_abilities';
	protected $fillable = array('character_id', 'ability_id', 'amount', 'version');

	public function definition() {
		return $this->hasOne('RulebookAbility', 'id', 'ability_id');
	}

}
