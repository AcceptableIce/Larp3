<?php

class CharacterAttributeLoss extends CharacterProperty {

	protected $table = 'characters_attributes_lost';
	protected $fillable = array('character_id', 'rank_lost', 'version');


}
