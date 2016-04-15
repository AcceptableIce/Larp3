<link rel="stylesheet" type="text/css" href="/css/characterSheet.css" />
<div class="character-sheet">
<? 
	function print_dots($total, $crossAmt) {
		$out = "";
		for($i = 0; $i < $total; $i++) {
			$cross = $i >= ($total - $crossAmt) ? 'cross' : '';
			$out .= "<div class='dot $cross'>○</div>";
		}
		return $out;
	}
	
	if(!$character) {
		echo '<h3>Character not found.</h3>';
		die();
	}
	if($version == -1) $version = $character->activeVersion();
	if($version > $character->latestVersion()->version) {
		echo '<h3>Version not found.</h3>';
		die();
	}

	$requires_chop = $character->derangements($version)->whereHas('definition', function($q) { $q->where('requires_chop', true); })->exists();
?> 
<h3>
	{{$character->name}} 
	@if($version != $character->approved_version) 
		(Version {{$version}}) 
	@endif 
</h3>
<div class="row character-sheet-row collapse">
	<div class="stats columns">
		<div class="list-label character-stat">
			Player:
		</div>
		<div class="list-dots character-data">
			{{$character->owner->username}}
		</div>
		
		<div class="list-label character-stat">
			Sect:
		</div>
		<div class="list-dots character-data">
			<? $sect = $character->sect($version)->first(); ?>
			{{$sect ? $sect->definition->name : ""}}
		</div>
		
		<div class="list-label character-stat">
			Clan:
		</div>
		<div class="list-dots character-data">
			<? $clan = $character->clan($version)->first(); ?>
			{{$clan ? $clan->definition->name : ""}}
		</div>
		
		<? $path = $character->path($version)->first(); ?>
		@if($path)
			<div class="list-label character-stat">
				Path:
			</div>
			<div class="list-dots character-data">
				{{str_replace(["The Path of the", "The Path of"], "", $path->definition->name)}}
			</div>
		@endif

		<div class="list-label character-stat">
			Nature:
		</div>
		<div class="list-dots character-data">
			<? $nature = $character->nature($version)->first(); ?>
			{{$nature ? $nature->definition->name : ""}}
		</div>
		
		<? 
			$gen = $character->backgrounds($version)->whereHas('definition', function($q) { 
				$q->where('name', 'Generation'); 
			})->first();
			
			$gen_amt = $gen ? $gen->amount : 0; 
			if($character->hasFlaw("Fourteenth Generation")) $gen_amt = -1;
			if($character->hasFlaw("Fifteenth Generation")) $gen_amt = -2; 
		?>
		<div class="list-label character-stat">
			Generation:
		</div>
		<div class="list-dots character-data">
			{{13 - $gen_amt}}
		</div>
		
		<? $willpower = $character->willpower($version)->first(); ?>
		<div class="list-label character-stat">
			Willpower:
		</div>
		<div class="list-dots">
			{{print_dots($willpower->willpower_total, $willpower->willpower_total - $willpower->willpower_current)}}
		</div>
		
		<? 	//We start at 10. Higher generation characters get more blood, 1 per generation.
			$blood_pool = 10 + max(0, $gen_amt);
			$st_chop = mt_rand(0, 2);
			$pc_chop = mt_rand(0,2);
			if($st_chop > $pc_chop) {
				$blood = 4; //Sad day.
			} else if ($st_chop == $pc_chop) {
				$blood = ceil($blood_pool/2);
			} else {
				$blood = $blood_pool - 1;
			}
			//If we have the Stigmata flaw, we lose another blood.
			if($character->flaws($version)->whereHas('definition', function($q) { 
				$q->where('name', 'Stigmata'); 
			})->exists()) $blood--;			
			
			//If we have ghouls, we lose one point per ghoul. There... isn't really a good way of doing this 
			//for an odd number of ghouls, so I round up. Sorry players.
			if($character->clan($version)->first()->definition->name != 'Tsimisce') {
				$ghoul_total = 0;
				foreach($character->backgrounds($version)->whereHas('definition', function($q) { 
					$q->where('name', 'Ghouls'); 
				})->get() as $ghoul) {
					$ghoul_total += $ghoul->amount;
				}
				$blood -= ceil($ghoul_total / 2);
			}
		?>
		
		<div class="list-label character-stat">
			Blood:
		</div>
		<div class="list-dots character-data">
			{{$blood}}/{{$blood_pool}}
		</div>
		
		@if($character->hasDiablerizedRecently())
			<p>Recently Diablerized</p>
		@endif
	</div>
	<div class="damage-track columns">
		<?
		//Now we build the damage track. If a character has the Huge Size merit or Fort 2, they get an additional box (for each!);
		$hugesize = $character->hasMerit('Huge Size');
		$fort_request = $character->disciplines($version)->whereHas('definition', function($q) { 
			$q->where('name', 'Fortitude'); 
		})->first();
		$fortitude_1 = $fort_request && $fort_request->ranks >= 1;
		$fortitude_2 = $fort_request && $fort_request->ranks >= 2;

		$boxes = ["H" => 2, "B" => 3, "W" => 2, "I" => 1];
		if($fortitude_2) $boxes["H"]++;
		if($hugesize) $boxes["B"]++;
		if($character->getOptionValue("Additional Healthy Box") == 1) $boxes["H"]++;
		if($character->getOptionValue("Additional Bruised Box") == 1) $boxes["B"]++;
		if($character->getOptionValue("Additional Wounded Box") == 1) $boxes["W"]++;
		if($character->getOptionValue("Additional Incapacitated Box") == 1) $boxes["I"]++;
		
		?>
		@foreach($boxes as $key => $value)
			@for($i = 0; $i < $value; $i++)
				<div class="damage-track-box">
					@if($i == 0 && $key == "B")
						<div class="damage-track-marker track-marker-left">+0</div>
					@endif
					@if($i == 0 && $key == "W" && !$fortitude_1)
						<div class="damage-track-marker track-marker-left">+1</div>
					@endif				
					<span>{{$key}}</span>
					@if($i == 0 && $key == "B")
						<div class="damage-track-marker track-marker-right">-1</div>
					@endif
					@if($i == 0 && $key == "W" && !$fortitude_1)
						<div class="damage-track-marker track-marker-right">LT</div>
					@endif		
				</div>		
			@endfor
		@endforeach
		<div class="damage-track-last-row">
			<div class="damage-track-box">U</div>	
			<div class="damage-track-box" style="width: 21px;">T</div>	
			<div class="damage-track-box fd-box">FD</div>	
		</div>	
	
	</div>
	<div class="attributes columns">
		<? $traits = $character->attributes($version)->first(); ?>
		<div class="attribute-row">
			<label class="list-label">({{$traits->physicals}}) Physical:</label> 
			{{print_dots($traits->physicals, 0)}}
		</div>
		<div class="attribute-row">
			<label class="list-label">({{$traits->mentals}}) Mental:</label> 
			{{print_dots($traits->mentals, 0)}}
		</div>
		<div class="attribute-row">
			<label class="list-label">({{$traits->socials}}) Social:</label> 
			{{print_dots($traits->socials, 0)}}
		</div>
		
		@if($path)
			<br>
			<? $virtues = $path->definition->stats();?>
			<div class="attribute-row">
				<label class="list-label">{{$virtues[2]}}:</label> 
				{{print_dots($path->virtue3, 0)}}
			</div>
			<div class="attribute-row">
				<label class="list-label">{{$virtues[0]}}:</label> 
				{{print_dots($path->virtue1, 0)}}
			</div>
			<div class="attribute-row">
				<label class="list-label">{{$virtues[1]}}:</label> 
				{{print_dots($path->virtue2, 0)}}
			</div>
			<div class="attribute-row">
				<label class="list-label">{{$virtues[3]}}:</label> 
				{{print_dots($path->virtue4, 0)}}
			</div>
		@endif
	</div>
	@if($path)
		<div class="sins columns">
			<b>Sins</b><br> 
			<? $i = 5; ?>
			@foreach($path->definition->sins() as $s)
				<div class="sin-count">{{$i--}}:</div> 
				<div class="sin-value">{{$s}}</div>
			@endforeach
		</div> 
	@endif
	<div class="frenzy-triggers columns">
		<b>Frenzy Triggers</b><br>
		<div class="sin-count">5:</div>	
		<div class="sin-value">Outright humiliation; mortal insults</div>
		
		<div class="sin-count">4:</div>	
		<div class="sin-value">Loved one in danger; humilation</div>
		
		<div class="sin-count">3:</div> 
		<div class="sin-value">Physical provocation or attacks; taste of blood when hungry</div>
		
		<div class="sin-count">2:</div> 
		<div class="sin-value">Sight of blood when hungry; harassed; life-threatening situations</div>
		
		<div class="sin-count">1:</div>	
		<div class="sin-value"> Being bullied; smell of blood when hungry (Blood &le; 4)</div>
	</div>
