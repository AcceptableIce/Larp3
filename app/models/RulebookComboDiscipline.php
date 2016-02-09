<?php

class RulebookComboDiscipline extends Eloquent {
	protected $table = 'rulebook_combo_disciplines';
	protected $fillable = ['option1', 'option2', 'option3', 'owner_id', 'name', 'description'];
	public function option1() {
		return $this->hasOne('RulebookDisciplineRank', 'id', 'option1');
	}
	public function option2() {
		return $this->hasOne('RulebookDisciplineRank', 'id', 'option2');
	}
	public function option3() {
		return $this->hasOne('RulebookDisciplineRank', 'id', 'option3');
	}	
	public function owner() {
		return $this->hasOne('User', 'id', 'owner_id');
	}

	public function cost($character_id, $version = -1) {
		$character = Character::find($character_id);
		return $this->getOptionCost($this->option1()->first(), $character, $version) + $this->getOptionCost($this->option2()->first(), $character, $version) + 
									($this->option3 == null ? 0 : $this->getOptionCost($this->option3()->first(), $character, $version));
	}

	function getOptionCost($option, $character, $version) {
		$experience_cost = 0;
		$in_clans = $character->inClanDisciplines($version);
		$found = false;
		foreach($in_clans as $in) {
			if($in->id == $option->discipline_id) $found = true;
		}
		if($found) {
			//It is in clan, so the cost is lower. 
			$cost_array = [3, 3, 6, 6, 9];
			$experience_cost = $cost_array[$option->rank - 1];
		} else {
			//The cost array is higher, since it is out of clan.
			//However, if it is a common discipline, the scaling is different.
			if($option->discipline->common) {
				$cost_array = [3, 3, 7, 7, 11];
				$experience_cost = $cost_array[$option->rank - 1];
			} else {
				$cost_array = [4, 4, 8, 8, 12];
				$experience_cost = $cost_array[$option->rank - 1];
			}
		}
		return $experience_cost;
	}
}

?>