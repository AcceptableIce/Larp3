@extends('layout')
@section('title', 'Page Not Found')
@section('includes') 
<style type="text/css">
.slightly-smaller {
	font-size: 0.75em;
	color: #999;
	position: relative;
	top: -15px;
}
</style>
@stop
@section('content')
<? $options = [	
		"That... wasn't quite right",
		"Ninety-six better than a 404, right?",
		"If you were doing something important... sorry about that.",
		"Not my best work.",
		"This probably never would've happened on the old boards, maybe."
	]; 
?>
<div class="row">
	<div class="small-12 columns">
		<h1>
			Error 500 - Something Broke<br>
			<span class="slightly-smaller">{{$options[array_rand($options)]}}</span>
		</h1>
		<p>
			An internal error occurred. We've already been alerted, and we'll fix it as soon as possible.<br>
			You may want to go <a href="javascript:history.go(-1)">right back to where you were.</a>
		</p>
	</div>
</div>
@stop	