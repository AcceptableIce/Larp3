@extends('dashboard')


@section('dashboard-script')
  self.activeTab("todo");
  $(document).foundation();
@stop

@section('dashboard-style')
.st-incomplete-row {
	padding-right: 0;
}

.unread-topics {
	width: 25px;
	height: 25px;
	background-color: #e0e0e0;
	display: inline-block;
	top: 17px;
	float: left;
}

.unread-topics.unread {
	background-color: #A1D490;
}

table {
	width: 100%;
}

@stop

@section('dashboard-content')
<? 
	$user = Auth::user(); 
	$topics = ForumTopicReminder::where('user_id', $user->id)->get()
?>
<div class="row left">
	<div class="small-12 columns">
		<h2>To-do List</h2>
		<p>
			You have {{$topics->count()}} topic{{$topics->count() == 1 ? '' : 's'}} on your to-do list.
		</p>
		<table>
			<thead>
				<tr>
					<td></td>
					<td>Thread</td>
					<td>Last Post</td>
					<td>Last Updated</td>
				</tr>
			</thead>
			<tbody>
			@foreach($topics as $reminder)
				<? $post = $reminder->topic->lastUpdatedPostForUser($user->id); ?>
				<tr>
					<td class="st-incomplete-row">
						<div class="unread-topics {{$reminder->topic->hasUnreadPosts($user->id) ? 'unread' : ''}}"></div>
					</td>
					<td><a href="{{$reminder->topic->getLinkForLastPost($user)}}">{{$reminder->topic->title}}</a></td>
					<td>{{$post->poster->username}}</td>
					<td>{{ Helpers::timestamp($post->created_at) }}</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
</div>
@stop
@stop