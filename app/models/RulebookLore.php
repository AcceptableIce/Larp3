<?php

class RulebookLore extends Eloquent {
	protected $table = 'rulebook_lores';
	protected $fillable = ['background_id', 'level', 'description'];
	
	public function background() {
		return $this->hasOne('RulebookBackground', 'id', 'background_id');
	}
}

?>