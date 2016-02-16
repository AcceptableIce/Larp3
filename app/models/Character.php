<?php

class Character extends Eloquent {

	protected $table = 'characters';

	protected $appends = ['sect', 'clan', 'nature', 'willpower', 'attributes', 'abilities', 'disciplines', 'rituals', 'backgrounds', 'path', 'derangements', 'merits', 'flaws'];
	protected $fillable = array('user_id', 'name');

	public function activeVersion() {
		if($this->approved_version == 0) return 1;
		return $this->approved_version;
	}

	public function latestVersion() {
		return CharacterVersion::where('character_id', $this->id)->orderBy("version", "desc")->first();
	}

	public function version($version) {
		return CharacterVersion::where(array('character_id' => $this->id, 'version' => $version));
	}

	public function versions() {
		return $this->hasMany("CharacterVersion", "character_id", "id");
	}

	public function owner() {
		return $this->belongsTo('User', 'user_id', 'id');
	}

	public function permittedForums() {
		return $this->hasMany("ForumCharacterPermission", "character_id", "id");
	}
	
	public function positions() {
		return $this->hasMany("CharacterPosition", "character_id", "id");
	}
	
	public function clan($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterClan::character($this->id)->version($version);
	}

	public function getClanAttribute() {
		return $this->clan();
	}

	public function sect($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterSect::character($this->id)->version($version);
	}

	public function getSectAttribute() {
		return $this->sect();
	}

	public function nature($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterNature::character($this->id)->version($version);
	}

	public function getNatureAttribute() {
		return $this->nature();
	}

	public function willpower($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterWillpower::character($this->id)->version($version);
	}
	
	public function getWillpowerAttribute() {
		return $this->willpower();
	}

	public function attributes($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterAttributes::character($this->id)->version($version);
	}
	
	public function getAttributesAttribute() {
		return $this->attributes();
	}

