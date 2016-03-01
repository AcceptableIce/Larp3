<?php

class ForumTopicWatch extends Eloquent {
	protected $table = "forums_topics_watches";
	protected $fillable = ['user_id', 'topic_id'];
	public function user() {
		return $this->hasOne('User', 'id', 'user_id');
	}

	public function topic() {
		return $this->hasOne('ForumTopic', 'id', 'topic_id');
	}
}