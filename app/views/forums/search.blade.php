@extends('forums/forumLayout')
<? 	$listing = DB::table('forums_posts')->where('body', 'LIKE', '%'.$query.'%')->orderBy('created_at', 'desc')->paginate(15);
	$user = Auth::user(); 
	function br2nl( $input ) {
     $out = str_replace( "<br>", "\n", $input );
     $out = str_replace( "<br/>", "\n", $out );
     $out = str_replace( "<br />", "\n", $out );
     $out = str_replace( "<BR>", "\n", $out );
     $out = str_replace( "<BR/>", "\n", $out );
     $out = str_replace( "<BR />", "\n", $out );
     return $out;
}
?>
@section('title', "Search Results for \"$query\"")
@section('forum-style') 

@stop

@section('forum-script')
@stop

@section('forum-content')
<ul class="button-group breadcrumb-group">
	<li><a href="/forums" class="button small secondary"><i class="icon-home"></i></a></li>
	<li><a href="#" class="button small secondary">Search Results for "{{$query}}"</a></li>
</ul>

<h3 class="topic-title">Search Results for "{{$query}}"</h3>

<div class="topic-pagination search-pagination">{{$listing->links()}}</div>

@foreach($listing as $result)
<? $post = ForumPost::find($result->id); ?>
<div class="row search-result-row">
	<div class="small-12 medium-3 columns post-user-column search-user-column">
		<div class="user-card">
			<div class="user-name">
				{{$post->poster->username}}
			</div>
		</div>
	</div>
	<div class="small-12 medium-9 columns">
		<div class="post-data">
			<div class="post-title">
				<a href="{{$post->topic->getLinkForPostById($post->id)}}">{{$post->topic->title}}</a>
				<span class="right">Posted {{$post->created_at->diffForHumans()}}</span>
			</div>
			<div class="post-body">
				<div class="post-content search-content">{{nl2br(strip_tags(trim(br2nl($post->body))))}}</div>

				@foreach($post->edits()->orderBy('created_at')->take(3)->get() as $edit) 
					<div class="edit-notification">
						<i>Edited {{$edit->created_at->diffForHumans()}} by {{$edit->user->username}}</i>
					</div>
				@endforeach
			</div>
		</div>
	</div>
</div>
@endforeach
@stop