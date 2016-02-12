<?php
class ForumController extends BaseController {

	function showTopic($id) {
		$topic = ForumTopic::find($id);
		$topic->views++;
		$topic->save();

		$topic->markAsRead(Auth::user());
		if(Auth::user()->canAccessTopic($id)) {
			return View::make('forums/viewTopic')->with('id', $id); 
		} else {
			return "Access denied.";
		}
	}
	function postTopic() {
		if(!Auth::check()) return Redirect::to("/forums");
		$topic_id = Input::get("topic_id");
		if(isset($topic_id)) {
			//Edit logic
			$topic = ForumTopic::find(Input::get("topic_id"));
			$post = $topic->firstPost;
			$user = Auth::user();
			if(!$user->canAccessTopic($topic_id)) return "Access denied.";
			if($post->posted_by == $user->id || $user->isStoryteller()) {
				$body = ForumPost::replaceSpecialTerms(Input::get("body"));
				$post->body = $body;
				$post->save();
				$topic->title = Input::get("title");
				$topic->save();
				//Save an edit record
				$posted_by = Input::get("post-as");
				$poster = User::find($posted_by);
				if(!$poster) $poster = Auth::user();
				$edit = new ForumEdit;
				$edit->post_id = $post->id;
				$edit->user_id = $poster->id;
				$edit->save();
				
				ForumTopicAddedUser::where('topic_id', $topic_id)->delete();
				$addedUsersRaw = Input::get("added-users");
				if(strlen(trim($addedUsersRaw)) > 0) {
					$addedUsers = explode(",", $addedUsersRaw);
					foreach($addedUsers as $au) {
						$new_au = new ForumTopicAddedUser;
						$new_au->topic_id = $topic->id;
						$new_au->user_id = $au;
						$new_au->save();
					}
				}

		
				return Redirect::to('/forums/topic/'.$post->topic->id);
			} else {
				return "Post does not belong to user";
			}
		} else {
			$user = Auth::user();
			$forum_id = Input::get("forum_id");
			$title = Input::get("title");
			$body = Input::get("body");
			if(!$user->canAccessForum($forum_id)) return "Access denied.";
			if($forum_id == null || $title == null || $body == null) {
				//Validate.
				return "failed.";
			} else {
				$forum = Forum::find($forum_id);
				if(!$forum->topic_permission || Auth::user()->hasPermission(PermissionDefinition::find($forum->topic_permission)->name) || Auth::user()->isStoryteller()) {
					$posted_by = Input::get("post-as");
					$body = ForumPost::replaceSpecialTerms($body);
					$topic = $forum->post($title, $body, User::find($posted_by));
					
					$addedUsersRaw = Input::get("added-users");
					if(strlen(trim($addedUsersRaw)) > 0) {
						$addedUsers = explode(",", $addedUsersRaw);
						foreach($addedUsers as $au) {
							$new_au = new ForumTopicAddedUser;
							$new_au->topic_id = $topic->id;
							$new_au->user_id = $au;
							$new_au->save();
						}
					}
					//Check for @mentions.
					$this->alertMentions($posted_by, $body, $topic);

					if($topic) {
						return Redirect::to('/forums/topic/'.$topic->id);
					} else {
						return "No access.";
					}
				} else {
					return 'No write permission';
				}
			}
		}
	}

