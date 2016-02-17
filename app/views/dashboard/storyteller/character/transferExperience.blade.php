@extends('dashboard/storyteller')
@section('storyteller-content')
<? $character = Character::find($id); ?>
@section('title', 'Positions for '.$character->name)

<div class="row left">
	<h2>Transfer Experience to {{$character->name}}</h2>
	<p>Updated experience totals may not appear on the character list immediately.</p>
	<table>
		<thead>
			<th>Character ID</th>
			<th>Name</th>
			<th>Experience</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(Character::where('id', '<>', $character->id)->where('user_id', $character->user_id)->get() as $c)
			<tr>
				<td>{{$c->id}}</td>
				<td>{{$c->name}}</td>
				<td>{{@$c->availableExperience()}}</td>
				<td>
					<form method="post" action="/dashboard/storyteller/character/{{$id}}/experience/transfer">
						<input type="hidden" name="from" value="{{$c->id}}" />
						<input type="submit" class="button small success" value="Transfer" />
					</form>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/character/{{$id}}/positions/add" class="panel">
		<? $positions = RulebookPosition::all(); 
			foreach($positions as $key => $p) {
				if(CharacterPosition::where(['character_id' => $character->id, 'position_id' => $p->id])->exists()) $positions->forget($key);
			}
		?>
		<h4>Add Position</h5>
		@if($positions->count() > 0)
			<label>Position Name</label>
			<select name="position">
				@foreach($positions as $p)
					<? $count = CharacterPosition::where('position_id', $p->id)->count(); ?>
					<option value="{{$p->id}}">{{$p->name}} ({{$count}} character{{$count == 1 ? '' : 's'}} with position)</option>
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