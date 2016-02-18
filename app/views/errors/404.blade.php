@extends('layout')
@section('title', 'Page Not Found')
@section('includes') 
<style type="text/css">
.slightly-smaller {
	font-size: 0.75em;
	color: #999;
}
</style>
@stop
@section('content')
<? $options = [	"Someone must've Touch of Madness'd it.",
				"Quick, go through my memories and find where I left it.",
				"This is more aggrivating than... uh... fire.",
				"Maybe it's just in Obs.",
				"Are pages irreplaceable like item cards?",
				"Anyone have Heightened Senses?"]; ?>
<div class="row">
	<div class="small-12 columns">
		<h1>404? <span class="slightly-smaller">{{$options[array_rand($options)]}}</span></h1>
		<p>
			The URL you requested doesn't exist.<br>
			Feel free to go <a href="javascript:history.go(-1)">right back to where you were.</a>
		</p>
	</div>
</div>
@stop	