	public function abilities($version = -1, $ignoreRankZeros = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreRankZeros) {
			return CharacterAbility::character($this->id)->version($version);
		}
		return CharacterAbility::character($this->id)->where('amount', '>', 0)->version($version);
	}

	public function getAbilitiesAttribute() {
		return $this->abilities();
	}

	public function disciplines($version = -1, $ignoreRankZeros = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreRankZeros) {
			return CharacterDiscipline::character($this->id)->version($version);
		}
		return CharacterDiscipline::character($this->id)->where('ranks', '>', 0)->version($version);
	}

	public function getDisciplinesAttribute() {
		return $this->disciplines();
	}

	public function rituals($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterRitual::character($this->id)->version($version);
	}

	public function getRitualsAttribute() {
		return $this->rituals();
	}

	public function backgrounds($version = -1, $ignoreRankZeros = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreRankZeros) {
			return CharacterBackground::character($this->id)->version($version);
		}
		return CharacterBackground::character($this->id)->where('amount', '>', 0)->version($version);
	}

	public function getBackgroundsAttribute() {
		return $this->backgrounds();
	}

	public function path($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterPath::character($this->id)->version($version);
	}

	public function getPathAttribute() {
		return $this->path();
	}

	public function derangements($version = -1, $ignoreBoughtOff = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreBoughtOff) {
			return CharacterDerangement::character($this->id)->version($version);
		}
		return CharacterDerangement::character($this->id)->where('bought_off', false)->version($version);
	}

	public function getDerangementAttribute() {
		return $this->derangements();
	}

	public function merits($version = -1, $ignoreBoughtOff = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreBoughtOff) return CharacterMerit::character($this->id)->version($version);
		return CharacterMerit::character($this->id)->where('bought_off', false)->version($version);
	}

	public function getMeritsAttribute() {
		return $this->merits();
	}

	public function flaws($version = -1, $ignoreBoughtOff = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreBoughtOff) return CharacterFlaw::character($this->id)->version($version);
		return CharacterFlaw::character($this->id)->where('bought_off', false)->version($version);
	}

	public function getFlawsAttribute() {
		return $this->flaws();
	}

	public function elderPowers($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterElderPower::character($this->id)->version($version);
	}

	public function getElderPowersAttribute() {
		return $this->elderPowers();
	}

	public function comboDisciplines($version = -1, $ignoreBoughtOff = false) {
		if($version == -1) $version = $this->activeVersion();
		if($ignoreBoughtOff) return CharacterComboDiscipline::character($this->id)->version($version);
		return CharacterComboDiscipline::character($this->id)->where('removed', false)->version($version);
	}

	public function getComboDisciplinesAttribute() {
		return $this->comboDisciplines();
	}

	public function clanOptions($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterClanOptions::character($this->id)->version($version);
	}

	public function getClanOptionsAttribute() {
		return $this->clanOptions();
	}
	
	public function hasDiablerizedRecently() {
		$threshold = new DateTime;
		$threshold->modify('3 months ago');
		if(CharacterDiablerieExperience::where('character_id', $this->id)->where('date', '>=', $threshold)->exists()) {
			return true;
		}
		return false;
	}

	public function hasDiablerized() {
		if(CharacterDiablerieExperience::where('character_id', $this->id)->exists()) return true;
		if(CharacterFlaw::where('character_id', $this->id)->whereHas('definition', function($q) { $q->where('name', 'Prior Diablerie'); })->exists()) return true;
		return false;
	}

	public function getTimeoutDate() {
		if($this->time_out) {
			return DateTime::createFromFormat('Y-m-d', $this->time_out);
		}
		$version_one = $this->versionInfo(1);
		return $version_one ? $version_one->created_at->modify('+ 2 years') : null;
	}

	public function hasFlaw($name, $version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return $this->flaws($version)->whereHas('definition', function($q) use ($name) { $q->where('name', $name); })->exists();
	}

	public function hasMerit($name, $version = -1) {
		if($version == -1) $version = $this->activeVersion();		
		return $this->merits($version)->whereHas('definition', function($q) use ($name) { $q->where('name', $name); })->exists();
	}

	public function getBackgroundDots($name, $version = -1) {
		if($version == -1) $version = $this->activeVersion();		
		$val = $this->backgrounds($version)->whereHas('definition', function($q) use ($name) { $q->where('name', $name); })->first();
		return $val ? $val->amount : 0;
	}

	public function inClanDisciplines($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		$clanDefinition = $this->clan($version)->first()->definition;
		if($clanDefinition->name == "Caitiff") {
			$clanOptions = $this->clanOptions($version)->first();
			$disciplines = [];
			$disciplines[] = RulebookDiscipline::where('name', $clanOptions->option1)->first();
			$disciplines[] = RulebookDiscipline::where('name', $clanOptions->option2)->first();
			$disciplines[] = RulebookDiscipline::where('name', $clanOptions->option3)->first();
		} else if ($clanDefinition->name == "Malkavian") {
			$clanOptions = $this->clanOptions($version)->first();
			$disciplines = $clanDefinition->disciplines();
			if($clanOptions->option2) {
				$disciplines[] = RulebookDiscipline::where('name', $clanOptions->option2)->first();
			}
		} else if ($clanDefinition->name == "Tremere") {
			$clanOptions = $this->clanOptions($version)->first();
			$disciplines = $clanDefinition->disciplines();
			if($clanOptions->option1) {
				$disciplines[] = RulebookDiscipline::where('name', $clanOptions->option1)->first();
			}
		} else {
			$disciplines = $clanDefinition->disciplines();
		}
		return $disciplines;
	}

	public function getDifferences($version1, $version2) {
		//Version 2 should be later than version1
		$sheet1 = $this->modifySheetForDiffing($this->getVersion($version1), false);
		$sheet2 = $this->modifySheetForDiffing($this->getVersion($version2), false);
		$out = $this->arrayRecursiveDiff($sheet1, $sheet2);
		return $out;
	}

	function modifySheetForDiffing($sheet) {
		if($sheet == null) return null;
		$sheet["disciplines"] = $this->pull_ids($sheet["disciplines"]->toArray(), "discipline_id");
		$sheet["backgrounds"] = $this->pull_ids($sheet["backgrounds"]->toArray(), "background_id");
		$sheet["abilities"] = $this->pull_ids($sheet["abilities"]->toArray(), "ability_id");
		$sheet["derangements"] = $this->pull_ids($sheet["derangements"]->toArray(), "derangement_id");
		$sheet["rituals"] = $this->pull_ids($sheet["rituals"]->toArray(), "ritual_id");
		$sheet["merits"] = $this->pull_ids($sheet["merits"]->toArray(), "merit_id");
		$sheet["flaws"] = $this->pull_ids($sheet["flaws"]->toArray(), "flaw_id");
		$sheet["elderPowers"] = $this->pull_ids($sheet["elderPowers"]->toArray(), "elder_id");
		$sheet["comboDisciplines"] = $this->pull_ids($sheet["comboDisciplines"]->toArray(), "combo_id");

		return $sheet;
	}

	function pull_ids($array, $key) {
		$ret = [];
		if($array == []) return $ret;
		foreach($array as $a) {
			if($key == "discipline_id" && $a["path_id"] != 0) {
				$val = $a;
				unset($a[$key]);
				$ret[$val[$key]."-".$a["path_id"]] = $a;
			}  else {
				$val = $a;
				unset($a[$key]);
				$ret[$val[$key]] = $a;
			}
		}
		return $ret;
	}

	function putArrayInSlot($array, $slot) {
		$out = [];
		$restrict_array = ["version", "created_at", "updated_at", "id", "free_points", "lost_points", "version_id"];
		foreach($array as $k => $v) {
			if(is_object($v)) $v = $v->toArray();
			if(is_array($v)) {
				$rPut = $this->putArrayInSlot($v, $slot);
				if(count($rPut)) $out[$k] = $rPut;
			} else {
				if(!in_array($k, $restrict_array)) $out[$k] = $slot == 0 ? [$v, null] : [null, $v];
			}
		}
		return $out;
	}
	function arrayRecursiveDiff($aArray1, $aArray2) {
	  $aReturn = array();
	  $restrict_array = ["version", "comment", "created_at", "updated_at", "id", "free_points", "lost_points", "version_id"];
	  if($aArray1 == null) return $this->putArrayInSlot($aArray2, 1);
	  if($aArray2 == null) return $this->putArrayInSlot($aArray1, 0);
	  foreach ($aArray1 as $mKey => $mValue) {
	 	if(is_object($mValue)) $mValue = $mValue->toArray();
	    if (array_key_exists($mKey, $aArray2)) {
	    	$m2Value = $aArray2[$mKey];
	      	if(is_object($m2Value)) $m2Value = $m2Value->toArray();
	      if (is_array($mValue)) {
	        $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $m2Value);
	        if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
	      } else {
	        if ($mValue != $m2Value) {
	          if(!in_array($mKey, $restrict_array)) $aReturn[$mKey] = [$mValue, $m2Value];
	        }
	      }
	    } else {
	      	foreach($mValue as $k=>$v) {
	      		if(!in_array($k, $restrict_array)) $aReturn[$mKey][$k] = [null, $v];
	  		}
	    }
	  }

	  //right to left
	  foreach ($aArray2 as $mKey => $mValue) {
	 	if(is_object($mValue)) $mValue = $mValue->toArray();
	    if (array_key_exists($mKey, $aArray1)) {
	    	$m2Value = $aArray1[$mKey];
	      	if(is_object($m2Value)) $m2Value = $m2Value->toArray();
	      if (is_array($mValue)) {

	        $aRecursiveDiff = $this->arrayRecursiveDiff($m2Value, $mValue);
	        if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
	      } else {
	        if ($mValue != $m2Value) {
	          if(!in_array($mKey, $restrict_array)) $aReturn[$mKey] = [$m2Value, $mValue];
	        }
	      }
	    } else {
	      	foreach($mValue as $k=>$v) {
	      		if(!in_array($k, $restrict_array)) $aReturn[$mKey][$k] = [null, $v];
	  		}
	    }
	  }
	  return $aReturn;
	} 


	public function attributePointsLost($version = -1) {
		$cost = 0;
		if($version == -1) $version = $this->activeVersion();
		foreach(CharacterAttributeLoss::character($this->id)->version($version, "<=")->get() as $loss) {
			$cost += max(1, $loss->rank_loss - 9);
		}
		return $cost;
	}

	public function versionInfo($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return CharacterVersion::where(array('character_id' => $this->id, 'version' => $version))->first();
	}

	public static function activeCharacters() {
		return Character::where(array('is_npc' => false, 'active' => true))->where('approved_version', '>', 0);
	}

	public static function activeNPCs() {
		return Character::where(array('is_npc' => true, 'active' => true));
	}
	
	public function getOptionValue($name) {
		$setting = CharacterStorytellerOption::where('character_id', $this->id)->whereHas('definition', function ($q) use ($name) { $q->where('name', $name); })->first();
		return $setting ? $setting->value : null;	
	}

	public function getDisciplineCost($discipline, $rank, $version) {
		if($rank == 0) return 0;
		$experience_cost = 0;
		$in_clans = $this->inClanDisciplines($version);
		$found = false;
		foreach($in_clans as $in) {
			if($in->id == $discipline->id) $found = true;
		}

		if($found) {
			//It is in clan, so the cost is lower. 
			$cost_array = [3, 6, 12, 18, 27, 39];
			$experience_cost = $cost_array[$rank - 1];
		} else {
			//The cost array is higher, since it is out of clan.
			//However, if it is a common discipline, the scaling is different.
			if($discipline->common) {
				$cost_array = [3, 6, 13, 20, 31];
				$experience_cost = $cost_array[$rank - 1];
			} else {
				$cost_array = [4, 8, 16, 24, 36];
				$experience_cost = $cost_array[$rank - 1];
			}
		}
		return $experience_cost;
	}
	
	public function gamesMissed() {
		$last_game = GameSessionCheckIn::where('character_id', $this->id)->with('session')->get()->sortByDesc('date');
		if($last_game->first()) {
			$last_game_data = $last_game->first();
			return GameSession::where('date', '>', $last_game_data->session->date)->where('submitted', 1)->count();
		}
		return -1;
	}
	
	public function printName() {
		if(Auth::check() && Auth::user()->isStoryteller()) {
			return "<a href='/dashboard/character/$this->id/print'>$this->name</a>";
		}
		return $this->name;
	}

	public function revertChanges($version) {
		if(Auth::user()->isStoryteller()) {
			$this->approved_version = $version;
			$this->in_review = false;
			$this->save();
		}
		CharacterVersion::where('character_id', $this->id)->where('version', '>', $version)->delete();
	}

	public function availableExperience($version = -1) {
		if($version == -1) $version = $this->activeVersion();
		return 10 + $this->experience - $this->getExperienceCost($version);
	}
	
	public function getDisciplinePathCost($discipline, $path, $rank, $version) {
		$cost = $this->getDisciplineCost($discipline, $rank, $version);
		$path = RulebookDisciplinePath::find($path);
		if($path->hard_path) {
			$cost += $rank;
		}
		return $cost;
	}
	public function getVersion($version, $ignoreZeros = true) {
		if($version == 0) return null;
		$out = array();
		$out["id"] = $this->id;
		$out["name"] = $this->name;
		$out["version"] = $this->versionInfo($version);
		$out["sect"] = $this->sect($version)->first();
		$out["clan"] = $this->clan($version)->first();
		$out["nature"] = $this->nature($version)->first();
		$willpower = $this->willpower($version)->first();
		$out["willpower"] = $willpower ? array("traits" => $willpower->willpower_current, "dots" => $willpower->willpower_total) : null;
		$out["attributes"] = $this->attributes($version)->first();
		$out["abilities"] = $this->abilities($version,  $ignoreZeros)->get();
		$out["disciplines"] = $this->disciplines($version, $ignoreZeros)->get();
		$out["rituals"] = $this->rituals($version)->get();
		$out["backgrounds"] = $this->backgrounds($version, $ignoreZeros)->get();
		$out["path"] = $this->path($version)->first();
		$out["derangements"] = $this->derangements($version, $ignoreZeros)->get();
		$out["merits"] = $this->merits($version, $ignoreZeros)->get();
		$out["flaws"] = $this->flaws($version, $ignoreZeros)->get();
		$out["clanOptions"] = $this->clanOptions($version)->first();
		$out["elderPowers"] = $this->elderPowers($version)->get();
		$out["comboDisciplines"] = $this->comboDisciplines($version, $ignoreZeros)->get();
		$out["approved_version"] = $this->approved_version;
		return $out;
	}

	public function awardExperience($amount) {
		$this->experience += $amount;
		$this->save();
		Cache::forget("character-experience-$this->id");
	}

	public function cachedExperience() {
		return Cache::remember("character-experience-$this->id", 24*60, function() {
			return $this->approved_version > 0 ? @$this->availableExperience() : "N/A";
		});
	}
	public function getExperienceCost($version) {
		//Keep a running total of how expensive this sheet is.
		$experience_cost = 0;

		$clanData = $this->clan($version)->first();
		if(!$clanData) return 0;
		$clan = $clanData->definition->name;
		$clanOptions = $this->clanOptions($version)->first();
		$willpower = $this->willpower($version)->first();
		//Willpower costs 3 experience each.
		$experience_cost += max(0, $willpower->willpower_total - 4) * 3 + ($willpower->amount_lost) - ($willpower->amount_free);
		//Attributes cost 1 each, but you start with 15. The 11th costs 2, the 12th costs 3, and the 13th costs 4.
		$attributes = $this->attributes($version)->first();
		$cost_array = [1,2,3,4,5,6,7,8,9,10,12,15,19];
		$totalPointCost = -15 + $cost_array[$attributes->physicals - 1] + $cost_array[$attributes->mentals - 1] + $cost_array[$attributes->socials - 1];
		
		$experience_cost += max(0, $totalPointCost + $this->attributePointsLost($version));
		//If we are at character gen, the attribute total must be at least 15, or you're not using all your attribute points.
		//Storytellers can give free points, however
		$experience_cost -= $attributes->free_points;
		
		//If we have lost ranks in previous versions, we have to include this now.
		
		//Abilities cost 1 each.
		$abilities = $this->abilities($version, true)->get();
		$ability_total = 0;
		foreach($abilities as $a) {
			$ability_total += $a->amount + $a->lost_points - $a->free_points;
			if($clan == "Brujah") {
				if(	$clanOptions->option1 == "Neighborhood" && $a->definition->name == "Streetwise" ||
					$clanOptions->option1 == "Politics" && $a->definition->name == "Politics" ||
					$clanOptions->option1 == "University" && $a->definition->name == "Academics") $ability_total -= 1;
			} else if ($clan == "Gangrel" || $clan == "City Gangrel" || $clan == "Country Gangrel") {
				if($a->definition->name == "Animal Ken" || $a->definition->name == "Survival") $ability_total -= 1;
			} else if ($clan == "Malkavian" && $a->definition->name == "Awareness") {
				$ability_total -= 1;
			} else if ($clan == "Nosferatu" && ($a->definition->name == "Stealth" || $a->definition->name == "Survival")) {
				$ability_total -= 1;
			} else if ($clan == "Toreador") {
				if($a->definition->name == $clanOptions->option1 || $a->definition->name == $clanOptions->option2) {
					$ability_total -= ($clanOptions->option1 == $clanOptions->option2) ? 2 : 1;
				}
			} else if ($clan == "k" && $a->definition->name == "Occult") {
				$ability_total -= 1;
			} else if ($clan == "Daughters of Cacophony" && $a->definition->name == "Performance: Singing") {
				$ability_total -= ($clanOptions->option1 == "Performance (Ability)") ? 2 : 1;
			} else if ($clan == "Gargoyle" && $a->definition->name == "Awareness") {
				$ability_total -= 1;
			} else if ($clan == "Tzimisce" && $a->definition->name == "Occult") {
				$ability_total -= 1;
			} else if ($clan == "Assamite" && ($a->definition->name == "Melee" || $a->definition->name == "Brawl")) {
				$ability_total -= 1;
			} else if ($clan == "Ravnos" && $a->definition->name == "Streetwise") {
				$ability_total -= 1;
			} else if ($clan == "Followers of Set" && $a->definition->name == "Streetwise") {
				$ability_total -= 1;
			}
			if($a->specialization != null) $experience_cost++;

		}
		$experience_cost += max(0, -5 + $ability_total);

		$disciplines = $this->disciplines($version, true)->get();
		$discipline_cost = 0;
		//You begin with three free basics.
		$basic_count = 0;
		
		if($this->clan($version)->exists()) {
			$in_clans = $this->inClanDisciplines($version);
			foreach($disciplines as $d) {

				if($d->path_id > 0) {
					$experience_cost += $this->getDisciplinePathCost($d->definition, $d->path_id, $d->ranks, $version);
				} else {
					$experience_cost += $this->getDisciplineCost($d->definition, $d->ranks, $version);
				}
				$experience_cost += $d->lost_points - $d->free_points;
				if(in_array($d->definition, $in_clans)) {
					$basic_count += min($d->ranks, 2);
				}
			}
			$experience_cost -= min($basic_count * 3, 9);
		}

		$rituals = $this->rituals($version)->get();
		$freeBasic = false;
		foreach($rituals as $r) {
			$ritual_cost = array("Basic" => 2, "Intermediate" => 4, "Advanced" => 6);
			$experience_cost += $ritual_cost[$r->definition->group];
			if(!$freeBasic && $r->definition->group == "Basic" && $r->definition->name != "Rite of Introduction") {
				$experience_cost -= 2;
				$freeBasic = true;
			}
			if($r->definition->name == "Rite of Introduction") $experience_cost -= 2;
		}

		//Backgrounds always cost one, except Generation, which has a scaling cost.
		//We get 5 backgrounds for free, which must be taken at char gen.
		$backgrounds = $this->backgrounds($version, true)->get();
		$backgrounds_cost = 0;
		foreach($backgrounds as $b) {
			if($b->definition->name == "Generation") {
				$generation_cost = [1, 2, 4, 8, 16];
				$backgrounds_cost += $generation_cost[$b->amount - 1];
			} else {
				$backgrounds_cost += $b->amount;
			}
			$backgrounds_cost += $b->lost_points - $b->free_points;
			if($clan == "Brujah") {
				if($clanOptions->option1 == $b->definition->name) $backgrounds_cost -= 1;
			} else if ($clan == "Tremere" && $b->definition->name == "Occult") {
				$backgrounds_cost -= 1;
			} else if ($clan == "Ventrue" && ($b->definition->name == "Resources" || $b->definition->name == $clanOptions->option2)) {
				$backgrounds_cost -= 1;
			} else if ($clan == "Daughters of Cacophony" && $b->definition->name == "High Society" && $clanOptions->option1 == "High Society (Influence)") {
				$backgrounds_cost -= 1;
			} else if ($clan == "Lasombra" && $b->definition->name == $clanOptions->option1) {
				$backgrounds_cost -= 1;
			} else if ($clan == "Ravnos" && $b->definition->name == $clanOptions->option2) {
				$backgrounds_cost -= 1;
			} else if ($clan == "Giovanni" && $b->definition->name == $clanOptions->option1 || $b->definition->name == $clanOptions->option2) {
				$backgrounds_cost -= ($clanOptions->option1 == $clanOptions->option2) ? 2 : 1;
			} else if ($clan == "Followers of Set" && $b->definition->name == $clanOptions->option1 ) {
				$backgrounds_cost -= 1;
			}
			$sect = $this->sect($version)->first();
			if($sect) {
				$sectName = $sect->definition->name;
				if($b->definition->name == "Kindred Lore") {
					$backgrounds_cost -= min($sectName == "Independents" ? 3 : 2, $b->amount);
				} else if ($sectName == "Camarilla") {
					if($b->definition->name == "Camarilla Lore") $backgrounds_cost -= min(2, $b->amount);
					if($b->definition->name == "Kindred Lore") $backgrounds_cost -= min(2, $b->amount);				
				} else if ($sectName == "Sabbat") {
					if($b->definition->name == "Sabbat Lore") $backgrounds_cost -= min(2, $b->amount);
					if($b->definition->name == "Kindred Lore") $backgrounds_cost -= min(2, $b->amount);								
				}
			}
		}
		$experience_cost += max(0, -5 + $backgrounds_cost);

		$path = $this->path($version)->first();

		if(isset($path)) {
			$virtue_total = $path->virtue1 + $path->virtue2 + $path->virtue4;

			//Now we find our morality at start.
			$start_data = CharacterPath::character($this->id)->version(1)->first();
			if($start_data) {
				$start_path = $start_data->definition;
				if($start_path->stats()[0] == "Conviction" || $start_path->stats()[0] == "Instinct") $virtue_total++;
				if($start_path->stats()[1] == "Conviction" || $start_path->stats()[1] == "Instinct") $virtue_total++;
				$start_morality = $start_data->virtue3;
				//Calculate the cost based on our difference from the start. The rest of the cost is calculated by lost_points.
				$experience_cost += ($path->virtue3 - $start_morality)*2;

				//Virtue dots cost 3 each.
				$experience_cost += max(0, (($virtue_total - 10)* 3) + $path->lost_points - $path->free_points);
			}
		}

		//Morality dots cost 2 each.
		//$experience_cost += max(0, $path->virtue3 * 2);

		$merits = $this->merits($version, true)->get();
		$merits_cost = 0;
		foreach($merits as $m) {
			$merits_cost += $m->definition->cost + $m->lost_points - $m->free_points;
			if ($clan == "Gangrel" || $clan == "City Gangrel" || $clan == "Country Gangrel") {
				if($m->definition->name == "Inoffensive to Animals") $merits_cost -= 1;
			}
		}
		$experience_cost += $merits_cost;

		$flaws = $this->flaws($version, true)->get();
		$flaws_cost = 0;
		foreach($flaws as $f) {
			$flaws_cost += $f->definition->cost - $f->lost_points + $f->free_points;
		}
		foreach($this->derangements($version, true)->get() as $index => $d) {
			$flaws_cost += 2 - $d->lost_points + $d->free_points;
			if($clan == "Malkavian" && $index == 0) $flaws_cost -= 2;
		}
		$experience_cost -= $flaws_cost;
		foreach($this->elderPowers($version)->get() as $power) {
			$experience_cost += 12 - $power->free_points + $power->lost_points;
		}

		foreach($this->comboDisciplines($version)->get() as $combo) {
			$experience_cost += $combo->definition->cost($this->id) + $combo->lost_points - $combo->free_points;
		}

		//At character gen, we cannot have more than 7 points of flaws.

		$version_info = $this->versionInfo($version);
		//If we dropped morality at character gen, we get 2 experience.
		if($version_info->hasDroppedMorality == 1) $experience_cost -= 2;

		return $experience_cost;
	}
	// Validates whether or not a given character sheet follows the rules and is thus playable. ST character sheets should NOT need to be validated.
	// Certain rules are only applicable at chargen. The isChargen flag should be set to 'true' to indicate this.
	public function verify($version, $isChargen = false) {

		//Make sure the character has a name...
		$this->assert(strlen(trim($this->name)) > 0, "Characters must have a name.");

		//Make sure that the character has a sect.
		$this->assert($this->sect($version)->first() != null, "No clan selected.");

		//Next, make sure the character has a clan.
		$this->assert($this->clan($version)->first() != null, "No sect selected.");

		//Next, make sure the character has a nature.
		$this->assert($this->nature($version)->first() != null, "No nature selected.");

		//Make sure the character has fewer willpower traits than dots
		$willpower = $this->willpower($version)->first();

		$this->assert($willpower->willpower_current <= $willpower->willpower_total, "You cannot have more Willpower Traits than Willpower Dots.");

		//Make sure the character has fewer than 10 willpower dots
		$this->assert($willpower->willpower_total <= 10, "You cannot have more than 10 Willpower Dots.");

		//Make sure the character has at least 0 willpower traits and dots.
		$this->assert($willpower->willpower_current >= 0, "You cannot have negative Willpower Traits.");		
		$this->assert($willpower->willpower_total >= 0, "You cannot have negative Willpower Dots.");

		//Attributes cost 1 each, but you start with 15. The 11th costs 2, the 12th costs 3, and the 13th costs 4.
		$attributes = $this->attributes($version)->first();
		$cost_array = [1,2,3,4,5,6,7,8,9,10,12,15,19];

		//If we are at character gen, the attribute total must be at least 15, or you're not using all your attribute points.
		if($isChargen) {
			$this->assert($attributes->physicals + $attributes->mentals + $attributes->socials >= 15, "You must spend at least 15 attribute points at character creation.");
			//Additionally, we cannot have below 3 in any attribute.
			$this->assert($attributes->physicals >= 3, "You must have at least 3 Physical Dots at character creation.");
			$this->assert($attributes->mentals >= 3, "You must have at least 3 Mental Dots at character creation.");
			$this->assert($attributes->socials >= 3, "You must have at least 3 Social Dots at character creation.");
		}

		//Abilities cost 1 each.
		$abilities = $this->abilities($version)->get();
		$ability_total = 0;
		foreach($abilities as $a) {
			$ability_total += $a->amount;
		}
		//If we are at character gen, we must use our 5 free abilities.
		if($isChargen) $this->assert($ability_total >= 5, "You must have at least 5 abilities at character creation.");

		$disciplines = $this->disciplines($version)->get();
		$discipline_cost = 0;
		$has_ritual_access = false;
		//You begin with three free basics.
		$basic_count = 0;
		$in_clans = $this->inClanDisciplines($version);
		foreach($disciplines as $d) {
			//At character gen, you cannot have 4th level disciplines.
			if($isChargen) $this->assert($d->ranks < 4, "You cannot take 4th level disciplines at character creation.");
			if($d->definition->name == "Thaumaturgy" || $d->definition->name == "Necromancy") $has_ritual_access = true;
			if(in_array($d->definition, $in_clans)) {
				$basic_count += min($d->ranks, 2);
			}
		}
		$this->assert($basic_count >= 3, "You must select at least 3 basic in-select discipline powers at character creation.");
		
		//If we do not have Thaumaturgy or Necromancy, we cannot own Rituals.
		$rituals = $this->rituals($version)->get();
		if(!$has_ritual_access) {
			$this->assert(sizeof($rituals) == 0, "Characters without Thaumaturgy or Necromancy cannot own rituals.");
		}

		//Backgrounds always cost one, except Generation, which has a scaling cost.
		//We get 5 backgrounds for free, which must be taken at char gen.
		$backgrounds = $this->backgrounds($version)->get();
		$backgrounds_cost = 0;
		foreach($backgrounds as $b) {
			if($b->definition->name == "Generation") {
				$generation_cost = [1, 2, 4, 8, 16];
				$backgrounds_cost += $generation_cost[$b->amount - 1];
			} else {
				$backgrounds_cost += $b->amount;
			}
		}
		if($isChargen) $this->assert($backgrounds_cost >= 5, "You must have at least 5 points of backgrounds at character creation.");

		//We must have a path.
		$path = $this->path($version)->first();
		$this->assert($path != null, "Characters must have a path.");
		//Now, we assert that none of the virtues are over 5, and that we have at least seven at char gen (not including morality).
		$virtue_total = $path->virtue1 + $path->virtue2 + $path->virtue4;
		$path_data = $path->definition;
		if($path_data->stats()[0] == "Conviction" || $path_data->stats()[0] == "Instinct") $virtue_total++;
		if($path_data->stats()[1] == "Conviction" || $path_data->stats()[1] == "Instinct") $virtue_total++;
		$this->assert($path->virtue1 > 0 && $path->virtue2 > 0 && $path->virtue3 > 0 && $path->virtue4 > 0, "All virtues must have at least one Dot.");

		if($isChargen) $this->assert($virtue_total >= 10, "You must have spent all 7 of your virtue points at character creation.");
		//We must also check that morality is either the average of virtue 1 and 2, or one less at char gen.
		$morality_cap = ceil(($path->virtue1 + $path->virtue2) / 2);
		if($isChargen) $this->assert($path->virtue3 == $morality_cap || $path->virtue3 == $morality_cap - 1);

		//Virtue dots cost 3 each. TODO: Account for adding and removing.

		//Morality dots cost 2 each.
		//$experience_cost += max(0, $path->virtue3 * 2);

		$merits = $this->merits($version)->get();
		$merits_cost = 0;
		foreach($merits as $m) {
			$merits_cost += $m->definition->cost;
		}
		//At character gen, we cannot have more than 7 points of merits.
		if($isChargen) $this->assert($merits_cost <= 7, "You can only take a maximum of 7 points of Merits at character creation.");

		$flaws = $this->flaws($version)->get();
		$flaws_cost = 0;
		foreach($flaws as $f) {
			$flaws_cost += $f->definition->cost;
		}
		foreach($this->derangements($version)->get() as $d) {
			$flaws_cost += 2;
		}
		if($this->clan($version)->first()->definition->name == "Malkavian") $flaws_cost -= 2;

		//At character gen, we cannot have more than 7 points of flaws.
		if($isChargen) $this->assert($flaws_cost <= 7, "You can only take a maximum of 7 points of Flaws at character creation.");

		//At character gen, we make sure all of our clan options are selected
		$clanOptionCounts = [	"Brujah" => 1, "Caitiff" => 3, "Malkavian" => 2, "Toreador" => 2, "Ventrue" => 2, "Lasombra" => 2, "Ravnos" => 2,
								"Daughters of Cacophony" => 2, "Setites" => 1, "Giovanni" => 2];
		$clanName = $this->clan($version)->first()->definition->name;
		if($isChargen && array_key_exists($clanName, $clanOptionCounts)) {
			$counts = $clanOptionCounts[$clanName];
			$clanOptions = $this->clanOptions($version)->first();
			switch($counts) {
				case 3:
					$this->assert($clanOptions->option3 != null, "You must select all of your clan options.");
				case 2:
					$this->assert($clanOptions->option2 != null, "You must select all of your clan options.");
				case 1:
					$this->assert($clanOptions->option1 != null, "You must select all of your clan options.");			
			}
		}

		//We must finally make sure we haven't spent more experience than we have.
		$xp_cost = $this->getExperienceCost($version);
		$this->assert($xp_cost <= $this->experience + 10, "You have spent more experience than you have (".$xp_cost." of ".(10 + $this->experience).")");

		return $xp_cost;
	}

	private function assert($condition, $message = "An error occured while validating this character.") {
		if(!$condition) throw new CharacterValidationException($message);
	}
}

class CharacterValidationException extends Exception {}
