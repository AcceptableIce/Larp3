@extends('dashboard')
@section('title', 'My Characters')
@section('dashboard-script')
	self.activeTab("characters");

	self.promptDeleteName = ko.observable();
	self.promptDeleteId = ko.observable();
	self.promptDeleteField = ko.observable();
	self.promptDelete = function(name, id) {
		self.promptDeleteField("");
		self.promptDeleteName(name);
		self.promptDeleteId(id);
		$('#delete-modal').foundation('reveal', 'open');
	}

	self.canDelete = ko.computed(function() {
		return self.promptDeleteField() == self.promptDeleteName();
	});

	self.completeDelete = function() {
		$.ajax({
			url: "/characters/delete",
			type: 'post',
			data: {
				characterId: self.promptDeleteId()
			},
			success: function(data) {
				document.location = "/dashboard/characters";
			},
			error: function() {
				console.log('Failed to delete');
			}
		});
	}
@stop
@section('dashboard-content')
<div id="delete-modal" class="reveal-modal" data-reveal aria-labelledby="deleteModalTitle" aria-hidden="true" role="dialog">
  <h2 id="deleteModalTitle">Delete <span data-bind="text: promptDeleteName"></span>?</h2>
  <p>Are you sure you wish to delete <span data-bind="text: promptDeleteName"></span>? <b>This cannot be undone.</b></p>
  <p>Type this character's name into the box below to confirm the delete.</p>
  <input type="text" data-bind="textInput: $root.promptDeleteField, valueUpdate: afterkeydown" placeholder="Confirm..." />
  <hr>
  <button class="button alert small" data-bind="click: completeDelete, css: {'disabled': !canDelete() }">Delete</button>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<?
	$user = Auth::user();
	$sections = ['Active' => $user->characters()->where(['active' => true, 'is_npc' => false]),
				 'Active NPCs' => $user->characters()->where(['active' => true, 'is_npc' => true]),
				 'Inactive NPCs' => $user->characters()->where(['active' => false, 'is_npc' => true]),
				 'Pending Review' => $user->characters()->where(['in_review' => true, 'approved_version' => 0, 'is_npc' => false]),
				 'Inactive' => $user->characters()->where(['in_review' => false, 'active' => false, 'is_npc' => false])->where('approved_version', '>', '0'),
				 'Incomplete' => $user->characters()->where(['in_review' => false, 'active' => false, 'approved_version' => 0, 'is_npc' => false]),
				];
?>
<h2 class="character-title">My Characters</h2>
<a href="/generator"><button class="button small new-character"><i class="icon-plus"></i> New Character</button></a>
@foreach($sections as $key => $value)
<? $count = $value->count(); if($count == 0) continue; ?>
<h4>{{$key}}</h4>
<table class="responsive">
	<thead>
		<th>Actions</th>
		<th>Character Name</th>
		<th>Clan</th>
		<th>Experience</th>
		<th>Active Version</th>
		<td>Last Updated</th>
	</thead>
	<tbody>
		@foreach($value->get() as $character)
			<tr>
				<td>
					@if(!$character->in_review)
					<a href="/generator/{{$character->id}}"><i class="icon-pencil" data-tooltip title="Edit"></i></a>
					@endif
					
					<a href="/dashboard/character/{{$character->id}}/biography"><i class="icon-vcard" data-tooltip data-options="disable_for_touch:true" title="Edit Biography"></i></a>
					<a href="/dashboard/character/{{$character->id}}/print"><i class="icon-print" data-tooltip data-options="disable_for_touch:true" title="Print"></i></a>
					<a href="#" data-dropdown="drop-{{$character->id}}" data-options="align:bottom"><i class="icon-dot-3"></i></a>
					<ul id="drop-{{$character->id}}" data-dropdown-content class="f-dropdown small drop-bottom">
						@if($user->isStoryteller())
							<li><a href="/dashboard/storyteller/character/{{$character->id}}/toggleNPC"><i class="icon-graduation-cap"></i> 
							@if($character->is_npc) Remove NPC Status @else Give NPC Status	@endif
							</a></li>
						@endif
						<li><a href="/dashboard/character/{{$character->id}}/lores"><i class="icon-book"></i> Read Lores</a></li>
						<li><a href="/dashboard/character/{{$character->id}}/cheatsheet"><i class="icon-book-open"></i> Character Reference</a>
						<li><a href="/dashboard/character/{{$character->id}}/versioncontrol"><i class="icon-back-in-time"></i> Version Control</a></li>
					  	<li class="warning"><a href="#" data-bind="click: function() { promptDelete('{{$character->name}}', {{$character->id}}) }"><i class="icon-trash"></i> Delete Character</a></li>
					</ul>
				</td>
				<td>{{$character->name}}</td>
				<? $clan = $character->clan()->first(); ?>
				<td>{{$clan ? $clan->definition->name : "No clan"}}</td>
				<td>{{$character->approved_version > 0 ? @$character->availableExperience() : "N/A"}}</td>
				<td>{{$character->approved_version == 0 ? ($character->in_review ? "<i>Pending Approval</i>" : "<i>Incomplete</i>") : 'v'.$character->latestVersion()->version}}</td>
				<td>{{$character->versionInfo($character->latestVersion()->version)->updated_at}}
			</tr>
		@endforeach
</table>
@endforeach
@stop
@stop