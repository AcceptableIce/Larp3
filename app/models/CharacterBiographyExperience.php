<?php

class CharacterBiographyExperience extends Eloquent {
	
	protected $table = 'characters_biography_experience';
	protected $fillable = ['character_id', 'questionnare_xp', 'backstory_xp'];
}