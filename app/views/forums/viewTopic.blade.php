@extends('forums/forumLayout')
<?
	$forum = $topic->forum; 
	$user = Auth::user();
	
	$listing = $topic->postsForUser($user->id)->with("poster")->paginate(10);
	$i = $listing->getFrom() - 1;	
?>
@section('title', $topic->title)
@section('forum-style') 

@stop

@section('forum-script')
	var editor = tinymce.init({
		selector: "#quick-post",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect removeformat | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : '',
    	statusbar: false,
    	menubar: false,
    	setup: function(editor) {
	    	editor.on('keydown', function(e) {
		    	if((e.metaKey || e.ctrlKey) && e.keyIdentifier === "Enter") {
		    		e.preventDefault();
					e.stopPropagation();
			    	$("#quick-reply-form").submit();
		    	}
	    	});
    	}
	});

	self.promptDeleteId = ko.observable();
	self.promptDeleteThread = ko.observable(false);
	self.promptDelete = function(id, thread) {
		self.promptDeleteId(id);
		self.promptDeleteThread(thread);
		$('#delete-modal').foundation('reveal', 'open');
	}
	
	self.stopDelete = function() {
		$('#delete-modal').foundation('reveal', 'close');
	}


	self.showAlertSTsModal = function(id) {
		$("#alert-sts-modal").foundation('reveal', 'open');
	}
	
	self.currentUser = ko.observable({ 
		id: {{$user->id}},
		username: "{{$user->username}}"
	});
	
	self.postLikes = ko.observableArray([]);
	@foreach($topic->postsForUser($user->id)->get() as $post)
		<? $likes = $post->likes()->join("users", "users.id", "=", "user_id")->select(DB::raw('post_id, users.id as user_id, username'))->orderBy('username')->get(); ?>
		self.postLikes.push({ 
			id: {{$post->id}},
			likes: {{$likes->toJson()}}
		});
	@endforeach
	
	self.hasLikedPost = function(id) {
		for(var i = 0; i < self.postLikes().length; i++) {
			var post = self.postLikes()[i];
			if(post.id == id) {
				for(var j = 0; j < post.likes.length; j++) {
					var like = post.likes[j];
					if(like.user_id == self.currentUser().id) return true;
				}
			}
		}
		return false;
	}
	
	self.getLikedList = function(id) {
		var liked = [];
		var userLiked = false;
		for(var i = 0; i < self.postLikes().length; i++) {
			var post = self.postLikes()[i];
			if(post.id == id) {
				for(var j = 0; j < post.likes.length; j++) {
					var like = post.likes[j];
					if(like.user_id == self.currentUser().id) {
						userLiked = true;
					} else {
						liked.push(like.username);
					}
				}
			}
		}
		
		if(liked.length == 0 && !userLiked) {
			return "";
		}
		if(userLiked && liked.length == 0) {
			return "You liked this post.";
		} else if(userLiked && liked.length == 1) {
			return "You and " + liked[0] + " liked this post.";
		} else if(userLiked && liked.length > 1) {
			return "You, " + liked.slice(0, liked.length - 1).join(", ") + ", and " + liked[liked.length - 1] + " liked this post.";
		} else if (!userLiked && liked.length == 1) {
			return liked[0] + " liked this post.";
		} else if (!userLiked && liked.length == 2) {
			return liked.join(" and ") + " liked this post.";
		} else if(!userLiked && liked.length > 2) {
			return liked.slice(0, liked.length - 1).join(", ") + ", and " + liked[liked.length - 1] + " liked this post.";
		}
		return "";
	}
	
	self.toggleLike = function(id) {
		$.post("/forums/post/" + id + "/toggleLike");
		for(var i = 0; i < self.postLikes().length; i++) {
			var post = self.postLikes()[i];
			if(post.id == id) {
				var found = false;
				for(var j = 0; j < post.likes.length; j++) {
					var like = post.likes[j];
					if(like.user_id == self.currentUser().id) {
						post.likes.splice(j);
						j--;
						found = true;
					}
				}
				console.log(found);
				if(!found) {
					post.likes.push({
						user_id: self.currentUser().id, 
						user: { 
							username: self.currentUser().username
						} 
					});
				}
			}
		}
		var temp = self.postLikes();
		self.postLikes({});
		self.postLikes(temp);
	}
	
	var startPoint = {{$i + 1}};
	var selectedPost = -1;
	$(document).keydown(function(e) {
		if($("textarea").is(":focus")) return;
		var keyCode = e.keyCode;
		console.log(keyCode);
		if(keyCode >= 48 && keyCode <= 57) {
			if(keyCode == 48) keyCode = 58; //Offset 0 to 10
			var offset = startPoint + (keyCode - 49);
			$(document).scrollTop($("#post" + offset).offset().top - 50);
			selectedPost = offset;
			$(".post-row").removeClass("active")
			$("#post-row-" + offset).addClass("active");
		} else {
			switch(keyCode) {
				case 82: // R
					if(!e.metaKey && !e.ctrlKey) {
						e.preventDefault();
						e.stopPropagation();	
						tinyMCE.activeEditor.focus();
						$(document).scrollTop($(".quick-reply").offset().top);
					}
					break;
				case 69: // Ctrl+E
					e.preventDefault();
					e.stopPropagation();
					if((e.metaKey || e.ctrlKey) && selectedPost > 0) {
						$("#edit-post-" + selectedPost).click();
					}
					break;
				case 65: // Ctrl+A
					e.preventDefault();
					e.stopPropagation();
					if((e.metaKey || e.ctrlKey) && selectedPost > 0) {
						$("#quote-post-" + selectedPost).click();
					}
					break;
				case 83: //Ctrl+S
					e.preventDefault();
					e.stopPropagation();
					if(e.metaKey || e.ctrlKey) {
						$(".toggle-complete").first().click();
					}
					break;
			}
		}
	})
