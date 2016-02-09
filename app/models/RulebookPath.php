<?php

class RulebookPath extends Eloquent {
	protected $table = 'rulebook_paths';
	
	public function sins() {
		return explode("||", $this->sins);
	}
	
	public function stats() {
		return explode("||", $this->stats);
	}
}

?>