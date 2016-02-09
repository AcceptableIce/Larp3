<?php

class RulebookDiscipline extends Eloquent {
	protected $table = 'rulebook_disciplines';
	
	public function ranks() {
		return RulebookDisciplineRank::where('discipline_id', $this->id)->orderBy('rank');
	}

	public function paths() {
		return RulebookDisciplinePath::where('discipline_id', $this->id);
	}
}

?>