
<h3>Changes for {{$character->name}}</h3> 
<h5><i>Version {{$version - 1}} <i class="icon-right-thin"></i> Version {{$version}}</i></h5>
@if($character->latestVersion()->version < $version) Version not found. It may have been rejected or rolled back. <? return; ?>@endif
<div class="panel callout">
<label>{{$character->owner->username}} left this comment:</label>
<p>{{$character->latestVersion()->comment}}</p>
</div>
<a class="changes-view-sheet" href="/dashboard/character/{{$character->id}}/print/{{$version}}">View Sheet</a>
<? 
if(!function_exists("print_change")) {
	function print_change($title, $key, $format, $map = null) {
		if(!isset($key)) return;
		if($map) {
			$from = $key[0] == null ? null : call_user_func_array($map, [$key[0]]);
			$to = $key[1] == null ? null : call_user_func_array($map, [$key[1]]);
		} else {
			$from = $key[0];
			$to = $key[1];
		}
		if(strlen($title) > 0) echo "<b>$title</b>";
		if($from == null) {
			echo "<p>".str_replace("\1", "<span class='label success'>$to</span>", $format[0])."</p>";
		} else if($to == null) {
			echo "<p>".str_replace("\1", "<span class='label alert'>$from</span>", $format[2])."</p>";
		} else {
			$change_str = "Changed";
			if(is_numeric($to) && is_numeric($from)) {
				if($to > $from) {
					$change_str = "Increased";
				} else {
					$change_str = "Decreased";
				}
			}
			echo "<p>".str_replace(	["\1", "\2", "->", "[change]"],
			 						["<span class='strikethrough label alert'>$from</span>", "<span class='label success'>$to</span>", "<i class='icon-right-thin'></i>", $change_str]
			 						, $format[1])."</p>";		
		}
	}
}

$differences = $character->getDifferences($version - 1, $version); 

