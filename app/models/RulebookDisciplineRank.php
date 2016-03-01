<?php

class RulebookDisciplineRank extends Eloquent {
	protected $table = 'rulebook_discipline_ranks';
	protected $fillable = ['name', 'description', 'discipline_id', 'rank', 'path_id'];
	public function discipline() {
		return $this->hasOne('RulebookDiscipline', 'id', 'discipline_id');
	}
}

?>