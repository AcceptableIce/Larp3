@if($user->isStoryteller())
	@if(!$character->in_review)
		<a href="/generator/{{$character->id}}">
			<i class="icon-pencil" data-tooltip data-options="disable_for_touch:true" title="Edit"></i>
		</a>
	@endif
	@if($filter == "changed" || $filter == "new")
		<a href="/dashboard/storyteller/character/{{$character->id}}/changes">
			<i class="icon-check" data-tooltip data-options="disable_for_touch:true" title="Approve or Reject"></i>
		</a>
	@endif
	<a href="/dashboard/character/{{$character->id}}/print">
		<i class="icon-print" data-tooltip data-options="disable_for_touch:true" title="Print"></i>
	</a>
	<a href="#" data-dropdown="drop-{{$character->id}}" data-options="align:bottom" >
		<i class="icon-dot-3"></i>
	</a>
	<div class="character-dropdown-holder">
		<ul id="drop-{{$character->id}}" data-dropdown-content class="f-dropdown small drop-bottom">
			<li>
				<a href="/dashboard/storyteller/character/{{$character->id}}/toggleNPC">
					<i class="icon-graduation-cap"></i> 
					@if($character->is_npc) Remove NPC Status @else Give NPC Status	@endif
				</a>
			</li>
			<li>
				<a href="/dashboard/storyteller/character/{{$character->id}}/toggleActive">
					<i class="icon-flash"></i> 
					@if($character->active) Inactivate @else Activate @endif
				</a>
			</li>		
			<li>
				<a href="/dashboard/character/{{$character->id}}/lores">
					<i class="icon-book"></i> 
					Read Lores
				</a>
			</li>
			<li>
				<a href="/dashboard/character/{{$character->id}}/biography">
					<i class="icon-vcard"></i> 
					Edit Biography
				</a>
			<li>
			<li>
				<a href="/dashboard/character/{{$character->id}}/cheatsheet">
					<i class="icon-book-open"></i> 
					Character Reference
				</a>
			<li>
				<a href="/dashboard/storyteller/character/{{$character->id}}/experience">
					<i class="icon-ticket"></i> 
					Award Experience
				</a>
			<li>
			<li>
				<a href="/dashboard/storyteller/character/{{$character->id}}/experience/transfer">
					<i class="icon-flow-line"></i> 
					Transfer Experience
				</a>
			</li>
			<li>
				<a href="/dashboard/storyteller/character/{{$character->id}}/positions">
					<i class="icon-trophy"></i> 
					Manage Positions
				</a>
			</li>
			<li>
				<a href="/dashboard/storyteller/character/{{$character->id}}/timeout">
					<i class="icon-flight"></i> 
					Set Timeout Date
				</a>
			</li>	
			<li>
				<a href="/dashboard/character/{{$character->id}}/versioncontrol">
					<i class="icon-back-in-time"></i> 
					Version Control
				</a>
			</li>
		  	<li class="warning">
		  		<a href="#" data-bind="click: function() { promptDelete('{{$character->name}}', {{$character->id}}) }">
			  		<i class="icon-trash"></i> 
			  		Delete Character
			  	</a>
			</li>
		</ul>
	</div>
@else
	@if(!$character->in_review)
		<a href="/generator/{{$character->id}}"><i class="icon-pencil" data-tooltip title="Edit"></i></a>
	@endif
					
		<a href="/dashboard/character/{{$character->id}}/biography">
			<i class="icon-vcard" data-tooltip data-options="disable_for_touch:true" title="Edit Biography"></i>
		</a>
		<a href="/dashboard/character/{{$character->id}}/print">
			<i class="icon-print" data-tooltip data-options="disable_for_touch:true" title="Print"></i>
		</a>
		<a href="#" data-dropdown="drop-{{$character->id}}" data-options="align:bottom">
			<i class="icon-dot-3"></i>
		</a>
		
		<ul id="drop-{{$character->id}}" data-dropdown-content class="f-dropdown small drop-bottom">
			@if($user->isStoryteller())
				<li>
					<a href="/dashboard/storyteller/character/{{$character->id}}/toggleNPC">
						<i class="icon-graduation-cap"></i> 
						@if($character->is_npc) Remove NPC Status @else Give NPC Status	@endif
					</a>
				</li>
			@endif
			<li>
				<a href="/dashboard/character/{{$character->id}}/lores">
					<i class="icon-book"></i> 
					Read Lores
				</a>
			</li>
			<li>
				<a href="/dashboard/character/{{$character->id}}/cheatsheet">
					<i class="icon-book-open"></i> 
					Character Reference
				</a>
			</li>
			<li>
				<a href="/dashboard/character/{{$character->id}}/versioncontrol">
					<i class="icon-back-in-time"></i> 
					Version Control
				</a>
			</li>
			<li class="warning">
				<a href="#" data-bind="click: function() { promptDelete('{{$character->name}}', {{$character->id}}) }">
					<i class="icon-trash"></i> 
					Delete Character
				</a>
			</li>
		</ul>
@endif