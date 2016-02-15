@extends('dashboard')
@section('title', 'Account Settings')

@section('dashboard-script')
  self.activeTab("settings");
  	tinymce.init({
		selector: "textarea",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : '',
    	statusbar: false,
    	menubar: false
	});
@stop

@section('dashboard-content')
<? $user = Auth::user(); ?>
<div class="row left">
	<div class="small-12 columns">
	  <h3>User Settings</h3>
	  <form method="post" action="/dashboard/settings/save">
	  <label for="user-email">Email
		  	<input type="text" name="user-email" id="user-email" value="{{$user->email}}" />
		  	<p class="setting-description">Your email address.</p>
		  </label>
	  @foreach(UserSettingDefinition::orderBy('position')->get() as $definition)
	  	<label for="user-settings-{{$definition->id}}">{{$definition->name}}
		  	{{$definition->createForm($user)}}
		  	<p class="setting-description">{{$definition->description}}</p>
	  	</label>
	  @endforeach
	  <hr>
	  <input type="submit" class="button success" value="Save Settings" />
	  </form>
	</div>
</div>
@stop
@stop
