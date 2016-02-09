<?php

class RulebookElderPower extends Eloquent {
	protected $table = 'rulebook_elder_powers';
	protected $fillable = ['owner_id', 'name', 'description', 'discipline_id'];
	public function discipline() {	
		return $this->hasOne('RulebookDiscipline', 'id', 'discipline_id');
	}

	public function owner() {
		return $this->hasOne('Character', 'id', 'owner_id');
	}

}

?>