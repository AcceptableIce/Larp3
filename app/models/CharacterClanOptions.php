<?php

class CharacterClanOptions extends CharacterProperty {

	protected $table = 'characters_clan_options';
	protected $fillable = array('character_id', 'option1', 'option2', 'option3', 'version');

}
