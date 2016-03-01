<?php

class CharacterPosition extends Eloquent {
	protected $table = 'characters_positions';
	
	public function definition() {
		return $this->hasOne('RulebookPosition', 'id', 'position_id');
	}
}