<?php

class RulebookLore extends Eloquent {
	protected $table = 'rulebook_lores';
	
	public function background() {
		return $this->hasOne('RulebookBackground', 'id', 'background_id');
	}
}

?>