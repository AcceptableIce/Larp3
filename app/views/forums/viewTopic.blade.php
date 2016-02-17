@extends('forums/forumLayout')
<? 	$topic = ForumTopic::find($id); 
	$forum = $topic->forum; 
	$user = Auth::user();
?>
@section('title', $topic->title)
@section('forum-style') 

@stop

@section('forum-script')
	tinymce.init({
		selector: "#quick-post",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect removeformat | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : '',
    	statusbar: false,
    	menubar: false
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
<a href="/forums/topic/{{$id}}/post" class="button info small right"><i class="icon-plus"></i> Post Reply</a>
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
<?
	$listing = $topic->postsForUser($user->id)->with("poster")->paginate(10);
	$i = $listing->getFrom() - 1;	
?>
<div class="topic-pagination">{{$listing->links()}}</div>


<? 
	//variable hoisting
	$first_poster_id = $topic->firstPost->posted_by; 
	$isStoryteller = $user->isStoryteller();
?>

@foreach($listing as $post)
<? $poster = $post->poster; ?>
<? $i++ ?>
<div class="row post-row small-collapse medium-uncollapse">
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
				<a id="post{{$i}}"></a>{{$i}}
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

				<div class="forum-signature">{{$post->poster->getSettingValue("Forum Signature")}}</div>
				<div class="post-options">
					@if(($forum->asymmetric_replies  || $forum->id == 35) && $isStoryteller) <!-- Contact the STs -->
						<a href="/forums/topic/{{$id}}/toggleComplete">
							@if($topic->is_complete) <button class="button post-option tiny warning">Mark Incomplete</button>
							@else <button class="button post-option tiny success">Mark Complete</button>
							@endif
						</a>
					@endif
					@if($isStoryteller)
						<a href="/forums/topic/{{$id}}/toggleSticky">
						@if($topic->is_sticky)<button class="button post-option tiny">Unstick</button>
						@else <button class="button post-option tiny">Stick</button>
						@endif
						</a>
						<button data-bind="click: $root.showAlertSTsModal" class="button post-option tiny warning">Alert STs</button>
					@endif
					<a href="/forums/topic/{{$id}}/post?quote={{$post->id}}"><button class="button post-option tiny">Quote</button></a>
					@if($post->posted_by == $user->id || $isStoryteller) 
						<a href="/forums/{{$i == 1 ? 'topic/'.$topic->id : 'post/'.$post->id}}/edit"><button class="button post-option tiny">Edit</button></a>
					@endif
					<a href="#" data-bind="click: function() { promptDelete({{$post->id}}, {{$i == 1 ? "true " : "false"}}); }"<button class="button post-option alert tiny">Delete</button></a>
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
				<form method="post" action="/forums/reply/post">
					<input type="hidden" value="{{$id}}" name="topic_id" />
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
			<a href="/forums/topic/{{$id}}/post" class="button info small right bottom-reply"><i class="icon-plus"></i> Post Reply</a>
		@endif
		<div class="topic-option">
			<a href="/forums/topic/{{$id}}/toggleWatch">@if($topic->userIsWatching($user->id)) Stop Watching This Topic @else Watch This Topic @endif</a>
		</div>
	</div>

</div>
@stop