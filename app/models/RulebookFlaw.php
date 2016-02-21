<?php

class RulebookFlaw extends Eloquent {
	protected $table = 'rulebook_flaws';
	protected $fillable = ["name", "description", "short_description", "cost", "group", "requires_description"];
}

?>