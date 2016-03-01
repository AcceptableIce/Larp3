<?php

class CharacterStorytellerOption extends Eloquent {
	protected $table = 'characters_storyteller_options';
	protected $fillable = ['user_id', 'option_id'];
	
	public function definition() {
		return $this->hasOne('RulebookStorytellerOption', 'id', 'option_id');
	}
}

?>