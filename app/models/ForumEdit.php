<?php
class ForumEdit extends Eloquent {
	protected $table = 'forums_edits';

	public function post() {
		return $this->hasOne('ForumPost', 'id', 'post_id');
	}

	public function user() {
		return $this->hasOne('User', 'id', 'user_id');
	}
}