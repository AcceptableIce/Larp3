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
		"npcs-active" => Character::where(array('is_npc' => true, 'active' => true))
	];
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
	  <dd class="{{$filter == 'all' ? 'active' : ''}}">
	  	<a href="/dashboard/storyteller/characters/all">All ({{$request['all']->count()}})</a>
	  </dd>
	  <dd class="{{$filter == 'complete' ? 'active' : ''}}">
	  	<a href="/dashboard/storyteller/characters">Complete ({{$request['complete']->count()}})</a>
	  </dd> 
	  <dd class="{{$filter == 'changed' ? 'active' : ''}}">
	  	<a href="/dashboard/storyteller/characters/changed">Changed ({{$request['changed']->count()}})</a>
	  </dd>
	  <dd class="{{$filter == 'new' ? 'active' : ''}}">
	  	<a href="/dashboard/storyteller/characters/new">New ({{$request['new']->count()}})</a>
	  </dd>
	  <dd class="{{$filter == 'npcs' ? 'active' : ''}}">
	  	<a href="/dashboard/storyteller/characters/npcs">NPCs ({{$request['npcs']->count()}})</a>
	  </dd>
	  <dd class="{{$filter == 'npcs-active' ? 'active' : ''}}">
	  	<a href="/dashboard/storyteller/characters/npcs/active">Active NPCs ({{$request['npcs-active']->count()}})</a>
	  </dd>
	</dl>
	<? $items = $request[$filter]->with('owner')->orderBy('name')->paginate(25); ?>
	<div class="character-pagination">{{ $items->links() }}</div>
</div>
<table class="responsive storyteller-character-list-{{$filter}}">
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
				{{$character->printOptions(Auth::user(), $filter)}}
			</td>
			<td>
				{{$character->name}}
			</td>
			<td>
				{{$character->owner->username}}
			</td>
			<? $clan = $character->clan()->first(); ?>
			<td>
				@if($clan)
					@if($clan->definition->name == "Daughters of Cacophony")
						Daughters
					@elseif($clan->definition->name == "Followers of Set")
						Setites
					@else
						{{$clan->definition->name}}
					@endif
				@else
					No clan
				@endif
			</td>
			<td>
				@if($clan)
					@if($clan->hiddenDefinition->name == "Daughters of Cacophony")
						Daughters
					@elseif($clan->hiddenDefinition->name == "Followers of Set")
						Setites
					@else
						{{$clan->hiddenDefinition->name}}
					@endif
				@else
					No clan
				@endif		
			</td>
			<td>
				{{$character->cachedExperience()}}
			</td>
			<td>
				@if($character->approved_version == 0)
					{{$character->in_review ? "<i>Pending Approval</i>" : "<i>Incomplete</i>"}}
				@else
					{{'v'.$character->approved_version}}
				@endif 
			</td>			
			<td>
				@if($character->latestVersion()->version == 0) 
					{{$character->in_review ? "<i>Pending Approval</i>" : "<i>Incomplete</i>"}}
				@else
					{{'v'.$character->latestVersion()->version}}
				@endif
			</td>
			<td>
				{{$character->versionInfo($character->latestVersion()->version)->updated_at}}
			</td>
			@if(strpos($filter, 'npcs') === false)
				<td>
					{{$character->gamesMissed() != -1 ? $character->gamesMissed() : "Never Played"}}
				</td>
			 	<td>
				 	<? $date = $character->getTimeoutDate(); ?>
				 	{{$date ? $date->format('m/d/Y') : 'N/A'}}
				 </td>
			@endif
		</tr>
	@endforeach
</table>
<p>* Experience totals are cached and may be somewhat behind.</p>

@stop
@stop