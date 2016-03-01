@extends('dashboard/storyteller')
@section('title', 'Manage User Settings')
@section('storyteller-content')
<div class="row left">
	<h2>User Settings</h2>
	<table class="responsive">
		<thead>
			<th>ID</th>
			<th>Name</th>
			<th>Description</th>
			<th>Type</th>
			<th>Position</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(UserSettingDefinition::all() as $d)
				<tr>
					<td>
						{{$d->id}}
					</td>
					<td>
						{{$d->name}}
					</td>
					<td>
						{{$d->description}}
					</td>
					<td>
						{{$d->type}}
					</td>
					<td>
						{{$d->position}}
					</td>					
					<td>
						<form method="post" action="/dashboard/storyteller/manage/userSettings/delete">
							<input type="hidden" name="id" value="{{$d->id}}" />
							<input type="submit" class="button small alert" value="Delete Setting" />
						</form>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/manage/userSettings/create" class="panel">
		<h4>Add Setting</h5>
		<label>Name</label>
		<input type="text" name="name" />
		<label>Description</label>		
		<input type="text" name="description" />
		<label>Type</label>		
		<select name="type">
			<option value="select">Dropdown</option>
			<option value="checkbox">Checkbox</option>
			<option value="textarea">Textarea</option>
		</select>
		<label>Position</label>		
		<input type="text" name="position" />		
		<input type="submit" class="button small" value="Add Setting" />
	</form>
</div>
@stop
@stop