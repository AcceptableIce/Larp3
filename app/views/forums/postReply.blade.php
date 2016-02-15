@extends('forums/forumLayout')
@section('title', 'Post Reply')
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
<? 
$user = Auth::user();
$quote = Input::get('quote');
$quoteValue = "";
if(isset($quote)) {
	$postToQuote = ForumPost::find($quote);
	if($postToQuote) {
		$quoteValue = "<blockquote><cite>".$postToQuote->poster->username." wrote:</cite>".$postToQuote->body."</blockquote><br>";
	}
}
if(isset($post_id)) {
	$post = ForumPost::find($post_id);
	$topic = $post->topic;
} else {
	$topic = ForumTopic::find($id);
} ?>
<ul class="button-group breadcrumb-group">
	<li><a href="/forums" class="button small secondary"><i class="icon-home"></i></a></li>
	<li><a href="/forums/{{$topic->forum->id}}" class="button small secondary">{{$topic->forum->name}}</a></li>
	<li><a href="/forums/topic/{{$topic->id}}" class="button small secondary">{{$topic->title}}</a></li>	
	@if($topic->forum->reply_permission == null || $user->hasPermissionById($topic->forum->reply_permission))
		<li><a href="#" class="button small secondary">Post Reply</a></li>
	@endif

</ul>
<form method="post" action="/forums/reply/post">
	<div class="forum-title">{{isset($post_id) ? "Edit" : "Post"}} Reply to "{{$topic->title}}"</div>
	@if(isset($id))<input type="hidden" value="{{$id}}" name="topic_id" />@endif
	@if(isset($post_id)) <input type="hidden" value="{{$post_id}}" name="post_id" /> @endif
	<textarea class="topic-body" name="body" placeholder="Type your message here..." id="post">{{isset($post) ? $post->body : $quoteValue}}</textarea>
	@if(Auth::user()->isStoryteller() && $topic->forum->asymmetric_replies)
		<div class="switch post-option-switch">
		  <input id="st-switch" name="st-reply" type="checkbox">
		  <label for="st-switch"></label>
		</div> 
		<label for="st-switch" class="post-option-label">Storyteller Response</label>						
	@endif
	<div class="switch post-option-switch">
	  <input id="watch-switch" name="watch" type="checkbox">
	  <label for="watch-switch"></label>
	</div> 
	<label for="watch-switch" class="post-option-label">Watch this Topic</label>	
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