@stop

@section('forum-content')

<div id="delete-modal" class="reveal-modal" data-reveal aria-labelledby="deleteModalTitle" aria-hidden="true" role="dialog">
  <h2 id="deleteModalTitle">Delete <span data-bind="text: promptDeleteThread() ? 'Thread' : 'Post'"></span>?</h2>
  <p>Are you sure you wish to delete this post? <b>This cannot be undone.</b></p>
  <div data-bind="visible: $root.promptDeleteThread()"><p><b>This will delete the entire thread.</b></p></div>
  <hr>
  <form method="post" action="/forums/post/delete">
  	<button class="button small" data-bind="click: stopDelete">Nevermind.</button>
  	<input type="hidden" name="id" data-bind="value: $root.promptDeleteId" />
 	<input type="submit" class="button alert small" value="Delete">
  </form>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
@if($user->isStoryteller())
<div id="alert-sts-modal" class="reveal-modal" data-reveal aria-labelledby="alertSTsTitle" aria-hidden="true" role="dialog">
  <h2 id="alertSTsTitle">Alert Storytellers</h2>
  <p>Select the Storytellers you would like to alert.</b></p>
  <form method="post" action="/forums/alert">
  @foreach(User::listStorytellers() as $st)
	<div class="columns small-3">  
	  	<label for="st-alert-{{$st->id}}" class="alert-label">{{$st->username}}</label>	
	  	<div class="switch alert-switch">
		  <input id="st-alert-{{$st->id}}" name="st-alert-{{$st->id}}" type="checkbox" checked>
		  <label for="st-alert-{{$st->id}}"></label>
		</div> 
	</div>
  @endforeach
  <label for="alert-comment">Leave a Comment:
  	<textarea id="alert-comment" name="alert-comment"></textarea>
  </label>
  <hr>
  	<input type="hidden" name="topic" value="{{$topic->id}}" />
 	<input type="submit" class="button warning right" value="Alert">
  </form>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
@endif

<ul class="button-group breadcrumb-group">
	<li><a href="/forums" class="button small secondary"><i class="icon-home"></i></a></li>
	<li><a href="/forums/{{$topic->forum->id}}" class="button small secondary">{{$topic->forum->name}}</a></li>
	<li><a href="#" class="button small secondary">{{$topic->title}}</a></li>

