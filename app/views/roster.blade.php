@extends('layout')
@section('title', 'Character Roster')
@section('includes') 
<style type="text/css">
.clan-title {
	background-color: #A33643;
	color: #fff;
	padding: 10px 20px;
	font-size: 1.2em;
	clear: both;
}

.clan-member {
	border-bottom: 1px solid #e0e0e0;
}

.clan-members .clan-member:last-child {
	border-bottom: none;
}
.clan-member .columns {
	padding: 0.5em 2em;
}

.clan-members {
	border: 1px solid #e0e0e0;
}

.clan-members .row {
	margin: 0 auto;
}

.row.clan-row {
}

.roster-wrapper .npc, .roster-wrapper .npc a {
	color: #6397BA;
	margin-bottom: 0px;
}

.position-list {
	font-size: 0.8em;
	font-style: italic;
	text-transform: uppercase;
	font-weight: 300;
	color: #333;
}

.position-list.character-row {
	margin-top: -13px;
    padding-left: 2.5em;
}

.clan-row .small-12 {
	margin-bottom: 15px;
}

.clan-member a {
	color: #000;
}


</style>
@stop
@section('content')
<div class="roster-wrapper theme-wrapper">
	<div class="row roster-content">
		<div class="small-12 columns">
			<h1>Character Roster</h1>
			<p>
				Player Characters are in black. 
				NPCs are in <span class="npc">blue</span>.<br>
				<b>
					This is an out-of-character reference. 
					Please take care to not confuse your knowledge of clan and position with your character's.
				</b>
			</p>
			<?
				$query =	DB::table('characters')->select('hidden_id')->where('active', true)
							->join('characters_versions', function($join) {
								$join->on('characters_versions.character_id', '=', 'characters.id');
								$join->on('characters_versions.version', '=', 'characters.approved_version');
							})->join('characters_clan', function($join) {
								$join->on('characters.id', '=', 'characters_clan.character_id');
								$join->on('characters_clan.version_id', '=', 'characters_versions.id');
							})->join('rulebook_clans', 'characters_clan.hidden_id', '=', 'rulebook_clans.id')
							->groupBy('hidden_id')->orderBy('rulebook_clans.name');
			?>
			<? $i = 0; ?>
			@foreach($query->get() as $q) 
				<? $i = ($i + 1) % 2; ?>
				@if($i == 1) 
					<div class="row large-uncollapse clan-row"> 
				@endif
		
				<div class="columns small-12 medium-6">
					<div class="clan-title">
						{{RulebookClan::find($q->hidden_id)->name}}
					</div>
					<div class="clan-members">
						<?
							$member_query = DB::table('characters')->select('characters.id')->where('active', true)
											->join('characters_versions', function($join) {
												$join->on('characters_versions.character_id', '=', 'characters.id');
												$join->on('characters_versions.version', '=', 'characters.approved_version');
											})
											->join('characters_clan', function($join) {
												$join->on('characters.id', '=', 'characters_clan.character_id');
												$join->on('characters_clan.version_id', '=', 'characters_versions.id');	
											})->where('characters_clan.hidden_id', $q->hidden_id)->orderBy('name');
						?>
						@foreach($member_query->get() as $member)
							<? $character = Character::find($member->id); ?>
							<div class="row clan-member">
								@if($character->is_npc)
									<div class="columns small-12 npc">
										{{$character->printName()}}
										<div class="position-list">
											{{CharacterPosition::with('definition')->where('character_id', $character->id)->get()
												->map(function($item, $key) { return $item->definition; })->implode('name', ', ')}}
										</div>
									</div>
								@else
									<div class="row">
										<div class="columns small-6">
											{{$character->printName()}}
										</div>
										<div class="columns small-6">
											{{$character->owner->mailtoLink()}}
										</div>
									</div>
									<? $positions= CharacterPosition::with('definition')->where('character_id', $character->id)->get(); ?>
									@if($positions->count() > 0)
									<div class="row">
										<div class="columns small-12 position-list character-row">				
											{{$positions->map(function($item, $key) { return $item->definition; })->implode('name', ', ')}}
										</div>
									</div>
									@endif
								@endif
							</div>
						@endforeach
					</div>	
				</div>
		
				@if($i == 0) 
					</div>
				@endif
			@endforeach
		</div>
	</div>
</div>
@stop