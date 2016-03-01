@extends('dashboard/storyteller')
@section('title', 'Manage Positions')
@section('storyteller-content')
<div class="row left">
	<h2>Character Positions</h2>
	<table class="responsive">
		<thead>
			<th>Position ID</th>
			<th>Name</th>
			<th>Characters</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(RulebookPosition::all() as $d)
				<tr>
					<td>
						{{$d->id}}
					</td>
					<td>
						{{$d->name}}
					</td>
					<td>
						{{CharacterPosition::where('position_id', $d->id)->count()}}
					</td>
					<td>
						<form method="post" action="/dashboard/storyteller/manage/positions/delete">
							<input type="hidden" name="id" value="{{$d->id}}" />
							<input type="submit" class="button small alert" value="Delete Position" />
						</form>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/manage/positions/create" class="panel">
		<h4>Add Position</h5>
		<label>Position Name</label>
		<input type="text" name="name" />
		<input type="submit" class="button small" value="Add Position" />
	</form>
</div>
@stop
@stop