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
	<input type="submit" class="button success button-submit right" value="Submit" />
</form>
@stop