<?php

class RulebookDerangement extends Eloquent {
	protected $table = 'rulebook_derangements';
	protected $fillable = ['name', 'description', 'requires_chop', 'requires_description'];
}

?>