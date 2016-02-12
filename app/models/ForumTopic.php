<?php

class ForumTopic extends Eloquent {
	
	protected $table = 'forums_topics';
    protected $appends = ['lastUpdatedPost'];

	public function forum() {
		return $this->hasOne("Forum", "id", "forum_id");
	}

	public function posts() {
		return $this->hasMany("ForumPost", "topic_id", "id");
	}

	public function postsForUser($user_id) {
		$query = $this->posts();
		//If this belongs to Forum 5 (Contact the Storytellers), do not show ST-only replies.
		if($this->forum->category_id == 5 && !User::find($user_id)->isStoryteller()) {
			$query = $query->where('is_storyteller_reply', false);
		}
		return $query;
	}

	public function addedUsers() {
		return $this->hasMany("ForumTopicAddedUser", "topic_id", "id");
	}
	
	public function markAsRead($user) {
		$receipt = ForumTrackRead::firstOrCreate(['topic_id' => $this->id, 'user_id' => $user->id]);
		$receipt->mark_read = new DateTime;
		$receipt->save();
	}

	public function firstPost() {
		return $this->hasOne("ForumPost", "id", "first_post");
	}

	public function getLastUpdatedPostAttribute() {
		return ForumPost::where('topic_id', $this->id)->orderBy('created_at', 'desc')->first();
	}

	public function lastUpdatedPostForUser($user_id) {
		return $this->postsForUser($user_id)->orderBy('created_at', 'desc')->first();
	}

	public function userIsWatching($user_id) {
		return ForumTopicWatch::where(['user_id' => $user_id, 'topic_id' => $this->id])->exists();
	}

	public function postReply($user_id, $body) {
		$post = new ForumPost;
		$post->topic_id = $this->id;
		$post->body = $body;
		$post->posted_by = $user_id;
		$post->save();
		return $post;
	}

	public function getLinkForPost($position) {
		$page = ceil($position / 10);
		return "http://larp.illini-rp.net/forums/topic/$this->id?page=$page#post$position";
	}

	public function getLinkForPostById($id) {
		$listing = array_pluck($this->posts()->get()->toArray(), "id");
		$position = array_search($id, $listing);
		return $this->getLinkForPost($position + 1);
	}

	public function getLinkForLastPost() {
		$post = $this->posts()->count();
		return $this->getLinkForPost($post);
	}

	public function hasUnreadPosts($user_id) {
		$receipt = ForumTrackRead::where(['user_id' => $user_id, 'topic_id' => $this->id])->first();
		if($receipt != null) {
			return $this->postsForUser($user_id)->where('created_at', '>', $receipt->mark_read)->count() > 0;
		} else {
			return true;
		}
	}
}