<?php

class RulebookDisciplinePath extends Eloquent {
	protected $table = 'rulebook_discipline_paths';

	public function ranks() {
		return RulebookDisciplineRank::where(array('discipline_id' => $this->discipline_id, 'path_id' => $this->id));
	}
}

?>