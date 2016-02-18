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
				<td>
					{{$c->id}}
				</td>
				<td>
					{{$c->name}}
				</td>
				<td>
					{{@$c->availableExperience()}}
				</td>
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
</div>
@stop
@stop