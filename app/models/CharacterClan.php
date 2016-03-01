<?php

class CharacterClan extends CharacterProperty {

	protected $table = 'characters_clan';
	protected $fillable = array('character_id', 'clan_id', 'hidden_id');

	public function definition() {
		return $this->hasOne('RulebookClan', 'id', 'clan_id');
	}
	
	public function hiddenDefinition() {
		return $this->hasOne('RulebookClan', 'id', 'hidden_id');
	}
}
