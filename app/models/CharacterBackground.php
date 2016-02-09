<?php

class CharacterBackground extends CharacterProperty {

	protected $table = 'characters_backgrounds';
	protected $fillable = array('character_id', 'background_id', 'amount', 'version');

	public function definition() {
		return $this->hasOne('RulebookBackground', 'id', 'background_id');
	}

}
