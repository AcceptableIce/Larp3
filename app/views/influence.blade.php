@extends('layout')
@section('title', 'Influence Caps')
@section('includes') 
<style type="text/css">
.cap-item {
	border: 1px solid #c0c0c0;
}
</style>
@stop
@section('content')
<div class="influence-wrapper theme-wrapper">
	<div class="row">
		<div class="small-12 columns">
			<h1>Influence Caps</h1>
			<p>
				The following are the influence caps for the various fields in the game. 
				You cannot purchase more dots in a field than these caps. 
				If you already have more dots than the caps, then the "extra" ones cannot be used at this time. 
				Alone, you cannot perform an action which requires more points than the cap, 
				but if you work with someone else you can double them.
				For more information, check out the Influence section in <a href="/larp201">LARP 201</a>.
			</p>
			<ul class="small-block-grid-2 medium-block-grid-3 large-block-grid-4">
			@foreach(InfluenceCap::with('definition')->get()->sortBy('definition.name') as $index => $cap)
				<li>
					<div class="row">
						<div class="small-6 columns">
							<b class="inline right">
								{{$cap->definition->name}}:
							</b> 
						</div>
						<div class="small-6 columns">
							{{$cap->capacityString()}}
						</div>
					</div>
				</li>
			@endforeach
			</ul>
		</div>
	</div>
</div>
@stop	