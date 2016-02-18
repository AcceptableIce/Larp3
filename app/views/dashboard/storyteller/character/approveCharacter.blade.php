@extends('dashboard/storyteller')
@section('title', 'Approve Character')
<? $character = Character::find($id); ?>
@section('storyteller-content')
<? echo View::make('partials/changes', ['character' => $character, 'version' => $character->latestVersion()->version])->render(); ?>
@stop
@stop