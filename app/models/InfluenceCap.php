<?php

class InfluenceCap extends Eloquent {
	protected $table = 'influence_caps';
	
	public function definition() {
		return $this->hasOne('RulebookBackground', 'id', 'background_id');
	}
	
	public function capacityString() {
		return $this->capacity.$this->delta;
	}
}

?>