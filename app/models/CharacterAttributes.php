<?php

class CharacterAttributes extends CharacterProperty {

	protected $table = 'characters_attributes';
	protected $fillable = array('character_id', 'physicals', 'mentals', 'socials', 'version');

}
