<?php

class ForumPost extends Eloquent {

	protected $table = 'forums_posts';

	public function topic() {
		return $this->belongsTo('ForumTopic', 'topic_id', 'id');
	}

	public function poster() {
		return $this->hasOne('User', 'id', 'posted_by');
	}

	public function edits() {
		return $this->hasMany('ForumEdit', 'post_id', 'id');
	}

	public static function render($body) {
		//First off, look for tags.
		$matches = [];
		$body = preg_replace_callback("/\[\[([\w\W]+?)\]\]/", function($match) {
			$arguments = explode("/", $match[1]);
			switch(strtolower($arguments[0])) {
				case "change":
					$id = intval($arguments[1]);
					$char = Character::find($id);
					if($char && (Auth::user()->isStoryteller() || Auth::user()->id == $char->user_id) && sizeof($arguments) == 3) {
						return View::make('partials/changes', ['character' => $char, 'version' => $arguments[2]])->render();
					}
				break;
				case "questionnaire":
					$id = intval($arguments[1]);
					$char = Character::find($id);
					if($char && (Auth::user()->isStoryteller() || Auth::user()->id == $char->user_id)) {
						return View::make('partials/questionnaire', ['character' => $char])->render();
					}
				break;
				case "handbook":
					return "<div class='handbook-page'>".View::make('partials/handbookPage', ['title' => $arguments[1]])->render()."</div>";
				case 'influence':
					if(sizeof($arguments) == 2) {
						$capName = $arguments[1];
						$cap = InfluenceCap::whereHas('definition', function($q) use ($capName) { $q->where('name', $capName); })->first();
						if($cap) return $cap->capacityString();
					}
				break;
				case "character":
					$char = $this->poster->activeCharacter();
					if(sizeof($arguments) == 3 && $char) {
						$type = $arguments[1];
						$value = $arguments[2];
						switch(strtolower($type)) {
							case "background":
								return $char->getBackgroundDots($value);
						}
					}
				break;
				case "deadline":
					//Determine if we're past the deadline.
					$now = new DateTime;
					$now->setTime(0, 0); 
					$nextGame = GameSession::where('date', '>=', $now)->orderBy('date')->first();
 
					if($nextGame) {
						$date = new DateTime($nextGame->date);
						$date->setTimezone(new DateTimeZone("America/Chicago"));
						$date->modify("6 hours"); //Fix timezone offset
						$date->setTime(19, 00);
						$deadlineDate = new DateTime($nextGame->date);
						$deadlineDate->setTimezone(new DateTimeZone("America/Chicago"));
						$deadlineDate->modify('previous Wednesday, 6 PM CST');
						if((new DateTime) > $deadlineDate) {
							return '<span class="past-deadline">No more changes can be submitted this cycle.</span>';
						}
						return 'Changes can still be submitted.';
					}
					return 'Could not find the next session';
				break;
			}
			return $match[0];
			}, $body);

		//Check for @mentions
		$body = preg_replace_callback("/(?<=^|(?<=[^a-zA-Z0-9-_\.]))@([A-Za-z0-9!#$%\-^&*]+|{[A-Za-z]+[A-Za-z0-9 !#$%\-^&*]+})/", function($match) {
			$username = $match[1];
			if(strtolower($username) == "andrew") $username = "Crap. I am Malevolent.";
			if($username[0] == "{") $username = substr($username, 1, strlen($username) - 2);
			$user = User::where('username', 'like', $username)->first();
			if($user) return "<div class='mention'>@".$user->mailtoLink()."</div>";
			return $username;
		}, $body);

		return $body;
	}

	public static function replaceSpecialTerms($body) {
		if(Auth::user()->isStoryteller()) {
			$body = preg_replace_callback("/(\s|\S|^|\\\\)(RTFM201|RTFM101|RTFM)/", function($match) {
				if($match[1] == "\\") {
					return $match[2];
				}
				switch(trim($match[2])) {
					case "RTFM201":
						return $match[1].'Information on this can be found in <a href="/larp201">LARP 201</a>.';
					case "RTFM101":
					case "RTFM":
						return $match[1].'Information on this can be found in <a href="/larp101">LARP 101</a>.';
				}
			}, $body);
		}
		$body = preg_replace_callback("/\[\[([\w\W]+?)\]\]/", function($match) {
			$arguments = explode("/", $match[1]);
			switch(strtolower($arguments[0])) {
				case "chop":
					$symbols = ["Rock", "Paper", "Scissors"];
					return "<div class='auto-chop'>".$symbols[array_rand($symbols)]."</div>";
				default:
					return $match[0];
			}
		}, $body);


		return $body;
	}


}