</div>
<div class="row character-sheet-row collapse">
	<div class="columns abilities">
		<b>Abilities</b> <br>
		<? 	$abilities = $character->abilities($version)->with('definition')->get();
			$abilities = $abilities->sortBy('definition.name');
		?>
		@foreach($abilities as $ability) 
			<div class="list-label">
				{{$ability->definition->name}} 
				@if($ability->specialization)
					<div class="specialization">
						{{$ability->specialization}}
					</div>
				@endif
			</div> 
			<div class="list-dots">
				{{print_dots($ability->amount, 0)}}
			</div>
		@endforeach
	</div>
	<div class="columns backgrounds">
		<b>Backgrounds</b><br>
		<? 	$backgrounds = $character->backgrounds($version)->with('definition')->get(); 
			$backgrounds = $backgrounds->sortBy('definition.name')->groupBy('definition.group')->sortBy('definition.group');
			$order = ['Backgrounds', 'Influence', 'Lores'];
		?>
		@foreach($order as $key)
			@if(isset($backgrounds[$key]))
				@foreach($backgrounds[$key] as $background) 
					<div class="list-label">
						{{$background->definition->name}}
						@if($background->description)
							<div class="specialization">
								{{$background->description}}
							</div>
						@endif
					</div>
					<div class="list-dots">
						{{print_dots($background->amount, 0)}}
					</div>
				@endforeach
			<div class="background-spacer"></div>
			@endif
		@endforeach
	</div>	
	<div class="columns merits">
		<b>Merits</b><br>
		@foreach($character->merits($version)->with('definition')->get()->sortBy('definition.name') as $merit) 
			<div class="merit-flaw-box">
				{{$merit->definition->name}}
				@if($merit->description)
					<div class="specialization">
						{{$merit->description}}
					</div>
				@endif
				@if($merit->definition->short_description)
					<div class="short-description">
						{{$merit->definition->short_description}}
					</div>
				@endif
			</div>
		@endforeach
		
		<? $rituals = $character->rituals($version)->with('definition')->get()->sortBy('definition.name')->sortBy('definition.group'); ?>
		<br>
		@if($rituals->count() > 0)
			<b>Rituals</b><br>
			@foreach($rituals as $rit) 
				<div class="merit-flaw-box">
					{{substr($rit->definition->group, 0, 1)}}: {{$rit->definition->name}}
				</div>
			@endforeach
		@endif
	</div>	
	<div class="columns flaws">
		<b>Flaws</b><br>
		@if($character->clan($version)->first()->definition->name == "Ventrue")
			<div class="merit-flaw-box">
				Feeding Restriction
				<div class="specialization">
					{{$character->clanOptions($version)->first()->option1}}
				</div>
			</div>
		@endif
		@foreach($character->flaws($version)->with('definition')->get()->sortBy('definition.name') as $flaw) 
			<div class="merit-flaw-box">
				{{$flaw->definition->name}}
				@if($flaw->description)
					<div class="specialization">
						{{$flaw->description}}
					</div>
				@endif
				@if($flaw->definition->short_description)
					<div class="short-description">
						{{$flaw->definition->short_description}}
					</div>
				@endif
			</div>
		@endforeach
		<br>
		<? $derangements = $character->derangements($version)->with('definition')->get()->sortBy('definition.name'); ?>
		@if($derangements->count() > 0)
			<b>Derangements</b><br>
			@foreach($derangements as $der) 
				<div class="merit-flaw-box">
					{{$der->definition->name}}
					@if($der->description)
						<div class="specialization">
							{{$der->description}}
						</div>
					@endif
			</div>
			@endforeach
		@endif
	</div>	
	<div class="columns rotschreck-triggers">
		<b>R&ouml;tschreck Triggers</b><br>
		<div class="sin-count">5:</div>	
		<div class="sin-value"> Trapped in a burning building; direct sunlight</div>
		
		<div class="sin-count">4:</div>	
		<div class="sin-value"> House fire; being burned</div>
		
		<div class="sin-count">3:</div>	
		<div class="sin-value"> Bonfire; uncovered window during daylight</div>
		
		<div class="sin-count">2:</div> 
		<div class="sin-value"> Torch; obscured sunlight</div>
		
		<div class="sin-count">1:</div>	
		<div class="sin-value"> Cigarette lighter; sunrise</div>
	</div>	

	
	<div class="columns resources">
		<b>Resources</b><br>
		<? 	$resources_amount = 0;
			$resources_to_money = [200, 500, 1000, 3000, 10000, 30000];
			$cutoffs = [[], [20], [20, 500], [30, 1000, 2000], [40, 2500, 5000, 7500], [50, 6000, 12000, 18000, 24000]];
			$resource_dots = $character->getBackgroundDots("Resources", $version);
		 ?>
		 <b class="resource-total">
		 	You have ${{number_format($resources_to_money[$resource_dots])}} tonight.
		 </b>
		 <br>
		 @for($i = 0; $i < $resource_dots; $i++)
		 	<div class="resource-row">
		 		If you spend more than <b>${{number_format($cutoffs[$resource_dots][$i])}}</b>, 
		 		mark off {{$i + 1}} Trait{{$i == 0 ? "" : "s"}}.
		 	</div>
		 @endfor
	</div>		
