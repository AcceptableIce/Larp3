<?php

class CharacterPath extends CharacterProperty {

	protected $table = 'characters_paths';
	protected $fillable = array('character_id', 'path_id', 'virtue1', 'virtue2', 'virtue3', 'virtue4', 'version');

	public function definition() {
		return $this->hasOne('RulebookPath', 'id', 'path_id');
	}

}
