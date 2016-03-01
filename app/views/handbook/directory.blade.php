<? 
	$user = Auth::user();
	$st = $user->isStoryteller(); 
	$query = $st ? HandbookPage::orderBy('created_at', 'DESC') : HandbookPage::where('created_by', $user->id);
	$pagination = $query->paginate(15); 
?>

@extends('layout')
@section('title', 'Handbook Directory')
@section('includes')
<style type="text/css">
	.handbook-pagination {
		float: right;
		margin-top: -40px;
	}

	.handbook-page {
		padding: 10px 10px;
		border: 1px solid #c0c0c0;
		border-radius: 3px;
		margin-bottom: 10px;
	}

	.handbook-title {
		font-size: 1.3em;
	}

	.handbook-preview {
		overflow: hidden;
		font-size: 0.9em;
		max-height: 40px;
		width: 100%;
		color: #666;
		text-overflow: ellipsis;
	}

	.handbook-creator {
		color: #333;
		font-size: 0.9em;
		margin-top: 5px;
	}

	.handbook-permissions {
		font-size: 0.8em;
		color: #666;
		margin-top: 2px;
	}
</style>
@stop
@section('content')
<div class="row">
	<h2>{{$st ? "Handbook Directory" : "My Handbook Pages"}}</h2>
	<div class="handbook-pagination">{{$pagination->links()}}</div>

	@foreach($pagination as $page)
	<div class="handbook-page">
		<div class="handbook-title">
			<a href="/handbook/{{$page->getUrlReadyLink($page->title)}}">{{$page->title}}</a>
		</div>
		<div class="handbook-preview">{{{substr($page->body, 0, 400)}}}</div>
		@if($st)
			<div class="handbook-creator">Created by {{$page->createdBy->username}}</div>
			@if($page->read_permission && $page->readPermission->hasRestrictions())
				<div class="handbook-permissions">
					Read restricted to {{$page->readPermissionList()}}
				</div>
			@endif
			@if($page->write_permission && $page->writePermission->hasRestrictions())
				<div class="handbook-permissions">
					Write restricted to {{$page->writePermissionList()}}
				</div>
			@endif			
		@endif

	</div>
	@endforeach
</div>
@stop