</div>

<? $elders = $character->elderPowers($version)->get(); ?>
<div class="row character-sheet-row collapse">
	@foreach($character->disciplines($version)->with('definition')->get()->sortBy('definition.name') as $discipline)
	<div class="columns discipline">
		<?  $path = $discipline->path_id ? RulebookDisciplinePath::find($discipline->path_id) : null; 
			$ranks = $path ? $path->ranks()->get() : $discipline->definition->ranks()->get();
		?>
		<b>
			{{$discipline->definition->name}}
			@if($discipline->path_id)
				: <span class="discipline-path-name">{{$path->name}}</span>
			@endif
		</b><br>
		
		<i class="retest">
			@if($discipline->definition->retest != "None") 
				Retested with {{$discipline->definition->retest}} 
			@else 
				No Retest 
			@endif
		</i><br>
		
		@for($i = 0; $i < $discipline->ranks; $i++)
			<div class="discipline-row">{{$ranks[$i]->name}}</div>
		@endfor
		@foreach($elders as $elder)
			@if($elder->definition->discipline_id == $discipline->definition->id)
				<div class="discipline-row">{{$elder->definition->name}}</div>
			@endif
		@endforeach
	</div>
	@endforeach
	<? $combos = $character->comboDisciplines($version)->get(); ?>
	@if($combos->count() > 0)
		<div class="columns discipline">
			<b>Combo Disciplines</b>
			@foreach($combos as $combo)
				<div class="discipline-row">{{$combo->definition->name}}</div>
			@endforeach
		</div>
	@endif
</div>
@if($requires_chop)
<div class="requires-chop">
	This character requires a chop for the following derangements:<br>
	
	@foreach($character->derangements($version)->whereHas('definition', function($q) { 
		$q->where('requires_chop', true); 
	})->get() as $d)
		<div class="discipline-row">- {{$d->definition->name}}</div>
	@endforeach
	
	<div class="requires-chop-disclaimer">This box will not be printed.</div>
</div>
@endif