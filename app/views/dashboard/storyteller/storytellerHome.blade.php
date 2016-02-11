@extends('dashboard/storyteller')
@section('title', 'Storyteller Dashboard')
@section('dashboard-style')
 .st-incomplete-row .unread {
 	margin-left: 5px;
	}
.unread-topics {
	width: 25px;
	height: 25px;
	background-color: #e0e0e0;
	display: inline-block;
	top: 17px;
	margin-right: 20px;
}

.unread-topics.unread {
	background-color: #A1D490;
}

.storyteller-content {
	max-width: 1600px;
}

table {
	width: 100%;
}
@stop
@section('storyteller-script')

	self.promptDeleteName = ko.observable();
	self.promptDeleteId = ko.observable();
	self.promptDeleteField = ko.observable();
	self.promptDelete = function(name, id) {
		self.promptDeleteField("");
		self.promptDeleteName(name);
		self.promptDeleteId(id);
		$('#delete-modal').foundation('reveal', 'open');
	}

	self.canDelete = ko.computed(function() {
		return self.promptDeleteField() == self.promptDeleteName();
	});

	self.completeDelete = function() {
		$.ajax({
			url: "/characters/delete",
			type: 'post',
			data: {
				characterId: self.promptDeleteId()
			},
			success: function(data) {
				document.location = "/dashboard/characters";
			}
		});
	}
@stop
@section('storyteller-content')
<? $user = Auth::user(); ?>
<div class="row storyteller-content left">
	<h2>Storyteller Tools</h2>

	<div class="small-12 medium-7 columns">
		<h4>Incomplete Threads</h4>
		<? $topics = ForumTopic::where('is_complete', 0)->whereHas('forum', function($q) { $q->where('show_on_st_todo_list', true); })->orderBy('created_at', 'desc')->get(); ?>
		<p>There are currently <b>{{$topics->count()}}</b> incomplete threads. @if($topics->count() > 0) The oldest thread was created <b>{{$topics->last()->created_at->diffForHumans()}}</b>.@endif</p>
		<table class="responsive">
			<thead>
				<th></th>
				<th>Title</th>
				<th>Forum</th>
				<th>Posted By</th>
			</thead>
			<tbody>
				@foreach($topics as $topic)
				<tr>
					<td class="st-incomplete-row"><div class="unread-topics {{$topic->hasUnreadPosts($user->id) ? 'unread' : ''}}"></div></td>
					<td><a href="/forums/topic/{{$topic->id}}">{{$topic->title}}</a></td>
					<td><a href="/forums/{{$topic->forum->id}}">{{$topic->forum->name}}</a></td>
					<td>{{$topic->firstPost->poster->username}}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="small-12 medium-5 columns">
		<h4>Unresolved Forum Issues</h4>
		<? $topics = ForumTopic::where('is_complete', 0)->where(['forum_id' => 35, 'is_complete' => false, 'is_sticky' => false])->orderBy('created_at')->get(); ?>
		<p>There are currently <b>{{$topics->count()}}</b> unresolved issues. @if($topics->count() > 0) The oldest issue was created <b>{{$topics[0]->created_at->diffForHumans()}}</b>.@endif </p>
		<table class="responsive">
			<thead>
				<th></th>
				<th>Title</th>
				<th style="width: 20%">Age</th>
			</thead>
			<tbody>
				@foreach($topics as $topic)
				<tr>
					<td class="st-incomplete-row"><div class="unread-topics {{$topic->hasUnreadPosts($user->id) ? 'unread' : ''}}"></div></td>
					<td><a href="/forums/topic/{{$topic->id}}">{{$topic->title}}</a></td>
					<td>{{str_replace('ago' ,'', $topic->firstPost->created_at->diffForHumans())}}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
@stop
