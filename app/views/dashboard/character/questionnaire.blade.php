@extends('dashboard')
@section('dashboard-script')
	self.activeTab("characters");
	tinymce.init({
		selector: ".questionnaire-reply",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect | bold italic underline | bullist numlist outdent indent | forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : '',
    	statusbar: false,
        menubar: false
	});
@stop
@section('dashboard-content')
<div class="row left">
	<h2 class="character-title">Character Biography</h2>	
	<p>Please answer the following questions with as much information as you're comfortable giving. This questionnaire is worth an experience point when all ten questions
		are filled out. You can come back and edit your entries, but you only receive experience for the first time.</p>
	<form action="/dashboard/character/{{$character_id}}/biography/submit" method="post" enctype="multipart/form-data">
	@foreach(RulebookQuestionnaire::all() as $index => $q) 
		<div class="panel">
			<b>{{$index + 1}}. {{$q->question}}</b>
			<input type="hidden" name="ids[]" value="{{$q->id}}" />
			<? $response = CharacterQuestionnaire::where(['character_id' => $character_id, 'questionnaire_id' => $q->id])->first(); ?>
			<textarea name="replies[]" class="questionnaire-reply">{{$response ? $response->response : ""}}</textarea>
		</div>
	@endforeach
	<div class="panel">
		<b>If you have a backstory for your character, upload it here.</b>
		{{ Form::file('backstory'); }}
	</div>
	<input type="submit" class="button small success" value="Submit" />
	</form>
</div>

@stop
@stop