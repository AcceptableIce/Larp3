<?php

class RulebookDiscipline extends Eloquent {
	protected $table = 'rulebook_disciplines';
	protected $fillable = ['name', 'description', 'retest', 'common'];
	
	public function ranks() {
		return RulebookDisciplineRank::where('discipline_id', $this->id)->orderBy('rank');
	}

	public function paths() {
		return RulebookDisciplinePath::where('discipline_id', $this->id);
	}
}

?>