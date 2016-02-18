<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	protected $table = 'users';

	protected $hidden = ['password', 'email'];

    public function permissions() {
        return $this->hasMany("Permission");
    }

	public function hasPermission($permission) {
		$p_id = PermissionDefinition::where('name', $permission)->firstOrFail()->id;
        return $this->permissions()->where('permission_id', $p_id)->count() > 0 || $this->isStoryteller();
	}

	public function hasPermissionById($permission_id) {
        return $this->permissions()->where('permission_id', $permission_id)->count() > 0 || $this->isStoryteller();
	}

	public function isStoryteller() {
		return Cache::remember('user-storyteller-'.$this->id, 60, function() {
			$p_id = PermissionDefinition::where('name', 'Storyteller')->firstOrFail()->id;
			return $this->permissions()->where('permission_id', $p_id)->count() > 0;
		});
	}
	
	public function characters() {
		return $this->hasMany('Character');
	}

	public function activeCharacter() {
		return Character::where(["user_id" => $this->id, "active"=> true])->first();
	}
	
	public function forums() {
		//Everyone has access to unrestricted forums.
		$collection = Forum::with('category')->whereNotNull('category_id')->orderBy('position')->get();
		$character = $this->activeCharacter();
		foreach($collection as $k => $c) {
			$allowed = true;
			if($c->sect_id != null) {
				if($character == null || $character->sect() == null) {
					$allowed = false;
				} else {
					$sect = $character->sect()->first();
					$sect_id = $sect->hidden_id ? $sect->hidden_id : $sect->sect_id;
					if($sect_id != $c->sect_id) $allowed = false;
				}
			}

			if($c->clan_id != null) { 
				if($character == null || $character->clan() == null) {
					$allowed = false;
				} else {
					$clan = $character->clan()->first();
					$clan_id = $clan->hidden_id ? $clan->hidden_id : $clan->clan_id;
					if($clan_id != $c->clan_id) $allowed = false;
					}
			}
			if($c->background_id != null) {
				if($character == null) {
					$allowed = false;	
				} else {			
					if($character->backgrounds()->where('background_id', $c->background_id)->count() == 0) $allowed = false;
				}
			}

			if($c->read_permission != null) {
				if(!$this->hasPermissionById($c->read_permission)) $allowed = false;
			}

			if($c->is_private) {
				if($this->activeCharacter() == null) {
				 	$allowed = false;
				} else if(!ForumCharacterPermission::where(['forum_id' => $c->id, 'character_id' => $this->activeCharacter()->id])->exists()) {
					$allowed = false;
				}
			}

			if(!$allowed && !$this->isStoryteller()) $collection->forget($k);
		}

		return $collection;
	}
	
	public function forumListing() {
		return $this->forums()->sortBy('position')->groupBy('category.name')->sortBy(function($v) {
			return $v[0]["relations"]["category"]["display_order"]; //This is so messy...
		});
	}
	
	public function canAccessForum($forum) {
		foreach($this->forumListing() as $key => $value) {
			foreach($value as $v) {
				if($v->id == $forum) return true;
			}
		}
		return false;
	}

	public function getSettingValue($name) {
		$setting = UserSetting::where('user_id', $this->id)->whereHas('definition', function ($q) use ($name) { $q->where('name', $name); })->first();
		return $setting ? $setting->value : null;	
	}

	public function countPosts() {
		return ForumPost::where('posted_by', $this->id)->count();
	}

	public function mail() {
		return $this->hasMany('ForumMail', 'to_id', 'id');
	}

	public function unreadMail() {
		return $this->mail()->where('received_at', null);
	}

	public function canAccessTopic($topic_id) {
		$topic = ForumTopic::find($topic_id);
		$forum = $topic->forum;
		if($this->canAccessForum($topic->forum_id)) {
			$topicsForUser = $forum->rawTopicsForUser($this->id)->get();
			foreach($topicsForUser as $tp) {
				if($tp->topic_id == $topic_id) return true;
			}
		}
		return false;
	}

	public function sendMessage($sender_id, $subject, $body) {
		$message = new ForumMail;
		$message->to_id = $this->id;
		$message->from_id = $sender_id;
		$message->title = $subject;
		$message->body = $body;
		$message->save();

		$email = $this->email;
		$name = $this->username;
		Mail::send("emails.personalMessage", ['user' => $this, 'message_data' => $message], function($message) use ($email, $name, $subject) {
			$message->to($email, $name)->subject($subject);
		});
	}
	
	public function mailtoLink() {
		return "<a class='mailto-link' href='/dashboard/mail?mailto=".$this->username."'>".$this->username."</a>";
	}

	public static function listStorytellers() {
		return User::whereHas('permissions', function($q) { 
			$q->whereHas('definition', function($q) { 
				$q->where('name', 'Storyteller');
			 }); 
		})->get();
	}

}