	function postReply() {
		if(!Auth::check()) return Redirect::to("/forums");
		$post_id = Input::get("post_id");
		if(isset($post_id)) {
			//Edit logic
			$post = ForumPost::find(Input::get("post_id"));
			$user = Auth::user();
			if(!$user->canAccessTopic($post->topic_id)) return "Access denied.";
			if($post->posted_by == $user->id || $user->isStoryteller()) {
				$body = ForumPost::replaceSpecialTerms(Input::get("body"));

				$post->body = $body;
				if($user->isStoryteller()) $post->is_storyteller_reply = Input::get("st-reply") == "on" ? 0 : 1;
				$post->save();

				//Save an edit record
				$posted_by = Input::get("post-as");
				$poster = User::find($posted_by);
				if(!$poster) $poster = Auth::user();
				$edit = new ForumEdit;
				$edit->post_id = $post->id;
				$edit->user_id = $poster->id;
				$edit->save();

				if(Input::get("watch") == "on") $this->subscribeToTopic($post->topic->id);			
				return Redirect::to('/forums/topic/'.$post->topic->id.'?page='.ceil($post->topic->posts()->count() / 10));
			} else {
				return "Post does not belong to user";
			}
		} else {
			$user = Auth::user();
			$topic_id = Input::get("topic_id");
			$body = Input::get("body");
			if($topic_id == null || $body == null) {
				//Validate.
				return "failed.";
			} else {
				//Ensure this user has access to the forum to which we're trying to post, and the relevant write permission
				$topic = ForumTopic::find($topic_id);
				if($user->canAccessForum($topic->forum_id)) {
					$forum = $topic->forum;
					if(!$forum->reply_permission || Auth::user()->hasPermission(PermissionDefinition::find($forum->reply_permission)->name) || Auth::user()->isStoryteller()) {
						$posted_by = Input::get("post-as");
						$poster_id = $posted_by ? $posted_by : $user->id;
						$body = ForumPost::replaceSpecialTerms($body);
						$post = $topic->postReply($poster_id, $body);
						if($user->isStoryteller()) {
							$post->is_storyteller_reply = Input::get("st-reply") == "on" ? 0 : 1;
							$post->save();
						}
						//Mark the thread as incomplete again.
						$topic->is_complete = false;
						$topic->save();
						$this->messageSubscribers($post);
						if(Input::get("watch") == "on") $this->subscribeToTopic($post->topic->id);

						//Check for @mentions.
						$this->alertMentions($poster_id, $body, $topic);

						return Redirect::to('/forums/topic/'.$topic_id.'?page='.ceil($post->topic->posts()->count() / 10));
					} else {
						return 'No write permission';
					}
				} else {
					return "No access.";
				}

			}
		}				
	}

	function alertMentions($poster_id, $body, $topic) {
		preg_match_all("/(?<=^|(?<=[^a-zA-Z0-9-_\.]))@([A-Za-z0-9!#$%\-^&*]+|{[A-Za-z]+[A-Za-z0-9 !#$%^&\-*]+})/", $body, $matches);
		$poster = User::find($poster_id);
		foreach($matches[1] as $m) {
			if($m[0] == "{") $m = substr($m, 1, strlen($m) - 2);
			if(strtolower($m) == "andrew") $m = "Crap. I am Malevolent.";
			$user = User::where('username', 'like', $m)->first();
			if($user && $user->getSettingValue("Disable @Mentions") != 1) {
				$link = $topic->getLinkForLastPost();
				$user->sendMessage($poster_id, "Mentioned in topic $topic->title", "Hello $user->username,<br><br>You have been mentioned by $poster->username in the topic".
												" <a href='$link'>$topic->title</a>.<br><br>They said:<blockquote>$body</blockquote>");
			}
		}
	}

	function messageSubscribers($post) {
		foreach(ForumTopicWatch::where('topic_id', $post->topic->id)->get() as $watch) {
			$user = $watch->user;
			$topic = $watch->topic;
			//Do not message the user who posted it
			if($user != $post->poster->id) {
				//Make sure the user still has access to this post.
				if($user->canAccessTopic($topic->id)) {
					if($post->is_storyteller_reply && !$user->isStoryteller()) continue;
					Mail::send("emails.forumWatch", ['user' => $user, 'topic' => $topic, 'post' => $post], function($message) use ($user, $topic) {
						$message->to($user->email, $user->username)->subject("The topic $topic->title has been updated.");
					});
				}
			}
		}
	}

	function toggleWatch($id) {
		$user = Auth::user();
		$topic = ForumTopic::find($id);
		if($user) {
			if($topic) {
				if($user->canAccessTopic($id)) {
					if(ForumTopicWatch::where(['user_id' => $user->id, 'topic_id' => $id])->exists()) {
						$this->unsubscribeFromTopic($id);
					} else {
						$this->subscribeToTopic($id);
					}
					return Redirect::to("/forums/topic/$id");
				} else {
					return Response::json(["success" => false, "message" => "Access denied."]);			
				}
			} else {
				return Response::json(["success" => false, "message" => "Invalid topic."]);			
			}
		} else {
			return Response::json(["success" => false, "message" => "Not logged in."]);			
		}
	}

	function markForumRead($id) {
		$user = Auth::user();
		$forum = Forum::find($id);
		if($user) {
			if($forum) {
				$forum->markForumRead($user->id);
				return Redirect::to("/forums/$id");
			} else {
				return Response::json(["success" => false, "message" => "Invalid topic."]);			
			}
		} else {
			return Response::json(["success" => false, "message" => "Not logged in."]);			
		}
	}

