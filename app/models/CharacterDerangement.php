<?php

class CharacterDerangement extends CharacterProperty {

	protected $table = 'characters_derangements';
	protected $fillable = array('character_id', 'derangement_id', 'version');

	public function definition() {
		return $this->hasOne('RulebookDerangement', 'id', 'derangement_id');
	}

}
