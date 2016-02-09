<?php

class CharacterVersion extends Eloquent {

	protected $table = 'characters_versions';
	protected $fillable = array('character_id', 'version', 'hasDroppedMorality', 'comment');


}
