<?php

class ForumPostLike extends Eloquent {

	protected $table = 'forums_posts_likes';

	public function post() {
		return $this->belongsTo('ForumPost', 'post_id', 'id');
	}

	public function user() {
		return $this->hasOne('User', 'id', 'user_id');
	}
}
