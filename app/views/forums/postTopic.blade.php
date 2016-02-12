@extends('forums/forumLayout')
@section('title', 'Post Topic')
@section('forum-style') 
<style type="text/css">

</style>
@stop
@section('forum-script')
	tinymce.init({
		selector: "#post",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : ''
	});
	
	self.allUsers = ko.observableArray([]);
	self.selectedUsers = ko.observableArray([]);
	self.selectedUser = ko.observable();
	<? $added_users = ForumTopicAddedUser::where('topic_id', $topic_id)->get(); ?>
	@foreach(Character::activeCharacters()->get() as $c)
		var userObject = {id: "{{$c->owner->id}}", name: "{{$c->owner->username}}"};
		self.allUsers.push(userObject);
		@if($added_users->filter(function($item) use ($c) { return $item->user_id == $c->owner->id; })->first())
		self.selectedUsers.push(userObject);
		@endif
	@endforeach
	
	self.availableUsers = ko.computed(function() {
		var out = [];
		for(var i = 0; i < self.allUsers().length; i++) {
			var item = self.allUsers()[i];
			if(self.selectedUsers().indexOf(item) == -1) out.push(item);
		}
		return out;
	});
	
	self.addedUserOutput = ko.computed(function() {
		var selUserIds = [];
		for(var i = 0; i < self.selectedUsers().length; i++) {
			selUserIds.push(self.selectedUsers()[i].id);
		}
		return selUserIds.join(",");
	})
	
	self.addUser = function() {
		self.selectedUsers.push(self.selectedUser());
	};
	
	self.removeUser = function(item) {
		self.selectedUsers.remove(item);
	};
	 
@stop
@section('forum-content')
<? if(isset($topic_id)) {
	$topic = ForumTopic::find($topic_id);
	$forum = $topic->forum;
} else {
	$forum = Forum::find($id); 
} ?>
<ul class="button-group breadcrumb-group">
	<li><a href="/forums" class="button small secondary"><i class="icon-home"></i></a></li>
	<li><a href="/forums/{{$forum->id}}" class="button small secondary">{{$forum->name}}</a></li>
	<li><a href="#" class="button small secondary">New Topic</a></li>

</ul>
<form method="post" action="/forums/topic/post">
	@if($forum->id == 35)
		<div class="panel callout bug-report-disclaimer"><b>Bug reports are public.</b> Do not include any information that you don't wish other players to see.
		If you can't report the bug without revealing sensitive information, post to us in <a href="/forums/36">General Messages</a>.</div>
	@endif
	<div class="forum-title">{{isset($topic_id) ? "Edit" : "New"}} Topic</div>

	<input type="hidden" value="{{$forum->id}}" name="forum_id" />
	@if(isset($topic_id)) <input type="hidden" value="{{$topic->id}}" name="topic_id" /> @endif
	<input type="text" class="topic-field" name="title" placeholder="Subject" value="{{isset($topic) ? $topic->title : ''}}" />
	<div class="topic-divider"></div>
	<textarea id="post" class="topic-body" name="body" placeholder="Type your message here...">
		<?  if(isset($topic)) {
				echo $topic->firstPost->body;
			} else if($forum->id == 35) { 
				echo 	"<strong>Status: &nbsp;</strong>New<br /><strong>Comment:<br />".
						"</strong><br /><strong>Description<br /></strong><br /><br /><strong>Steps to Reproduce".
						"</strong><br /><br /><br /><strong>Relevant Items</strong><br />".
						"<br /><br /><strong>Related Issues</strong><br />"; 
			}//35 = Website Issues ?>
	</textarea>
	@if(Auth::user()->isStoryteller())
		<div class="post-as-box">
			<label for="post-as" class="post-as-label">Post As:</label>
			<select name="post-as" class="post-as-selector">
				@foreach(Character::activeCharacters()->get() as $c)
					<option value="{{$c->user_id}}" {{Auth::user()->id == $c->user_id ? "selected" : ""}}>{{$c->owner->username}}</option>
				@endforeach
				@foreach(User::listStorytellers() as $u)
					<option value="{{$u->id}}" {{Auth::user()->id == $u->id ? "selected" : ""}}>{{$u->username}} (ST)</option>
				@endforeach
			</select>
		</div>		
	@endif
	<input type="hidden" name="added-users" data-bind="value: addedUserOutput" />
	<input type="submit" class="button success button-submit right" value="Submit" />
	@if(Auth::user()->isStoryteller())
	<hr>
	<h4>Added Users</h4>
	<p>Allow additional users to view this thread. They must still have access to the forum.</p>
	<div class="row">
		<div class="small-12 columns" data-bind="foreach: selectedUsers">
			<div class="panel user-list-panel">
				<div class="remove-button" data-bind="click: $root.removeUser">&times</div>
				<span data-bind="text: name"></span>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="small-8 columns">
			<select data-bind="value: selectedUser, options: availableUsers, optionsText: 'name'"></select>&nbsp;&nbsp;
		</div>
		<div class="small-4 columns">
			<input type="button" class="button tiny success" data-bind="click: $root.addUser" value="Add to Thread" />
		</div>
	</div>
	@endif
</form>
@stop