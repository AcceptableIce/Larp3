@extends('dashboard/storyteller')
@section('title', 'All Characters')
@section('storyteller-script')

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
			}
		});
	}

@stop
@section('storyteller-content')
	<?
		$request = [
					"all" => Character::where('id', '>', 0),
					"complete" => Character::where(array('is_npc' => false, 'in_review' => false, 'active' => true))->where('approved_version', '>', 0),
					"changed" => Character::where(array('is_npc' => false,  'in_review' => true, 'active' => true))->where('approved_version', '>', 0),
					"new" => Character::where(array('is_npc' => false,  'in_review' => true, 'active' => false)),
					"npcs" => Character::where(array('is_npc' => true)),
					"npcs-active" => Character::where(array('is_npc' => true, 'active' => true))];
	?>
<div id="delete-modal" class="reveal-modal" data-reveal aria-labelledby="deleteModalTitle" aria-hidden="true" role="dialog">
  <h2 id="deleteModalTitle">Delete <span data-bind="text: promptDeleteName"></span>?</h2>
  <p>Are you sure you wish to delete <span data-bind="text: promptDeleteName"></span>? <b>This cannot be undone.</b></p>
  <p>Type this character's name into the box below to confirm the delete.</p>
  <input type="text" data-bind="textInput: $root.promptDeleteField, valueUpdate: afterkeydown" placeholder="Confirm..." />
  <hr>
  <button class="button alert small" data-bind="click: completeDelete, css: {'disabled': !canDelete() }">Delete</button>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div class="row left">
<dl class="sub-nav">
  <dt>Filter:</dt>
  <dd class="{{$filter == 'all' ? 'active' : ''}}"><a href="/dashboard/storyteller/characters/all">All ({{$request['all']->count()}})</a></dd>
  <dd class="{{$filter == 'complete' ? 'active' : ''}}"><a href="/dashboard/storyteller/characters">Complete ({{$request['complete']->count()}})</a></dd> 
  <dd class="{{$filter == 'changed' ? 'active' : ''}}"><a href="/dashboard/storyteller/characters/changed">Changed ({{$request['changed']->count()}})</a></dd>
  <dd class="{{$filter == 'new' ? 'active' : ''}}"><a href="/dashboard/storyteller/characters/new">New ({{$request['new']->count()}})</a></dd>
  <dd class="{{$filter == 'npcs' ? 'active' : ''}}"><a href="/dashboard/storyteller/characters/npcs">NPCs ({{$request['npcs']->count()}})</a></dd>
  <dd class="{{$filter == 'npcs-active' ? 'active' : ''}}"><a href="/dashboard/storyteller/characters/npcs/active">Active NPCs ({{$request['npcs-active']->count()}})</a></dd>
</dl>
<? $items = $request[$filter]->with('owner')->orderBy('name')->paginate(25); ?>
<div class="character-pagination">{{ $items->links() }}</div>
</div>
<table class="responsive">
	<thead>
		<th>Actions</th>
		<th>Character Name</th>
		<th>Player</th>
		<th>Clan</th>
		<th>Displaying As</th>
		<th>Experience</th>
		<th>Active Version</th>		
		<th>Latest Version</th>
		<th>Last Updated</th>
		@if(strpos($filter, 'npcs') === false)
			<th>Missed</th>
			<th>Time Out</th>
		@endif
	</thead>
	<tbody>
	@foreach($items as $character)
		<tr>
			<td class="character-actions">
				@if(!$character->in_review)
				<a href="/generator/{{$character->id}}"><i class="icon-pencil" data-tooltip data-options="disable_for_touch:true" title="Edit"></i></a>
				@endif
				@if($filter == "changed" || $filter == "new")
				<a href="/dashboard/storyteller/character/{{$character->id}}/changes"><i class="icon-check" data-tooltip data-options="disable_for_touch:true" title="Approve or Reject"></i></a>
				@endif
				<a href="/dashboard/character/{{$character->id}}/print"><i class="icon-print" data-tooltip data-options="disable_for_touch:true" title="Print"></i></a>
				<a href="#" data-dropdown="drop-{{$character->id}}" data-options="align:bottom" ><i class="icon-dot-3"></i></a>
				<div class="character-dropdown-holder">
				<ul id="drop-{{$character->id}}" data-dropdown-content class="f-dropdown small drop-bottom">
					<li><a href="/dashboard/storyteller/character/{{$character->id}}/toggleNPC"><i class="icon-graduation-cap"></i> 
					@if($character->is_npc) Remove NPC Status @else Give NPC Status	@endif
					</a></li>
					<li><a href="/dashboard/storyteller/character/{{$character->id}}/toggleActive"><i class="icon-flash"></i> 
						@if($character->active) Inactivate @else Activate @endif
					</a></li>		
					<li><a href="#"><i class="icon-book"></i> Read Lores</a></li>
					<li><a href="/dashboard/character/{{$character->id}}/biography"><i class="icon-vcard"></i> Edit Biography</a><li>								<li><a href="/dashboard/character/{{$character->id}}/cheatsheet"><i class="icon-book-open"></i> Character Reference</a>

					<li><a href="/dashboard/storyteller/character/{{$character->id}}/experience"><i class="icon-ticket"></i> Award Experience</a><li>
					<li><a href="/dashboard/storyteller/character/{{$character->id}}/positions"><i class="icon-trophy"></i> Manage Positions</a></li>
					<li><a href="/dashboard/storyteller/character/{{$character->id}}/timeout"><i class="icon-flight"></i> Set Timeout Date</a></li>	
					<li><a href="/dashboard/character/{{$character->id}}/versioncontrol"><i class="icon-back-in-time"></i> Version Control</a></li>
				  	<li class="warning"><a href="#" data-bind="click: function() { promptDelete('{{$character->name}}', {{$character->id}}) }"><i class="icon-trash"></i> Delete Character</a></li>
				</ul>
				</div>
			</td>
			<td>{{$character->name}}</td>
			<td>{{$character->owner->username}}</td>
			<? $clan = $character->clan()->first(); ?>
			<td>{{$clan ? $clan->definition->name : "No clan"}}</td>
			<td>{{$clan ? $clan->hiddenDefinition->name : "No clan"}}</td>
			<td>{{$character->cachedExperience()}}</td>
			<td>{{$character->approved_version == 0 ? ($character->in_review ? "<i>Pending Approval</i>" : "<i>Incomplete</i>") : 'v'.$character->approved_version}}</td>			
			<td>{{$character->latestVersion()->version == 0 ? ($character->in_review ? "<i>Pending Approval</i>" : "<i>Incomplete</i>") : 'v'.$character->latestVersion()->version}}</td>
			<td>{{$character->versionInfo($character->latestVersion()->version)->updated_at}}
			@if(strpos($filter, 'npcs') === false)
				<td>{{$character->gamesMissed() != -1 ? $character->gamesMissed() : "Never Played"}}</td>
				<? $date = $character->getTimeoutDate(); ?>
			 	<td>{{$date ? $date->format('m/d/Y') : 'N/A'}}</td>
			@endif
		</tr>
	@endforeach
</table>
<p>* Experience totals are cached and may be somewhat behind.</p>

@stop
@stop