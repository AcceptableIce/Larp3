<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Forum extends Eloquent {
	use SoftDeletingTrait;

	protected $table = 'forums';
    protected $dates = ['deleted_at'];

	public function category() {
		return $this->hasOne('ForumCategory', 'id', 'category_id');
	}

	public function topics() {
		return $this->hasMany('ForumTopic');
	}

	public function post($title, $body, $user = null) {
		if(!Auth::check()) return false;
		if(!$user) $user = Auth::user();
		if($user->canAccessForum($this->id)) {
			$topic = new ForumTopic;
			$topic->forum_id = $this->id;
			$topic->title = $title;
			$topic->save();

			$post = new ForumPost;
			$post->topic_id = $topic->id;
			$post->body = $body;
			$post->posted_by = $user->id;
			$post->save();

			$topic->first_post = $post->id;
			$topic->save();
			
			return $topic;
		} else {
			return false;
		}
	}

	public function rawTopicsForUser($user_id) {
		$user = User::find($user_id);
		$query = DB::table('forums_topics')
				->select(DB::raw("forums_topics.id, forum_id, title, first_post, is_complete, is_sticky, views, forums_topics.created_at, forums_topics.updated_at, fpost.topic_id"))
				->where('forum_id', $this->id)
				->leftJoin('forums_posts as fpost', 'forums_topics.first_post', '=', 'fpost.id');
		if($this->time_limited && !$user->isStoryteller()) {
			$char = $user->activeCharacter();
			if($char) {
				$timestamp = 0;
				$date_added = ForumCharacterPermission::where(['forum_id' => $this->id, 'character_id' => $char->id]);
				if($date_added->exists()) {
					$timestamp = $date_added->first()->created_at;
				} else if(!$this->is_private) {
					//Get version 1 creation date.
					$timestamp = CharacterVersion::where(['character_id' => $char->id, 'version' => 1])->first()->created_at;
				}
				$query = $query->where(function($q) use ($timestamp) {
					$q->where('fpost.created_at', '>', $timestamp);
					$q->orWhere('forums_topics.is_sticky', true);
				});
			} else {
				$query = $query->where('fpost.id', '<', 0);
			}
		}
		if($this->player_specific_threads)  {
			if(!$user->isStoryteller()) {
				$query = $query->leftJoin("forums_topics_added_users as added", function($join) use($user_id) {
					$join->on("added.topic_id", "=", "forums_topics.id")->where('added.user_id', '=', $user_id);	
				})->where(function($q) use ($user_id) {
					$q->where('fpost.posted_by', $user_id)->orWhereNotNull('added.user_id');
				});
			}
		}
		return $query;
	}

	public function topicsForUserInOrder($user_id) {
		return $this->rawTopicsForUser($user_id)
			->leftJoin(DB::raw('(SELECT id, topic_id, MAX(created_at) AS latestDate FROM forums_posts GROUP BY topic_id) AS posts'), 
			function($join) {
				$join->on('forums_topics.id','=','posts.topic_id');
			})
			->orderBy('is_sticky', 'desc')->orderBy('latestDate', 'desc');
	}

	public function postCount() {
		$count = 0;
		foreach($this->topics as $topic) {
			$count += $topic->posts()->count();
		}
		return $count;
	}

	public function postCountForUser($user_id) {
		$count = 0;
		$user = User::find($user_id);
		$query = $this->rawTopicsForUser($user_id)
					->select(DB::raw('COUNT(plist.id) AS posts'))
					->leftJoin('forums_posts as plist', 'forums_topics.id', '=', 'plist.topic_id')
					->groupBy('forums_topics.id');
					
		if($this->asymmetric_replies && !$user->isStoryteller()) {
			$query = $query->where('plist.is_storyteller_reply', false);
		}
		foreach($query->get() as $l) {
			$count += $l->posts;
		}
		return $count;
	}

	public function topicCountForUser($user_id) {
		return $this->rawTopicsForUser($user_id)->count();
	}
	
	public function lastUpdatedTopicForUser($user_id) {
		return $this->rawTopicsForUser($user_id)
				->leftJoin('forums_posts as plist', 'forums_topics.id', '=', 'plist.topic_id')
				->orderBy('plist.created_at', 'DESC')->first();
	}
	
	public function getMostRecentPostForUserMetadata($user_id) {
		$query = $this->rawTopicsForUser($user_id)
				->select(DB::raw("plist.created_at, plist.posted_by"))
				->leftJoin('forums_posts as plist', 'forums_topics.id', '=', 'plist.topic_id')
				->orderBy('plist.created_at', 'DESC');
		if($this->asymmetric_replies && !User::find($user_id)->isStoryteller()) {
			$query = $query->where('plist.is_storyteller_reply', false);
		}
		return $query->first();
	}

	public function hasUnreadPosts($user_id) {
		$response = $this->rawTopicsForUser($user_id)
						->select('title', 'watch.mark_read', DB::raw('MAX(plist.created_at) as maxca'))
						->leftJoin('forums_track_reads as watch', function($j) use ($user_id) {
							$j->on('forums_topics.id', '=', 'watch.topic_id')->where('watch.user_id', '=', $user_id);
						})	
						->join('forums_posts as plist', 'forums_topics.id', '=', 'plist.topic_id');

		if($this->asymmetric_replies && !Auth::user()->isStoryteller()) {
			$response = $response->where('plist.is_storyteller_reply', false);
		}

		$response = $response->groupBy('forums_topics.id');

		foreach($response->get() as $r) {
			if($r->maxca > $r->mark_read) return true;
		}
		return false;
	}

	public function markForumRead($user_id) {
		$user = User::find($user_id);
		foreach($this->topicsForUser($user_id)->get() as $topic) {
			if($topic->hasUnreadPosts($user_id)) {
				$topic->markAsRead($user);
			}
		}
	}
	
	public function getTopicsInOrder() {
		 return DB::table("forums_topics AS topics")
		 		->select(DB::raw("DISTINCT topics.id"))
		 		->where('forum_id', $this->id)
				->leftJoin(DB::raw('(SELECT id, topic_id, MAX(created_at) AS latestDate FROM forums_posts GROUP BY topic_id) AS posts'), 							function($join) {
						$join->on('topics.id','=','posts.topic_id');
					}
				)
				->orderBy('is_sticky', 'desc')->orderBy('latestDate', 'desc');
	}
	
	public function description() {
		$description = $this->description;
		$description = preg_replace_callback("/\[\[([\w\W]+?)\]\]/", function($match) {
			$arguments = explode("/", $match[1]);
			switch(strtolower($arguments[0])) {
				case 'influence':
					$capName = $arguments[1];
					return InfluenceCap::whereHas('definition', 
						function($q) use ($capName) { 
							$q->where('name', $capName); 
						}
					)->first()->capacityString();
			}
		}, $description);
		return $description;
	}

}