<?php

class GameSessionCheckIn extends Eloquent {

	protected $table = 'sessions_check_in';

	public function character() {
		return $this->hasOne('Character', 'id', 'character_id');
	}

	public function session() {
		return $this->hasOne('GameSession', 'id', 'session_id');
	}

}
