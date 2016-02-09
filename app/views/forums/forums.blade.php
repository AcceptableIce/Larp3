@extends('forums/forumLayout')
@section('title', 'Forums')
@section('forum-style') 
<style type="text/css">

</style>
@stop
@section('forum-script')
	function fireSearch() {
		document.location = "/forums/search/" + $("#search-query").val();
	}
	$(".search-button").click(function() {
		fireSearch();
	});

	$("#search-query").keypress(function(e) {
		if(e.which == 13) fireSearch();
	});
@stop
@section('forum-content')
<h2>Carpe Noctem Boards</h2>
@if(Auth::user()->isStoryteller())
<div class="search-container">
  <div class="row collapse">
    <div class="small-10 columns">
      <input type="text" id="search-query" placeholder="Search query...">
    </div>
    <div class="small-2 columns">
      <a href="#" class="button postfix search-button"><i class="icon-search"></i></a>
    </div>
  </div>
</div>
@endif
<? $user_id = Auth::user()->id; ?>
@foreach(Auth::user()->forumListing() as $category => $list)
	<div class="forum-category">
		<div class="forum-title">{{$category}}</div>
		@foreach($list as $forum)
		<div class="topic-row">
			<div class="unread-topic-container"><div class="unread-topics {{$forum->hasUnreadPosts($user_id) ? 'unread' : ''}}"></div></div>
			<div class="topic-row-title">
				<a href="/forums/{{$forum->id}}">{{$forum->name}}</a><br>
				<div class="forum-description">{{$forum->description()}}</div>
			</div>
			<div class="topic-row-data">
				<? $topics = $forum->topicCountForUser($user_id); ?>
				{{$topics}} topic{{$topics == 1 ? '' : 's'}}<br>
				<? $posts = $forum->postCountForUser($user_id); ?>
				{{$posts}} post{{$posts == 1 ? '' : 's'}}<br>	
			</div>
			<div class="topic-row-last-post">
				<? $topic = $forum->lastUpdatedTopicForUser($user_id); ?>
				@if($topic)
					<? $last_post = ForumTopic::find($topic->topic_id)->lastUpdatedPostForUser($user_id); ?>
					<span class="hide-for-small">Last updated </span> {{$last_post->created_at->diffForHumans() }}<br><span class="hide-for-small">by </span> {{$last_post->poster->mailtoLink()}}
				@endif
			</div>
		</div>
		@endforeach
		<a class="mark-category-read right" href="/forums/category/{{$forum->category_id}}/read">Mark category as read.</a>

	</div>
@endforeach
<div class="forum-category">
	<div class="forum-title">Forum Statistics</div>
	<div class="forum-statistics-box">
		<div class="online-users">
			<b>The following users are online:</b>
			<? $users = User::where('last_online', '>=', new DateTime('2 minutes ago'))->get(); ?>
			@foreach($users as $index => $u) 
			{{$u->username}}{{$index != $users->count() - 1 ? "," : ""}}
			@endforeach
		</div>
		<div class="columns small-3 forum-statistic">
			<label>Total Users</label>
			<div class="forum-statistic-value">{{ User::count(); }}</div>
		</div>
		<div class="columns small-3 forum-statistic">
			<label>Total Topics</label>
			<div class="forum-statistic-value">{{ ForumTopic::count(); }}</div>
		</div>		
		<div class="columns small-3 forum-statistic">
			<label>Total Posts</label>
			<div class="forum-statistic-value">{{ ForumPost::count(); }}</div>
		</div>
		<div class="columns small-3 forum-statistic">
			<label>Newest User</label>
			<? $newest_user = User::orderBy('created_at', 'desc')->first(); ?>
			<div class="forum-statistic-value">{{ $newest_user ? $newest_user->username : "None" }}</div>
		</div>	
		<div class="spacer"></div>			
	</div>
</div>

@stop