	function markCategoryRead($id) {
		$user = Auth::user();
		if($user) {
			foreach(Forum::where('category_id', $id)->get() as $forum) {
				$forum->markForumRead($user->id);
			}
			return Redirect::to("/forums/");
		} else {
			return Response::json(["success" => false, "message" => "Not logged in."]);			
		}
	}


	function subscribeToTopic($id) {
		$user = Auth::user();
		$topic = ForumTopic::find($id);
		if($user) {
			if($topic) {
				$watch = ForumTopicWatch::firstOrCreate(['user_id' => $user->id, 'topic_id' => $topic->id]);
				return true;
			} else {
				return Response::json(["success" => false, "message" => "Topic not found."]);
			}
		} else {
			return Response::json(["success" => false, "message" => "Unauthorized."]);
		}
	}

	function unsubscribeFromTopic($id) {
		$user = Auth::user();
		$topic = ForumTopic::find($id);
		if($user) {
			if($topic) {
				$watch = ForumTopicWatch::where(['user_id' => $user->id, 'topic_id' => $topic->id])->first();
				if($watch) {
					$watch->delete();
					return true;
				} else {
					return Response::json(["success" => false, "message" => "Watch not found."]);
				}
			} else {
				return Response::json(["success" => false, "message" => "Topic not found."]);
			}
		} else {
			return Response::json(["success" => false, "message" => "Unauthorized."]);
		}		
	}

	function toggleTopicComplete($id) {
		if(Auth::user()->isStoryteller()) {
			$topic = ForumTopic::find($id);
			if($topic) {
				$topic->is_complete = $topic->is_complete ? 0 : 1;
				$topic->save();
				return Redirect::to("/forums/topic/$id");
			} else {
				return Response::json(["success" => false, "message" => "No topic found with that ID."]);		
			}
		} else {
				return Response::json(["success" => false, "message" => "Unauthorized."]);
		}
	}

	function toggleTopicSticky($id) {
		if(Auth::user()->isStoryteller()) {
			$topic = ForumTopic::find($id);
			if($topic) {
				$topic->is_sticky = $topic->is_sticky ? 0 : 1;
				$topic->save();
				return Redirect::to("/forums/topic/$id");
			} else {
				return Response::json(["success" => false, "message" => "No topic found with that ID."]);		
			}
		} else {
			return Response::json(["success" => false, "message" => "Unauthorized."]);
		}
	}

	function deletePost() {
		$post = ForumPost::find(Input::get("id"));
		if($post) {
			$user = Auth::user();
			if($post->poster->id == $user->id || $user->isStoryteller()) {
				$post_forum = $post->topic->forum;
				$post_topic = $post->topic;
				$post->delete();
				//If this post is the first post in a thread, delete the thread.
 				ForumTopic::where('first_post', $post->id)->delete();
 				//Redirect to the thread if it still exists. Otherwise, go back to the forum.
 				if(ForumTopic::where('id', $post_topic->id)->count() > 0) {
 					return Redirect::to("/forums/topic/".$post_topic->id);
 				} else {
 					return Redirect::to("/forums/".$post_forum->id);
 				}
			} else {	
				return Response::json(["success" => false, "message" => "Insufficient priviledges."]);
			}
		} else {
			return Response::json(["success" => false, "message" => "No post found with that ID."]);
		}
	}

	public function alertSTs() {
		$topic = ForumTopic::find(Input::get("topic"));
		$user = Auth::user();
		if($user->isStoryteller()) {
			$sendTo = [];
			$message = Input::get("alert-comment");
			foreach(User::listStorytellers() as $st) {
				$response = Input::get("st-alert-".$st->id);
				if($response == "on") $sendTo[] = $st;
			}
			foreach($sendTo as $st) {
				$st->sendMessage(null, "Carpe Noctem Topic Alert", "Hello, $st->username,<br><br>This message has been sent to you by $user->username to".
				" your bring attention to the topic <a href='http://larp3.acceptableice.com/forums/topic/$topic->id'>$topic->title</a>.".($message ? " $user->username had this to say about the topic:<br><br>".
				"<blockquote>$message</blockquote>" : ""));
			}
			return Redirect::to('/forums/topic/'.$topic->id);
		} else {
			return Response::json(["success" => false, "message" => "Insufficient priviledges."]);
		}
	}

}
