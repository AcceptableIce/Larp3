<?php

class RulebookMerit extends Eloquent {
	protected $table = 'rulebook_merits';
	protected $fillable = ['name', 'description', 'short_description', 'cost', 'group', 'requires_description'];
}

?>