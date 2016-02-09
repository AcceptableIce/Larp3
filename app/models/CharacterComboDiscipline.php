<?php

class CharacterComboDiscipline extends CharacterProperty {

	protected $table = 'characters_combo_disciplines';
	protected $fillable = array('character_id', 'combo_id', 'version_id');

	public function definition() {
		return $this->hasOne('RulebookComboDiscipline', 'id', 'combo_id');
	}

}
