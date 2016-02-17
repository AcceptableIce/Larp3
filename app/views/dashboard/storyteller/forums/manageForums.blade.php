@extends('dashboard/storyteller')
@section('title', 'Manage Forums')
@section('storyteller-content')
<div class="row left">
	@if($mode == "management")
	<h2>Forum Management</h2>
	<a href="/dashboard/storyteller/manage/forums/new"><button class="button small right"><i class="icon-plus"></i>New Forum</button></a>
	<p>Note: Trashed forums are recoverable; they're flagged as hidden.</p>
	<? 	$categories = ForumCategory::orderBy('display_order')->get();
		$categories->add((object) ['name' => "Trashed", 'id' => 0]);
		$categories->add((object) ['name' => 'Unassociated', 'id' => 0]);
	?>
	@foreach($categories as $category)
		<h4>{{$category->name}}</h4>
		<table class="responsive">
			<thead>
				<th style="width: 90px">Actions</th>
				<th>Forum Name</th>
				<th>Sect Restriction</th>
				<th>Clan Restriction</th>
				<th>Background Restriction</th>
				<th>Read Permission</th>
				<th>Create Topic Permission</th>			
				<th>Reply Permission</th>					
				<th>Private</th>
				<th>On Todo List?</th>
				<th>Asymmetric?</th>
				<th>Time Limited?</th>
				<th>Allowed Characters</th>
				<th>Position</th>
			</thead>
			<tbody>
				<? 	if($category->name == "Trashed") {
						$collection = Forum::onlyTrashed()->get();
					} else if ($category->name == "Unassociated") {
						$collection = Forum::whereNull('category_id')->get();
					} else {
						$collection = Forum::where('category_id', $category->id)->orderBy('position')->get();
					}
				?>
				@foreach($collection as $forum)
				<tr>
					<td>
						<a href="/dashboard/storyteller/manage/forums/{{$forum->id}}/edit" data-tooltip title="Edit"><i class="icon-pencil"></i></a>
						<a href="/dashboard/storyteller/manage/forums/{{$forum->id}}/characters" data-tooltip title="Permitted Characters"><i class="icon-users"></i></a>
						@if($category->name == "Trashed")
							<a href="/dashboard/storyteller/manage/forums/{{$forum->id}}/restore" data-tooltip title="Restore"><i class="icon-flash"></i></a>
						@else 
							<a href="/dashboard/storyteller/manage/forums/{{$forum->id}}/delete" data-tooltip title="Trash"><i class="icon-trash"></i></a>
						@endif
					</td>
					<td>{{$forum->name}}</td>
					<td>{{$forum->sect_id ? RulebookSect::find($forum->sect_id)->name : ''}}</td>
					<td>{{$forum->clan_id ? RulebookClan::find($forum->clan_id)->name : ''}}</td>
					<td>{{$forum->background_id ? RulebookBackground::find($forum->background_id)->name : ''}}</td>
					<td>{{$forum->read_permission ? PermissionDefinition::find($forum->read_permission)->name : ''}}</td>
					<td>{{$forum->topic_permission ? PermissionDefinition::find($forum->topic_permission)->name : ''}}</td>		
					<td>{{$forum->reply_permission ? PermissionDefinition::find($forum->reply_permission)->name : ''}}</td>					
					<td>{{$forum->is_private ? "Yes" : "No"}}</td>
					<td>{{$forum->show_on_st_todo_list ? "Yes" : "No"}}</td>
					<td>{{$forum->asymmetric_replies ? "Yes" : "No"}}</td>
					<td>{{$forum->time_limited ? "Yes" : "No"}}</td>
					<td>{{ForumCharacterPermission::where('forum_id', $forum->id)->count()}}</td>
					<td>{{$forum->position}}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	@endforeach
	@elseif($mode == "edit")
	<? $forum = Forum::find(isset($id) ? $id : -1); ?>
	<h2>{{$forum ? 'Edit '.$forum->name : 'New Forum'}}</h2>
	<form action="/dashboard/storyteller/manage/forums/{{$forum ? $forum->id.'/' : ''}}save" method="post">
		<div class="row">
			<div class="small-2 columns">
				<label for="name" class="right inline">Forum Name</label>
			</div>
			<div class="small-10 columns">
				<input type="text" name="name" class="right-label" value="{{$forum ? $forum->name : ''}}" />
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<label for="description" class="right inline">Description</label>
			</div>
			<div class="small-10 columns">
				<textarea name="description" class="right-label">{{$forum ? $forum->description : ''}}</textarea>
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<label for="category" class="right inline">Category</label>
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="category">
					@foreach(ForumCategory::all() as $category)
						<option value="{{$category->id}}" @if($forum && $category->id == $forum->category_id)selected="selected"@endif>
							{{$category->name}}
						</option>
					@endforeach
				</select>
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<label for="sect" class="right inline">Required Sect</label>	
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="sect">
					<option value="NULL"></option>
					@foreach(RulebookSect::all() as $sect)
						<option value="{{$sect->id}}" @if($forum && $sect->id == $forum->sect_id)selected="selected"@endif>
							{{$sect->name}}
						</option>
					@endforeach
				</select>	
				<p class="description">Characters without this Sect cannot see this board.</p>	
			</div>
		</div>	
		<div class="row">
			<div class="small-2 columns">
				<label for="clan" class="right inline">Required Clan</label>	
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="clan">
					<option value="NULL"></option>				
					@foreach(RulebookClan::all() as $clan)
						<option value="{{$clan->id}}" @if($forum && $clan->id == $forum->clan_id)selected="selected"@endif>
							{{$clan->name}}
						</option>
					@endforeach
				</select>	
				<p class="description">Characters without this Clan cannot see this board.</p>	
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<label for="background" class="right inline">Required Background</label>	
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="background">
					<option value="NULL"></option>				
					@foreach(RulebookBackground::all() as $background)
						<option value="{{$background->id}}" @if($forum && $background->id == $forum->background_id)selected="selected"@endif>
							{{$background->name}}
						</option>
					@endforeach
				</select>
				<p class="description">Characters without this Background cannot see this board.</p>			
			</div>
		</div>	
		<div class="row">
			<div class="small-2 columns">
				<label for="read-permission" class="right inline">Read Permission</label>	
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="read-permission">
					<option value="NULL"></option>				
					@foreach(PermissionDefinition::all() as $permission)
						<option value="{{$permission->id}}" @if($forum && $permission->id == $forum->read_permission)selected="selected"@endif>
							{{$permission->name}}
							</option>
					@endforeach
				</select>
				<p class="description">Users without this permission cannot see this board.</p>				
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<label for="topic-permission" class="right inline">Create Topic Permission</label>	
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="topic-permission">
					<option value="NULL"></option>				
					@foreach(PermissionDefinition::all() as $permission)
						<option value="{{$permission->id}}" @if($forum && $permission->id == $forum->topic_permission)selected="selected"@endif>
							{{$permission->name}}
							</option>
					@endforeach
				</select>	
				<p class="description">Users without this permission cannot post new threads this board.</p>								
			</div>
		</div>	
		<div class="row">
			<div class="small-2 columns">
				<label for="reply-permission" class="right inline">Post Reply Permission</label>	
			</div>
			<div class="small-10 columns">
				<select class="right-label" name="reply-permission">
					<option value="NULL"></option>				
					@foreach(PermissionDefinition::all() as $permission)
						<option value="{{$permission->id}}" @if($forum && $permission->id == $forum->reply_permission)selected="selected"@endif>
							{{$permission->name}}
							</option>
					@endforeach
				</select>		
				<p class="description">Users without this permission cannot reply to topics in this board.</p>							
			</div>
		</div>				
		<div class="row">
			<div class="small-2 columns">
				<label for="private" class="right inline">Private?</label>	
			</div>
			<div class="small-10 columns">
				<div class="switch">
				  <input id="private" name="private" type="checkbox" {{$forum && $forum->is_private ? "checked" : ""}}>
				  <label for="private"></label>
				</div> 
				<p class="description">Characters must be explicitly added to view this board.</p>								
			</div>
		</div>
		<div class="row">
			<div class="small-2 columns">
				<label for="todo_list" class="right inline">On ST Todo List?</label>	
			</div>
			<div class="small-10 columns">
				<div class="switch">
				  <input id="todo_list" name="todo_list" type="checkbox" {{$forum && $forum->show_on_st_todo_list ? "checked" : ""}}>
				  <label for="todo_list"></label>
				</div> 
				<p class="description">Threads on this board will appear on the ST Todo List if not marked complete.</p>	
			</div>
		</div>		
		<div class="row">
			<div class="small-2 columns">
				<label for="asymmetric" class="right inline">Asymmetric Replies?</label>	
			</div>
			<div class="small-10 columns">
				<div class="switch">
				  <input id="asymmetric" name="asymmetric" type="checkbox" {{$forum && $forum->asymmetric_replies ? "checked" : ""}}>
				  <label for="asymmetric"></label>
				</div> 
				<p class="description">STs can post to themselves on this board, 
				with a special toggle to have specific posts appear to players.</p>
			</div>
		</div>		
		<div class="row">
			<div class="small-2 columns">
				<label for="time-limited" class="right inline">Time Limited?</label>	
			</div>
			<div class="small-10 columns">
				<div class="switch">
				  <input id="time-limited" name="time-limited" type="checkbox" {{$forum && $forum->time_limited ? "checked" : ""}}>
				  <label for="time-limited"></label>
				</div> 			
				<p class="description">Characters cannot view threads on this board that were created before they were added to it.</p>	
			</div>
		</div>			
		<div class="row">
			<div class="small-2 columns">
				<label for="position" class="right inline">Position</label>	
			</div>
			<div class="small-10 columns">
				<input type="text"  id="position" name="position" value="{{$forum ? $forum->position : '0'}}" />
			</div>
		</div>	
			
		<hr>
		
		<div class="row">
			<div class="small-2 columns">
				<label for="list-header" class="right inline">Listing Header</label>	
			</div>
			<div class="small-10 columns">
				<textarea id="list-header" name="list-header">{{$forum ? $forum->list_header : '0'}}</textarea>
				<p class="description">Text to display at the top of the topic listing for the board.</p>	
			</div>
		</div>	
		<div class="row">
			<div class="small-2 columns">
				<label for="post-header" class="right inline">Post Header</label>	
			</div>
			<div class="small-10 columns">
				<textarea id="post-header" name="post-header">{{$forum ? $forum->post_header : '0'}}</textarea>
				<p class="description">Text to display at the top of the "New Topic" page for the board.</p>	
			</div>
		</div>		
		<div class="row">
			<div class="small-2 columns">
				<label for="thread-template" class="right inline">Thread Template</label>	
			</div>
			<div class="small-10 columns">
				<textarea id="thread-template" name="thread-template">{{$forum ? $forum->thread_template : '0'}}</textarea>
				<p class="description">Default text for the "New Topic" textarea.</p>	
			</div>
		</div>	
				
		<hr>
		
		<input type="submit" class="button small success" value="Save" />
	</form>
	@endif
</div>
@stop
@stop