@foreach(CharacterQuestionnaire::where('character_id', $character->id)->get() as $index => $q)
<b>{{$index + 1}}. {{RulebookQuestionnaire::find($q->questionnaire_id)->question}}</b>
<div class="panel">
	<p>{{$q->response}}</p>
</div>
@endforeach
<? $c = Character::find($character->id); ?>
@if($c->backstory_file)<a href="/content/backstories/{{$c->backstory_file}}">Backstory</a>@endif
