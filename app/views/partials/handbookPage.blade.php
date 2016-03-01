<?
$page = HandbookPage::getByTitle($title);	
$user = Auth::user();
?>

<div class="row">
	<div class="small-12 columns">
			@if($page && !$page->userCanRead($user))
				<h1>This Handbook Page is Hidden</h1>
				<p>You do not have permission to view this page.</p>
			@else
				<h1 class="handbook-title">@if($page && $page->read_permission && $page->readPermission->hasRestrictions()) 
					<i class="handbook-lock icon-lock" data-tooltip title="Restricted to {{$page->readPermissionList()}}"></i>
					@endif
					{{$page ? $page->title : "Page not found"}}</h1>
					@if(Session::get('redirect'))
						<div class="redirect">(Redirected from <a href="/handbook/{{HandbookPage::getURLReadyLink(Session::get('redirect'))}}?redirect=no">{{Session::get('redirect')}}</a>)</div>
					@endif
				@if($user)
					<ul class="button-group handbook-edit-options">
						@if(isset($showNewPage))
							<li><a class="button small new-page" href="/handbook/directory">{{Auth::user()->isStoryteller() ? "Directory" : "My Pages"}}</a></li>
							<li><a class="button small new-page" href="/handbook/create">New Page</a></li>
						@endif
						@if($page && $page->userCanWrite($user))<li><a class="button small edit-page" href="/handbook/{{$title}}/edit">Edit Page</a></li>@endif
					</ul>	
				@endif
				@if($page) 
					{{$page->body()}}
					<div class="handbook-stats">
						@if($page->updated_by)
							<i>Last updated {{$page->updated_at->diffForHumans()}} by {{$page->updatedBy->username}}.</i>
						@endif
						@if($user && $user->isStoryteller())
							<br>Created by {{$page->createdBy->username}}. 
						@endif
						@if($page->write_permission && $page->writePermission->hasRestrictions())
							<br>Editing is restricted to: <i>
							{{$page->writePermissionList()}}</i>
						@endif
						@if($user && $user->isStoryteller())<br><a href="/handbook/{{$page->id}}/delete">Delete This Page.</a> @endif
					</div>
				@else
					<p>	There is no page called '{{$title}}' in the handbook yet. If you think this is a mistake,
						please post a message to the Storytellers in the <i>General Messages</i> board.
					</p>
					@if($user)
						<a class="button small success" href="/handbook/{{$title}}/create">Create this page.</a>
					@endif
				@endif
			@endif
	</div>
</div>