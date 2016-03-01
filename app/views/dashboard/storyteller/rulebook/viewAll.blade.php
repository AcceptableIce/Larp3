@extends('dashboard/storyteller')
@section('title', 'Edit Rulebook Records')

@section('storyteller-content')
<? 
	$user = Auth::user(); 

?>
<div class="row left">
	<div class="small-12 columns">
	  <h3>Edit Rulebook Records</h3>
		@foreach(Helpers::$rulebook_items as $key => $item)
		 	<a href="/dashboard/storyteller/rulebook/{{$key}}">
				{{ucwords(str_replace("_", " ", $key))}}<br>
			</a>
		@endforeach
	</div>
</div>
@stop
@stop
