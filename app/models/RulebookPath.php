<?php

class RulebookPath extends Eloquent {
	protected $table = 'rulebook_paths';
	protected $fillable = ['name', 'description', 'sins', 'stats'];
	
	public function sins() {
		return explode("||", $this->sins);
	}
	
	public function stats() {
		return explode("||", $this->stats);
	}
}

?>