foreach($differences as $key => $value) {
	if($key == "sect") {
		echo "<h5>Sect</h5>";
		print_change(	"",
						@$value["sect_id"],
						["Selected Sect \1.", "Changed Sect from \1 -> \2.", "Removed Sect."],
						function($val) { return RulebookSect::find($val)->name; });
		print_change(	"",
						@$value["hidden_id"],
						["Displaying Sect \1.", "Changed Override Sect from \1 -> \2.", "Removed Override Clan."],
						function($val) { return RulebookSect::find($val)->name; });				
	}
	if($key == "clan") {
		echo "<h5>Nature</h5>";
		print_change(	"",
						@$value["clan_id"],
						["Selected Clan \1.", "Changed Clan from \1 -> \2.", "Removed Clan."],
						function($val) { return RulebookClan::find($val)->name; });
		print_change(	"",
						@$value["hidden_id"],
						["Displaying Clan \1.", "Changed Override Clan from \1 -> \2.", "Removed Override Clan."],
						function($val) { return RulebookClan::find($val)->name; });		
	}
	if($key == "nature") {
		echo "<h5>Nature</h5>";
		print_change(	"",
						@$value["nature_id"],
						["Selected Nature \1.", "Changed Nature from \1 -> \2.", "Removed Nature."],
						function($val) { return RulebookNature::find($val)->name; });
	}
	if($key == "willpower") {
		echo "<h5>Willpower</h5>";
		print_change(	"", 
						@$value["dots"],
						["Began with \1 Dots.", "[change] Dots from \1 -> \2.", "Removed all Dots."]);
		print_change(	"", 
						@$value["traits"],
						["Began with \1 Traits.", "[change] Traits from \1 -> \2.", "Removed all Traits."]);
	}
	if($key == "attributes") {
		echo "<h5>Attributes</h5>";
		print_change(	"", 
						@$value["physicals"],
						["Began with \1 Physical Dots.", "[change] Physical Dots from \1 -> \2.", "Removed all Physical Dots."]);
		print_change(	"", 
						@$value["mentals"],
						["Began with \1 Mental Dots.", "[change] Mental Dots from \1 -> \2.", "Removed all Mental Dots."]);
		print_change(	"", 
						@$value["socials"],
						["Began with \1 Social Dots.", "[change] Social Dots from \1 -> \2.", "Removed all Social Dots."]);		
	}	

	if($key == "abilities") {
		echo "<h5>Abilities</h5>";
		foreach($value as $k => $v) {
			echo '<b>'.RulebookAbility::find($k)->name.'</b>';
			if(isset($v['amount'])) {
				print_change(	"", 
								@$v["amount"],
								["Initial purchase of \1 Dots.", "[change] Dots from \1 -> \2.", "Removed all Dots."]);
			}
			if(isset($v['specialization']) && $v['specialization'][0] != $v['specialization'][1]) {
				print_change(	"", 
								@$v["specialization"],
								["Added specialization \1.", "Changed specialization from \1 -> \2.", "Removed specialization \1."]);
			}
		}
	}
	if($key == "backgrounds") {
		echo "<h5>Backgrounds</h5>";
		foreach($value as $k => $v) {
			$name = RulebookBackground::find($k)->name;
			if(isset($v['description'][0])) {
				$name .= ": ".$v["description"][0];
			} else if(isset($v['description'][1])) {
				$name .= ": ".$v["description"][1];
			}
			print_change(	$name, 
							@$v["amount"],
							["Initial purchase of \1 Dots.", "[change] Dots from \1 -> \2.", "Removed all Dots."]);
		}
	}
	if($key == "rituals") {
		echo "<h5>Rituals</h5>";
		foreach($value as $k => $v) {
			print_change(	"", 
							$v["character_id"][0] == null ? [null, RulebookRitual::find($k)->name] : [RulebookRitual::find($k)->name, null],
							["Purchased \1.", "[change]", "Removed \1."]);
		}
	}
	if($key == "derangements") {
		echo "<h5>Derangements</h5>";
		foreach($value as $k => $v) {
			$description = "";
			if(isset($v['description'][0])) {
				$description = " (Description: <i> ".$v["description"][0]."</i>)";
			} else if(isset($v['description'][1])) {
				$description = " (Description: <i> ".$v["description"][1]."</i>)";
			}
			if(isset($v["character_id"])) {
				print_change(	"", 
					!isset($v["character_id"]) || $v["character_id"][0] == null ? [null, RulebookDerangement::find($k)->name] : [RulebookDerangement::find($k)->name, null],
								["Purchased \1$description.", "[change]", "Removed \1$description."]);
			} else if(isset($v["bought_off"])) {
				print_change("", [RulebookDerangement::find($k)->name, null],
							 ["","","Bought off \1$description."]);
			} else if (isset($v["description"])) {
				print_change("", [null, RulebookDerangement::find($k)->name],
						 ["Changed description of \1 to $description.","",""]);
			}
		}
	}

	if($key == "merits") {
		echo "<h5>Merits</h5>";
		foreach($value as $k => $v) {

			$description = "";
			if(isset($v['description'][0])) {
				$description = " (Description: <i> ".$v["description"][0]."</i>)";
			} else if(isset($v['description'][1])) {
				$description = " (Description: <i> ".$v["description"][1]."</i>)";
			}
			if(isset($v["character_id"])) {
				print_change(	"", 
								!isset($v["character_id"]) || $v["character_id"][0] == null ? [null, RulebookMerit::find($k)->name] : [RulebookMerit::find($k)->name, null],
								["Purchased \1$description.", "[change]", "Removed \1$description."]);
			} else if(isset($v["bought_off"])) {
				print_change("", [RulebookMerit::find($k)->name, null],
							 ["","","Bought off \1$description."]);
			} else if (isset($v["description"])) {
				print_change("", [null, RulebookMerit::find($k)->name],
						 ["Changed description of \1 to $description.","",""]);
			}
		}
	}

	if($key == "flaws") {
		echo "<h5>Flaws</h5>";
		foreach($value as $k => $v) {
			$description = "";
			if(isset($v['description'][0])) {
				$description = " (Description: <i> ".$v["description"][0]."</i>)";
			} else if(isset($v['description'][1])) {
				$description = " (Description: <i> ".$v["description"][1]."</i>)";
			}
			if(isset($v["character_id"])) {
				print_change(	"", 
								!isset($v["character_id"]) || $v["character_id"][0] == null ? [null, RulebookFlaw::find($k)->name] : [RulebookFlaw::find($k)->name, null],
								["Purchased \1$description.", "[change]", "Removed \1$description."]);
			} else if(isset($v["bought_off"])) {
				print_change("", [RulebookFlaw::find($k)->name, null],
							 ["","","Bought off \1$description."]);
			} else if (isset($v["description"])) {
				print_change("", [null, RulebookFlaw::find($k)->name],
						 ["Changed description of \1 to $description.","",""]);
			}
		}
	}
	if($key == "disciplines" && sizeof($value) > 0) {
		echo "<h5>Disciplines</h5>";
		foreach($value as $k => $v) {
			//Get rank difference
			$r1 = isset($v["ranks"][0]) ? $v["ranks"][0] : 0;
			$r2 = isset($v["ranks"][1]) ? $v["ranks"][1] : 0;
			$diff = $r2 - $r1;
			if(strpos($k, "-") !== FALSE) {
				$parts = explode("-", $k);
				$k = $parts[0];
				$path = $parts[1];
			}
			$discipline = RulebookDiscipline::find($k);
			if(isset($path)) {
				$discipline = RulebookDisciplinePath::find($path);
			}
			if($diff != 0) {
				$order = $diff > 0 ? [$r1, $r2] : [$r2, $r1];
				for($i = $order[0]; $i < $order[1]; $i++) {
						$diff_array = $diff > 0 ? 	[null, $discipline->ranks()->get()[$i]->name." (".$discipline->name." ".($i + 1).")"] : 
													[$discipline->ranks()->get()[$i]->name." (".$discipline->name." ".($i + 1).")", null];
						print_change("",
									$diff_array,
									["Purchased \1.", "[change]", "Removed \1."]);
				}
			}
		
		}
	}
	if($key == "elderPowers" && sizeof($value) > 0) {
		echo "<h5>Elder Powers</h5>";
		foreach($value as $k => $v) {
			print_change(	"", 
					$v["character_id"][0] == null ? [null, RulebookElderPower::find($k)->name,] : [RulebookElderPower::find($k)->name, null],
					["Purchased \1.", "[change]", "Removed \1."]);
		}
	}
	if($key == "comboDisciplines" && sizeof($value) > 0) {
		echo "<h5>Combo Disciplines</h5>";
		foreach($value as $k => $v) {
			print_change(	"", 
					$v["character_id"][0] == null ? [RulebookComboDiscipline::find($k)->name, null] : [null, RulebookComboDiscipline::find($k)->name],
					["Purchased \1.", "[change]", "Removed \1."]);
		}
	}

	if($key == "path") {
		echo "<h5>Path</h5>";
		$path = $character->path($character->latestVersion()->version)->first();
		if($path) {
			$path = $path->definition;
			print_change(	"", 
							@$value["path_id"],
							["Began on \1.", "Changed from \1 -> \2.", "Removed path (Is this a mistake?)."],
							function($val) { return RulebookPath::find($val)->name; });
			print_change(	"", 
							@$value["virtue1"],
							["Began with \1 Dots of ".$path->stats()[0].".", "[change] Dots of ".$path->stats()[0]." from \1 -> \2.", "Removed all Dots of ".$path->stats()[0]."."]);				
			print_change(	"", 
							@$value["virtue2"],
							["Began with \1 Dots of ".$path->stats()[1].".", "[change] Dots of ".$path->stats()[1]." from \1 -> \2.", "Removed all Dots of ".$path->stats()[1]."."]);			
			print_change(	"", 
							@$value["virtue3"],
							["Began with \1 Dots of ".$path->stats()[2].".", "[change] Dots of ".$path->stats()[2]." from \1 -> \2.", "Removed all Dots of ".$path->stats()[2]."."]);				
			print_change(	"", 
							@$value["virtue4"],
							["Began with \1 Dots of ".$path->stats()[3].".", "[change] Dots of ".$path->stats()[3]." from \1 -> \2.", "Removed all Dots of ".$path->stats()[3]."."]);				
		}
	}
}
?>
<? 
if(Auth::user()->isStoryteller()) {
	if($version > $character->approved_version) { ?>
		<form method="POST" action="/dashboard/storyteller/character/{{$character->id}}/accept" style="display: inline-block;"><input type="submit" class="button medium success" value="Accept Changes" /></form>
		<form method="POST" action="/dashboard/storyteller/character/{{$character->id}}/reject" style="display: inline-block;"><input type="submit" class="button medium alert" value="Reject Changes" /></form>
	<? } else { ?>
		<b>This version has already been processed. </b>
	<? } 
}?>
