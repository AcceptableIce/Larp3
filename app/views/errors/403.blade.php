@extends('layout')
@section('title', 'Page Not Found')
@section('includes') 
<style type="text/css">
.slightly-smaller {
	font-size: 0.75em;
	color: #999;
	position: relative;
	top: -5px;
	line-height: 1.2;
	display: block;
}
</style>
@stop
@section('content')
<? $options = [	
		"Pretend the <i>Mission: Impossible</i> theme is playing right now.",
		"This one's on you.",
		"Can't <i>Obfuscate</i> your way past this one.",
		"Wait, you can't see what's behind here? You're missing out, let me tell you."
	]; 
?>
<div class="row">
	<div class="small-12 columns">
		<h1>
			Access Denied<br>
			<span class="slightly-smaller">{{$options[array_rand($options)]}}</span>
		</h1>
		<p>
			You don't have permission to view this page.<br>
			Go <a href="javascript:history.go(-1)">right back to where you were</a>, 
			and we'll promise not to tell anyone.
		</p>
	</div>
</div>
@stop	