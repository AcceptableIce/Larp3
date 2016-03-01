@extends('dashboard/storyteller')

@section('storyteller-content')
<div class="row left">
	<h2>Award Experience to {{$character->name}}</h2>
	<form method="post" action="/dashboard/storyteller/experience/character/award" class="panel">
		<h4>Add or Remove Experience</h5>
		<p>
			<b>{{$character->name}} has {{$character->availableExperience()}} Experience.</b> 
			Negative values will remove Experience.
		</p>
		<input type="hidden" name="id" value="{{$character->id}}" />
		<label for="amount">Amount</label>
		<input type="text" name="amount" />
		<label for="message">Message (Optional)</label>
		<textarea name="message"></textarea>
		<input type="submit" class="button small" value="Award Experience" />
	</form>
</div>
@stop
@stop