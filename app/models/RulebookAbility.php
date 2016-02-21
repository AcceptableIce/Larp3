<?php

class RulebookAbility extends Eloquent {
	protected $table = 'rulebook_abilities';
	protected $fillable = ['name', 'description', 'group', 'isCustom', 'owner'];
}

?>