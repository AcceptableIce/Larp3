@extends('dashboard/storyteller')
@section('title', 'Biography Experience')
@section('storyteller-script')
	
@endsection
@section('storyteller-content')
<div class="row left">
	<h2>Character Biography Report</h2>
	<p>Fields with checkmarks have already been awarded. To view a character's backstory, press the book icon.<br>
		<b>Awarding character biography experience cannot be undone.</b></p>
	<table class="journal-grid responsive">
		<thead>
			<th>Character</th>
			<th></th>
			<th></th>
			<th>Question XP</th>
			<th>Backstory XP</th>
		</thead>
		<tbody>
			@foreach(Character::activeCharacters()->orderBy('name')->get() as $c)
			<? //Find the relevant thread 
				$topic = ForumPost::where('body', "[[questionnaire/$c->id]]")->first();
			?>
			<tr>
				<td>@if($topic) <a href="/forums/topic/{{$topic->topic->id}}">{{$c->name}}</a> @else {{$c->name}} @endif</td>
				<? 	$biographies = CharacterQuestionnaire::where('character_id', $c->id)->where('response', '!=', '')->get(); 
					$experience = CharacterBiographyExperience::where('character_id', $c->id)->first(); ?>
				<td>{{$biographies->count()}}</td>
				<td>@if($c->backstory_file)
					<a href="/content/backstories/{{$c->backstory_file}}"><i class="icon-book"></i></a>
				@endif</td>
				<td>@if($experience && $experience->questionnaire_xp)
					<i class='icon-check'></i>
					@else
					<form action="/dashboard/storyteller/experience/biographies/award" method="post">
						<input type="hidden" name="id" value="{{$c->id}}" />
						<input type="hidden" name="type" value="questionnaire" />
						<input  type="submit" class="button tiny success" value="Award Experience" />
					</form>
					@endif
				</td>
				<td>@if($experience && $experience->backstory_xp)
					<i class='icon-check'></i>
					@else
					<form action="/dashboard/storyteller/experience/biographies/award" method="post">
						<input type="hidden" name="id" value="{{$c->id}}" />
						<input type="hidden" name="type" value="backstory" />
						<input  type="submit" class="button tiny success" value="Award Experience" />
					</form>
					@endif
				</td>				
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@stop
@stop