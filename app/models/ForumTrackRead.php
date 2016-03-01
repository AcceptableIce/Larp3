<?php

class ForumTrackRead extends Eloquent {
	
	protected $table = 'forums_track_reads';
	protected $fillable = ['user_id', 'topic_id', 'mark_read'];
	public $timestamps = false;


	public function user() {
		return $this->hasOne('users', 'id', 'user_id');
	}

	public function topic() {
		return $this->hasOne('forums_topics', 'id', 'topic_id');
	}
}