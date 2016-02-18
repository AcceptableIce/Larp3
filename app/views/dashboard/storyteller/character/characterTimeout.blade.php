<? $character = Character::find($id); ?>
@extends('dashboard/storyteller')
@section('title', 'Timeout Date for '.$character->name)
@section('storyteller-content')
<div class="row left">
	<h2>Timeout Date for {{$character->name}}</h2>
	<? $date = $character->getTimeoutDate(); ?>
	<p>
		The current timeout date for {{$character->name}} is {{$date ? $date->format("m/d/Y") : "undefined"}}.
	</p>
	<form method="post" action="/dashboard/storyteller/character/{{$id}}/timeout/set" class="panel">
		<h4>Set Timeout Date</h5>
		<label>Date (MM/DD/YYYY)</label>
		<input type="text" name="date" />
		<input type="submit" class="button small" value="Set Timeout" />
	</form>
</div>
@stop
@stop