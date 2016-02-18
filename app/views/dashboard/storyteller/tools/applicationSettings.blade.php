@extends('dashboard/storyteller')
@section('title', 'Application Settings')

@section('storyteller-content')
<? $user = Auth::user(); ?>
<div class="row left">
	<div class="small-12 columns">
	  <h3>Application Settings</h3>
	  <form method="post" action="/dashboard/storyteller/settings/application/save">
		  @foreach(ApplicationSetting::all() as $definition)
		  	<label for="user-settings-{{$definition->id}}">
		  		{{$definition->name}}
			  	{{$definition->createForm()}}
			  	<p class="setting-description">
				  	{{$definition->description}}
				  </p>
		  	</label>
		  @endforeach
		  <hr>
		  <input type="submit" class="button success" value="Save Settings" />
	  </form>
	</div>
</div>
@stop
@stop
