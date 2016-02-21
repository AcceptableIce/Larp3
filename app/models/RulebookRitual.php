<?php

class RulebookRitual extends Eloquent {
	protected $table = 'rulebook_rituals';
	protected $fillable = ['name', 'description', 'group', 'is_thaumaturgy', 'isCustom', 'owner'];
}

?>