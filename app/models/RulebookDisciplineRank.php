<?php

class RulebookDisciplineRank extends Eloquent {
	protected $table = 'rulebook_discipline_ranks';
	
	public function discipline() {
		return $this->hasOne('RulebookDiscipline', 'id', 'discipline_id');
	}
}

?>