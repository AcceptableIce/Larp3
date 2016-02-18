@extends('dashboard/storyteller')
@section('storyteller-content')
<? $character = Character::find($id); ?>
@section('title', 'Positions for '.$character->name)

<div class="row left">
	<h2>Positions for {{$character->name}}</h2>
	<table>
		<thead>
			<th>Position ID</th>
			<th>Name</th>
			<th>Total Characters with Position</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(CharacterPosition::where('character_id', $character->id)->get() as $d)
				<tr>
					<td>
						{{$d->definition->id}}
					</td>
					<td>
						{{$d->definition->name}}
					</td>
					<td>
						{{CharacterPosition::where('position_id', $d->id)->count()}}
					</td>
					<td>
						<form method="post" action="/dashboard/storyteller/character/{{$id}}/positions/remove">
							<input type="hidden" name="position" value="{{$d->definition->id}}" />
							<input type="submit" class="button small alert" value="Remove from Position" />
						</form>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/character/{{$id}}/positions/add" class="panel">
		<? $positions = RulebookPosition::all(); 
			foreach($positions as $key => $p) {
				if(CharacterPosition::where(['character_id' => $character->id, 'position_id' => $p->id])->exists()) {
					$positions->forget($key);
				}
			}
		?>
		<h4>Add Position</h5>
		@if($positions->count() > 0)
			<label>Position Name</label>
			<select name="position">
				@foreach($positions as $p)
					<? $count = CharacterPosition::where('position_id', $p->id)->count(); ?>
					<option value="{{$p->id}}">
						{{$p->name}} ({{$count}} character{{$count == 1 ? '' : 's'}} with position)
					</option>
				@endforeach
			</select>
			<input type="submit" class="button small" value="Add Position" />
		@else
			<p>There are no more valid positions.</p>
		@endif
	</form>
</div>
@stop
@stop