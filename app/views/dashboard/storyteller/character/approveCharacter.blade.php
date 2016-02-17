@extends('dashboard/storyteller')
@section('title', 'Approve Character')
<? $character = Character::find($id); ?>
@section('storyteller-content')
<style type="text/css">
	.strikethrough {
		text-decoration: line-through;
	}

	.label {
		font-size: 1.0em;
	}
</style>
<? echo View::make('partials/changes', ['character' => $character, 'version' => $character->latestVersion()->version])->render(); ?>
@stop
@stop