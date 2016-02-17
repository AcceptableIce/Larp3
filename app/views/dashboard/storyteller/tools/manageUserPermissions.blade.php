@extends('dashboard/storyteller')
@section('title', 'Manage Permissions')
@section('storyteller-content')
<div class="row left" style="margin-bottom: 20px">
	<div class="small-12 medium-6 columns">
	<h2>User Permissions</h2>
		@foreach(User::has('permissions', '>=', 1)->get() as $u)
			<div class="permission-set">
				<h4>{{$u->username}}</h4>
				@foreach(Permission::where('user_id', $u->id)->get() as $p)
					<div class="permission-row">
						{{$p->definition->name}} 
						<form method="post" action="/dashboard/storyteller/manage/permissions/remove" class="remove-permission-form">
							<input type="hidden" name="user" value="{{$u->id}}" />
							<input type="hidden" name="permission" value="{{$p->definition->id}}" />
							<input type="submit" class="button alert tiny remove-permission" value="Remove Permission" />
						</form>
					</div>
				@endforeach
			</div>
		@endforeach
	</div>
	<div class="small-12 medium-6 columns">
		<h2>Permission Definitions</h2>
		<div class="permission-set">
			@foreach(PermissionDefinition::all() as $def)
				<div class="permission-row">
					{{$def->name}}
					<div class="permission-description">{{$def->definition}}</div>
					<form method="post" action="/dashboard/storyteller/manage/permissions/delete" class="remove-permission-form remove-permission-def-form">
						<input type="hidden" name="definition" value="{{$def->id}}" />
						<input type="submit" class="button alert tiny remove-permission" value="Delete Permission" />
					</form>
				</div>
			@endforeach
		</div>
	</div>
</div>
<div class="row left">
	<form method="post" action="/dashboard/storyteller/manage/permissions/grant" class="panel">
		<h4>Grant Permission</h5>
		<label>User</label>
		<select name="user">
			@foreach(User::orderBy('username')->get() as $u)
				<option value="{{$u->id}}">{{$u->username}}</option>
			@endforeach
		</select>		
		<label>Permission</label>
		<select name="permission">
			@foreach(PermissionDefinition::all() as $p)
				<? $count = Permission::where('permission_id', $p->id)->count(); ?>
				<option value="{{$p->id}}">{{$p->name}} ({{$count}} user{{$count == 1 ? '' : 's'}} with permission)</option>
			@endforeach
		</select>
		<input type="submit" class="button small" value="Grant Permission" />
	</form>
	<form method="post" action="/dashboard/storyteller/manage/permissions/create" class="panel">
		<h4>Create Permission</h5>
		<label>Name</label>
		<input type="text" name="name" />
		<label>Description</label>
		<textarea name="description"></textarea>
		<input type="submit" class="button small" value="Add Position" />
	</form>
</div>
@stop
@stop