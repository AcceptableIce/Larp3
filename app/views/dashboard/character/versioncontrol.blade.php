@extends('dashboard')
@section('dashboard-script')
	self.activeTab("characters");

	self.promptName = ko.observable();
	self.promptField = ko.observable();
	self.prompt = function(name) {
		self.promptField("");
		self.promptName(name);
		$('#delete-modal').foundation('reveal', 'open');
	}

	self.activeButton = ko.computed(function() {
		return self.promptField() == "{{$character->name}}";
	});

	self.complete = function() {
		$.ajax({
			url: "/characters/revert",
			type: 'post',
			data: {
				characterId: {{$character->id}},
				version: self.promptName()
			},
			success: function(data) {
				document.location = "/dashboard/character/{{$character->id}}/versioncontrol";
			}
		});
	}
@stop
@section('dashboard-content')
<div id="delete-modal" class="reveal-modal" data-reveal aria-labelledby="deleteModalTitle" aria-hidden="true" role="dialog">
	<h2 id="deleteModalTitle">
		Revert to Version <span data-bind="text: promptName"></span>?
	</h2>
	<p>
		Are you sure you wish to revert {{$character->name}} to version 
		<span data-bind="text: promptName"></span>? 
		<b>This cannot be undone. All versions after version <span data-bind="text: promptName"></span> 
		will be deleted.</b>
	</p>
	<p>
		Type this character's name into the box below to confirm the revert.
	</p>
	<input type="text" data-bind="textInput: $root.promptField, valueUpdate: afterkeydown" placeholder="Confirm..." />
	<hr>
	<button class="button alert small" data-bind="click: complete, css: {'disabled': !activeButton() }">Revert</button>
	<a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>

<h2 class="character-title">Version Control for {{$character->name}}</h2>

<table>
	<thead>
		<th>Actions</th>
		<th>Version</th>
		<th>Date Created</th>
		<th>Last Updated</th>
		<th></th>
	</thead>
	<tbody>
	@foreach($character->versions()->orderBy('version', 'desc')->get() as $version)
		<tr>
			<td>
				<a href="/dashboard/character/{{$character->id}}/print/{{$version->version}}">
					<i class="icon-doc-text-inv" data-tooltip title="View Character Sheet"></i>
				</a>
			</td>
			<td>
				{{$version->version}} 
				{{$version->version == $character->approved_version ? "<span class='label success'>Current Version</span>" : ""}}
				{{$version->version == $character->latestVersion()->version ? "<span class='label warning'>Latest Version</span>" : ""}}
			</td>
			<td>
				{{$version->created_at}}
			</td>
			<td>
				{{$version->updated_at}}
			</td>
			<td>
				@if($version->version == $character->approved_version || Auth::user()->isStoryteller()) 
					@if($version->version > $character->approved_version)
						<button class="button small alert" data-bind="click: function() { prompt({{$version->version}}) }">
							Force to Version {{$version->version}}
						</button>
					@elseif($version->version != $character->latestVersion()->version)
						<button class="button small alert" data-bind="click: function() { prompt({{$version->version}}) }">
							Revert to Version {{$version->version}}
						</button>
					@endif
				@endif
			</td>
		</tr>
	@endforeach
</table>
@stop
@stop