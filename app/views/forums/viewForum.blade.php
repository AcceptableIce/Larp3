@extends('forums/forumLayout')
<? 
$forum = Forum::find($id); 
?>
@section('title', $forum->name)
@section('forum-style') 

@stop
@section('forum-script')

@section('forum-content')


<ul class="button-group breadcrumb-group">
	<li><a href="/forums" class="button small secondary"><i class="icon-home"></i></a></li>
	<li><a href="#" class="button small secondary">{{$forum->name}}</a></li>
</ul>

@if($forum->topic_permission == null || Auth::user()->hasPermissionById($forum->topic_permission))
<a href="/forums/{{$id}}/post" class="button secondary small right"><i class="icon-plus"></i> New Topic</a>
@endif

<h3 class="topic-title">{{$forum->name}}</h3>
@if(ForumCharacterPermission::where('forum_id', $forum->id)->count() > 0)
	<div class="character-access-list">Characters with access:
	<? $list = [];
	foreach(ForumCharacterPermission::where('forum_id', $forum->id)->get() as $perm) $list[] = $perm->character->name;
	?>
		{{implode(", ", $list)}} 
	</div>
@endif

<? 	
	$user = Auth::user();
	$topics = $forum->topicsForUserInOrder($user->id);
	$pagination = $topics->paginate(15);
?>
<div class="topic-pagination">{{$pagination->links()}}</div>
@if($pagination->count() == 0) 
	<p style="clear: both;">There are no topics here. Be the first to post!</p>
@else
	@if(strlen($forum->list_header) > 0)
		<div class="list-header">{{ForumPost::render($forum->list_header)}}</div>
	@endif
	<div class="forum-title">Topics</div>
	<div class="topics-list">
	@foreach($pagination as $tp_data)

		<? $topic = ForumTopic::find($tp_data->topic_id); 
			if(!$topic) continue;
		?>
	<div class="topic-row {{$topic->is_complete && $user->isStoryteller() ? 'completed' : ''}}">
		<div class="unread-topic-container"><div class="unread-topics {{$topic->hasUnreadPosts($user->id) ? 'unread' : ''}}"></div></div>
		<div class="topic-row-title">
			<a class="topic-name" href="/forums/topic/{{$topic->id}}">
				@if($topic->is_complete && ($forum->id == 35 || $user->isStoryteller()))<label class="label success">Complete</label> @endif
				@if($topic->is_sticky) <label class="label success">Stickied</label>@endif 
				{{$topic->title}}
			</a>
			<a href="{{$topic->getLinkForLastPost()}}">
				<i class="to-page-link icon-right-open"></i>
				<i class="to-page-link icon-right-open"></i>
			</a>
			
			<br>
			by {{$topic->posts()->first()->poster->mailtoLink()}}
			<? $added_users = $topic->addedUsers; ?>
			@if($added_users->count() > 0 && $user->isStoryteller()) 
				<? $list = []; foreach($added_users as $u) $list[] = $u->user->username; ?>
				<div class="added-user-subheader">Added: {{Helpers::nl_join($list)}}</div>
			@endif
		</div>
		<div class="topic-row-data">
			<? $count = $topic->postsForUser($user->id)->count(); ?>
			{{$count}} post{{$count == 1 ? '' : 's'}}<br>
			<? $views = $topic->views; ?>
			{{$views}} view{{$views == 1 ? '' : 's'}}<br>			
		</div>
		<div class="topic-row-last-post">
			<? $post = $topic->lastUpdatedPostForUser($user->id); ?>
			<span class="hide-for-small">Last updated</span> {{ Helpers::timestamp($post->created_at) }}<br><span class="hide-for-small"> by </span> {{$post->poster->mailtoLink()}}
		</div>
	</div>
	@endforeach
	</div>
	<a class="mark-read right" href="/forums/{{$id}}/read">Mark forum as read.</a>
@endif
@stop