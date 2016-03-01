<?php

class CharacterQuestionnaire extends Eloquent {
	protected $table = 'characters_questionnaire';
	protected $fillable = ['character_id', 'questionnaire_id', 'response'];

	public function character() {
		return $this->hasOne('Character', 'id', 'character_id');
	}

	public function definition() {
		return $this->hasOne('RulebookQuestionnaire', 'id', 'questionnaire_id');
	}
}