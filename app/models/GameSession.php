<?php

class GameSession extends Eloquent {

	protected $table = 'sessions';
	
	public function checkins() {
		return $this->hasMany('GameSessionCheckIn', 'session_id', 'id');
	}

}
