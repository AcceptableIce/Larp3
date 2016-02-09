@extends('layout')
@section('title', 'Edit Handbook')
@section('includes') 
<style type="text/css">
	#page-edit {
		min-height: 400px;		
	}
</style>
@stop
@section('script')
<script type="text/javascript">
$(function() {
	function handbookVM() {
		var self = this;
		
		return self;
	}
	ko.applyBindings(new handbookVM());
	
	tinymce.init({
		selector: "#page-edit",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : ''
	});
});
</script>
@stop
@section('content')
<? 	
	$user = Auth::user();
	if(isset($name) && $mode == 1) {
		$page = HandbookPage::where('title', 'LIKE', $name)->first();
		$title = $page->title;
		$body = $page->body;
	} else if(isset($name) && $mode == 0) {
		$title = $name;
		$body = "";
	} else {
		$title = "";
		$body = "";
	}
	if(!$user || (isset($page) && $page && (!$page->userCanRead($user) || !$page->userCanWrite($user)))) return Redirect::to('/handbook');
?>
<div class="row">
	<div class="small-12 columns">
			<h1>{{$mode == 0 ? "Creating" : "Editing"}} {{$title == "" ? "a New Page" : $title}}</h1>
			<form method="post" action="/handbook/save">
				<input type="hidden" name="mode" value="{{$mode}}" />
				@if($title == "")
					<input type="text" name="title" class="title-edit" placeholder="Title" />
				@else
					<input type="hidden" name="title" value="{{$title}}" />
				@endif
				<textarea name="body" id="page-edit">{{$body}}</textarea>
				<hr>
				<? 	$userPermissionCount = $user->permissions()->count();
					$readPermission = isset($page) ? $page->readPermission : null;
					$writePermission = isset($page) ? $page->writePermission : null;
				?>
				@foreach(['read', 'write'] as $type)
					<? $activePermission = ($type == 'read' ? $readPermission : $writePermission); ?>
					<div class="panel">
						<b>{{ucfirst($type)}} Permission</b>
						@if($userPermissionCount > 0)
							<label for="{{$type}}-user-permission">Required User Permission</label>
							<select name="{{$type}}-user-permission">
								<option value="0"></option>
								<? $userPermissionId = $activePermission ? $activePermission->permission_id : -1; ?>
								@foreach(PermissionDefinition::all() as $p)
									<? 	$selected = $userPermissionId == $p->id; 
										$include = $user->hasPermission($p->name);
									?>
									@if($include)<option value="{{$p->id}}" {{$selected ? 'selected' : ''}}>{{$p->name}}</option>@endif
								@endforeach		
							</select>
						@endif
						<? 	$activeCharacter = $user->activeCharacter();
							$is_st = $user->isStoryteller();
						?>
						@if($activeCharacter || $is_st) 
							<label for="{{$type}}-sect-permission">Required Sect</label>
							<select name="{{$type}}-sect-permission">
								<option value="0"></option>
								<?	$sectDefinition = $activeCharacter->sect()->first()->definition; 
									$sectPermissionId = $activePermission ? $activePermission->sect_id: - 1; ?>
								@foreach(RulebookSect::all() as $p)
									<? 	$selected = $sectPermissionId == $p->id;
										$include = $is_st || $sectDefinition->id == $p->id; 
									?>
									@if($include)<option value="{{$p->id}}" {{$selected ? 'selected' : ''}}>{{$p->name}}</option>@endif
								@endforeach
							</select>
							<label for="{{$type}}-clan-permission">Required Clan</label>
							<select name="{{$type}}-clan-permission">
								<option value="0"></option>
								<?	$clanDefinition = $activeCharacter->clan()->first()->definition; 
									$clanPermissionId = $activePermission ? $activePermission->clan_id : -1; ?>
								@foreach(RulebookClan::all() as $p)
									<? 	$selected = $clanPermissionId == $p->id;
										$include = $is_st || $clanDefinition->name == $p->name; 
									?>
									@if($include)<option value="{{$p->id}}" {{$selected ? 'selected' : ''}}>{{$p->name}}</option>@endif
								@endforeach
							</select>
							<label for="{{$type}}-background-permission">Required Background</label>
							<select name="{{$type}}-background-permission">
								<option value="0"></option>
								<?	$backgroundPermissionId = $activePermission ? $activePermission->background_id: - 1; ?>
								@foreach(RulebookBackground::all() as $p)
									<? 	$selected = $backgroundPermissionId == $p->id;
										$include = $is_st || $activeCharacter->getBackgroundDots($p->name) > 0;
									?>
									@if($include)<option value="{{$p->id}}" {{$selected ? 'selected' : ''}}>{{$p->name}}</option>@endif
								@endforeach
							</select>							
						@endif
						@if($type == 'write')<p class="setting-description">Write Permission is always at least Read Permission, even if left blank. The creator of the page always has read and write permissions.</p> @endif
					</div>
				@endforeach
			<input class="button submit right" type="submit" value="Save" />
			</form>
	</div>
</div>

@stop