</ul>
<? $reply_permission = !$forum->reply_permission || $user->hasPermissionById($forum->reply_permission); ?>
@if($reply_permission)
<a href="/forums/topic/{{$topic->id}}/post" class="button info small right"><i class="icon-plus"></i> Post Reply</a>
@endif
<h3 class="topic-title">{{$topic->title}}</h3>
<? 	$added_users = $topic->addedUsers; 
	$added_successfully = [];
	$added_failure = [];
	$before_time = [];
	foreach($added_users as $u) {
		if($u->user->canAccessTopic($topic->id)) {
			$added_successfully[] = $u->user->username;
		} else {
			$added_failure[] = $u->user->username;
		}
	}
	if($forum->is_private) {
		foreach(ForumCharacterPermission::where(['forum_id' => $forum->id])->get() as $c) {
			if(!$c->character->owner->canAccessTopic($topic->id)) $before_time[] = $c->character->name;
		}
	}
	
?>
@if(count($added_successfully) > 0 || count($added_failure) > 0 || count($before_time) > 0)
	<div class="added-user-list">
		@if(count($added_successfully) > 0)
			{{Helpers::nl_join($added_successfully)}} can also see this thread.<br>
		@endif
		@if(count($added_failure) > 0)
			{{Helpers::nl_join($added_failure)}} {{count($added_failure) == 1 ? "has" : "have"}} been added, but can't view the thread.<br>
		@endif
		@if(count($before_time) > 0)
			{{Helpers::nl_join($before_time)}} {{count($before_time) == 1 ? "has" : "have"}} access to the board, but this thread predates them.<br>
		@endif				
	</div>
@endif

<div class="topic-pagination">{{$listing->links()}}</div>
<? 
	//variable hoisting
	$first_poster_id = $topic->firstPost->posted_by; 
	$isStoryteller = $user->isStoryteller();
?>

@foreach($listing as $post)
<? $poster = $post->poster; ?>
<? $i++ ?>
<a id="post{{$i}}"></a>
<div id="post-row-{{$i}}" class="row post-row small-collapse medium-uncollapse">
	<div class="small-12 medium-3 columns post-user-column">
		<div class="user-card">
			<div class="user-name">
				{{$poster->mailtoLink()}}
				<? 	$active_character = $poster->activeCharacter();
					$poster_st = $poster->isStoryteller(); ?>
				@if($active_character && !$poster_st)
					<div class="character-name">{{$active_character->printName()}}</div>
				@endif
			</div>
			<div class="user-data">
				<? 
					
					if(!$poster_st) {
						$clan = $active_character ? $active_character->clan()->first() : null; 
						if($clan) $clanName = $clan->hiddenDefinition->name;
					}
				?>
				@if($poster_st)
				<img class="clan-image" src="/img/clans/storyteller.png" />
				@else
					@if($clan)<img class="clan-image" src="/img/clans/{{strtolower(str_replace(' ', '_', $clanName))}}.gif" />@endif
				@endif
				<div class="user-statistics">
					@if($poster_st)
						<div class="user-statistic-clan">Storyteller</div>
					@elseif($clan)
						<div class="user-statistic-clan">Clan {{$clanName}}</div>
					@endif
					Posts: {{$poster->countPosts()}}
				</div>
				<div class="spacer"></div>
			</div>
		</div>
	</div>
	<div class="small-12 medium-9 columns">
		<div class="post-data">
			<div class="post-title">
				{{$i}}
				<span class="right">Posted {{Helpers::timestamp($post->created_at)}}</span>
			</div>
			<? 	$storyteller_reply = $isStoryteller && $poster_st && $forum->asymmetric_replies && !$post->is_storyteller_reply; 
				$user_reply = $isStoryteller && $forum->asymmetric_replies && !$poster_st && !$storyteller_reply;
			?>
			<div class="post-body {{$storyteller_reply ? 'storyteller-reply' : ''}} {{$user_reply ? 'user-reply' : ''}}">
				<div class="post-content">{{ForumPost::render($post->body)}}</div>

				@foreach($post->edits()->orderBy('created_at')->take(3)->get() as $edit) 
					<div class="edit-notification"><i>Edited {{Helpers::timestamp($edit->created_at)}} by {{$edit->user->username}}</i></div>
				@endforeach
				<? $signature = $post->poster->getSettingValue("Forum Signature"); ?>
				@if($signature) 
					<div class="forum-signature">{{$signature}}</div>
				@endif
				<div class="post-footer">
					<div class="post-likes">
						<i class="icon-thumbs-up" data-bind="css: {'active': $root.hasLikedPost({{$post->id}}) }, 
							click: function() { $root.toggleLike({{$post->id}}) }">
						</i>
						<div class="post-like-list" data-bind="text: $root.getLikedList({{$post->id}})"></div>
					</div>
					<div class="post-options">
						@if($post->posted_by == $user->id || $isStoryteller) 
							<a href="#" data-bind="click: function() { promptDelete({{$post->id}}, {{$i == 1 ? "true " : "false"}}); }">
								<button class="button post-option alert tiny">Delete</button>
							</a>
						@endif		
						@if($isStoryteller)
							<a href="/forums/topic/{{$topic->id}}/toggleSticky">
							@if($topic->is_sticky)<button class="button post-option tiny">Unstick</button>
							@else <button class="button post-option tiny">Stick</button>
							@endif
							</a>
							<button data-bind="click: $root.showAlertSTsModal" class="button post-option tiny warning">Alert STs</button>
						@endif
						<a href="/forums/topic/{{$topic->id}}/post?quote={{$post->id}}">
							<button id="quote-post-{{$i}}" class="button post-option tiny">Quote</button>
						</a>
						<a href="/forums/topic/{{$topic->id}}/toggleReminder">
							<button id="quote-remind-{{$i}}" class="button post-option tiny">
								{{ForumTopicReminder::where(['topic_id' => $topic->id, 'user_id' => $user->id])->exists() ? 'Remove from Todo' : 'Add to Todo'}}
							</button>
						</a>
						@if($post->posted_by == $user->id || $isStoryteller) 
							<a href="/forums/{{$i == 1 ? 'topic/'.$topic->id : 'post/'.$post->id}}/edit">
								<button id="edit-post-{{$i}}" class="button post-option tiny">Edit</button>
							</a>
						@endif
						@if(($forum->asymmetric_replies || $forum->id == 35) && $isStoryteller) <!-- Contact the STs -->
							<a href="/forums/topic/{{$topic->id}}/toggleComplete">
								@if($topic->is_complete) 
									<button class="button post-option tiny warning toggle-complete">Mark Incomplete</button>
								@else
									<button class="button post-option tiny success toggle-complete">Mark Complete</button>
								@endif
							</a>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endforeach
