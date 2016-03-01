<?php

class CharacterDiscipline extends CharacterProperty {

	protected $table = 'characters_disciplines';
	protected $fillable = array('character_id', 'discipline_id', 'amount', 'version');

	public function definition() {
		return $this->hasOne('RulebookDiscipline', 'id', 'discipline_id');
	}

}
