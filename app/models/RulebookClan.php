<?php

class RulebookClan extends Eloquent {
	protected $table = 'rulebook_clans';
	protected $fillable = ['name', 'advantages', 'disadvantages', 'disciplines'];
		
	public function disciplines() {
		$discipline_ids = explode(",", $this->disciplines);
		$disciplines = [];
		foreach($discipline_ids as $id) {
			$disciplines[] = RulebookDiscipline::find($id);
		}
		return $disciplines;
	}
}

?>