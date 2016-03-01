<? 	if(!isset($name)) $name = 'Home';
	$name = str_replace("_", " ", $name);
	$page = HandbookPage::where('title', 'LIKE', $name)->first();
?>
@extends('layout')
@section('title', $page ? $page->title : 'New Page')
@section('content')
<div class="handbook-content theme-wrapper">
	{{View::make('partials/handbookPage', ['title' => $name, 'showNewPage' => true])->render()}}
</div>
@stop