<div class="topic-pagination topic-pagination-bottom">{{$listing->links()}}</div>

@if($listing->getCurrentPage() == $listing->getLastPage() && $reply_permission)
<div class="row post-row small-collapse medium-uncollapse quick-reply-row">
	<div class="small-4 medium-3 columns quick-reply-card">
		<div class="user-card">
			<div class="user-name">
				{{$user->username}}
				<? $active_character = $user->activeCharacter(); ?>
				@if($active_character && !$user->isStoryteller())
					<div class="character-name">{{$active_character->name}}</div>
				@endif
			</div>
		</div>
	</div>
	<div class="small-12 medium-9 columns">
		<div class="post-data">
			<div class="post-title">
				Quick Reply
			</div>
			<div class="post-body quick-reply">
				<form method="post" id="quick-reply-form" action="/forums/reply/post">
					<input type="hidden" value="{{$topic->id}}" name="topic_id" />
					<textarea class="topic-body quick-reply-body" name="body" id="quick-post" placeholder="Type your message here..."></textarea>
					<div class="post-actions">
						@if($isStoryteller && $forum->asymmetric_replies)
							<div class="switch post-option-switch">
							  <input id="st-switch" name="st-reply" type="checkbox">
							  <label for="st-switch"></label>
							</div> 
							<label for="st-switch" class="post-option-label">Storyteller Response</label>						
						@endif
							
						<input type="submit" class="button small success button-submit right quick-reply-submit" value="Submit" />
					</div>
				</form>			
			</div>
		</div>
	</div>
</div>
@endif

<div class="row topic-options">
	<div class="small-12 columns">
		@if($reply_permission)
			<a href="/forums/topic/{{$topic->id}}/post" class="button info small right bottom-reply"><i class="icon-plus"></i> Post Reply</a>
		@endif
		<div class="topic-option">
			<a href="/forums/topic/{{$topic->id}}/toggleWatch">@if($topic->userIsWatching($user->id)) Stop Watching This Topic @else Watch This Topic @endif</a>
		</div>
	</div>

</div>
@stop