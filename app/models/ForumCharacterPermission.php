<?php

class ForumCharacterPermission extends Eloquent {

	protected $table = 'forums_permitted_characters';

	public function character() {
		return $this->hasOne('Character', 'id', 'character_id');
	}

	public function forum() {
		return $this->hasOne('Forum', 'id', 'forum_id');
	}
}