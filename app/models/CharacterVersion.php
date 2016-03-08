<?php

class CharacterVersion extends Eloquent {

	protected $table = 'characters_versions';
	protected $fillable = array('character_id', 'version', 'hasDroppedMorality', 'comment');
	
	protected $touchedRecords = [	"CharacterAbility" => [], "CharacterBackground" => [], "CharacterDiscipline" => [], "CharacterDerangement" => [], 
																"CharacterMerit" => [], "CharacterFlaw" => [], "CharacterElderPower" => [], "CharacterComboDiscipline" => [] ];
	
	protected $idLookup = ["rituals" => []];
	
	protected $editingUser = null;
	
	public static function createNewVersion(Character $character, $comment = null, $dropped = false) {
		//Get the active version
		$newVersion = new CharacterVersion;
		$newVersion->character_id = $character->id;
		$newVersion->version = $character->approved_version + 1;
		$newVersion->comment = $comment;
		$newVersion->save();
		$newVersion->copyDataFromPreviousVersion();
		return $newVersion;
	}
	
	public function character() {
		return $this->belongsTo('Character', 'character_id', 'id');
	}
	
	public function setEditingUser(User $user) {
		$this->editingUser = $user;
	}
	
	public function editingAsStoryteller() {
		return $this->editingUser && $this->editingUser->isStoryteller();
	}
	
	public function setHasDroppedMorality($value) {
		$this->hasDroppedMorality = $value;
		$this->save();
	}
	
	public function setSect(RulebookSect $sect, RulebookSect $displaySect = null) {
		$sectRecord = $this->findOne('CharacterSect');
		
		if($sectRecord) {
			if(!$this->editingAsStoryteller()) return;
		} else {
			$sectRecord = $this->createNewRecord('CharacterSect');
		}
		
		$sectRecord->sect_id = $sect->id;
		if($displaySect) {
			$sectRecord->hidden_id = $displaySect->id;
		}
		$sectRecord->save();
	}
	
	public function setClan(RulebookClan $clan, RulebookClan $displayClan = null) {
		$clanRecord = $this->findOne('CharacterClan');
		
		if($clanRecord) {
			if(!$this->editingAsStoryteller()) return;
		} else {
			$clanRecord = $this->createNewRecord('CharacterClan');
		}
		
		$clanRecord->clan_id = $clan->id;
		if($displayClan) {
			$clanRecord->hidden_id = $displayClan->id;
		}
		$clanRecord->save();
	}
	
	public function setClanOptions($option1, $option2, $option3) {
		$optionRecord = $this->findOne('CharacterClanOptions');
		if(!$optionRecord) {
			$optionRecord = $this->createNewRecord('CharacterClanOptions');
			$optionRecord->option1 = $option1;
			$optionRecord->option2 = $option2;
			$optionRecord->option3 = $option3;
			$optionRecord->save();
		}
	}
	
	public function setNature(RulebookNature $nature) {
		$natureRecord = $this->findOrCreateOne('CharacterNature');
		$natureRecord->nature_id = $nature->id;
		$natureRecord->save();
	}
	
	public function setWillpower($dots, $traits) {
		$willpowerRecord = $this->findOne("CharacterWillpower");
		
		if($willpowerRecord) {
			$willpowerDifference = $dots - $willpowerRecord->willpower_total;
			if($willpowerDifference > 0) {
				if($this->editingAsStoryteller()) {
					$willpowerRecord->amount_free += $willpowerDifference;
				}
			} else if ($willpowerDifference < 0) {
				if(!$this->editingAsStoryteller()) {
					$willpowerRecord->amount_lost += abs($willpowerDifference);
				}
			}
		} else {
			$willpowerRecord = $this->createNewRecord("CharacterWillpower");
			if($this->editingAsStoryteller()) {
				$willpowerRecord->amount_free = max(0, $willpowerRecord->willpower_total - 4) * 3;
			}
		}
		
		$willpowerRecord->willpower_total = $dots;
		$willpowerRecord->willpower_current = $traits;
		$willpowerRecord->save();
	}
	
	public function setAttributes($physicals, $mentals, $socials) {
		$attributeRecord = $this->findOne("CharacterAttributes");
		if($attributeRecord) {
			$physicalsDifference = $physicals - $attributeRecord->physicals;
			$mentalsDifference = $mentals - $attributeRecord->mentals;
			$socialsDifference = $socials - $attributeRecord->socials;
			
			//If we have lost attributes, record the loss for experience calculation purposes.
			if($physicalsDifference < 0) {
				for($i = $physicals + 1; $i <= $attributeRecord->physicals; $i++) {
					$rankLost = $this->createNewRecord("CharacterAttributeLoss");
					$rankLost->rank_lost = $i;
					$rankLost->save();
				}
			} else if($physicalsDifference > 0 && $this->editingAsStoryteller())  {
				for($i = $attributeRecord->physicals + 1; $i <= $physicals; $i++) {
					$attributeRecord->free_points += max(1, $i - 9);
				}
			}
			
			if($mentalsDifference < 0) {
				for($i = $mentals + 1; $i <= $attributeRecord->mentals; $i++) {
					$rankLost = $this->createNewRecord("CharacterAttributeLoss");
					$rankLost->rank_lost = $i;
					$rankLost->save();
				}
			} else if($mentalsDifference > 0 && $this->editingAsStoryteller())  {
				for($i = $attributeRecord->mentals + 1; $i <= $mentals; $i++) {
					$attributeRecord->free_points += max(1, $i - 9);
				}
			}
			
			if($socialsDifference < 0) {
				for($i = $socials + 1; $i <= $attributeRecord->socials; $i++) {
					$rankLost = $this->createNewRecord("CharacterAttributeLoss");
					$rankLost->rank_lost = $i;
					$rankLost->save();
				}
			} else if($socialsDifference > 0 && $this->editingAsStoryteller())  {
				for($i = $attributeRecord->socials + 1; $i <= $socials; $i++) {
					$attributeRecord->free_points += max(1, $i - 9);
				}
			}
		} else {
			$attributeRecord = $this->createNewRecord("CharacterAttributes");
		}
		
		$attributeRecord->physicals = $physicals;			
		$attributeRecord->mentals = $mentals;
		$attributeRecord->socials = $socials;
		$attributeRecord->save();
	}
	
	public function addAbility($ability, $count, $name = null) {
		$abilityRecord = $this->findOneWhere("CharacterAbility", "ability_id", $ability ? $ability->id : null);
		
		if($abilityRecord) {
			$rankDifference = $count - $abilityRecord->amount;
			if($rankDifference < 0) {
				$abilityRecord->lost_points += abs($rankDifference);
			} else if ($rankDifference > 0 && $this->editingAsStoryteller()) {
				$abilityRecord->free_points += $rankDifference;
			}
		} else {
			$abilityRecord = $this->createNewRecord("CharacterAbility");
			if(!$ability) {
				$ability = new RulebookAbility;
				$ability->name = $name;
				$ability->isCustom = true;
				$ability->owner = $this->character_id;
				$ability->save();
				if($this->editingAsStoryteller()) {
					$abilityRecord->free_points += $count;
				}
			}
			
			$abilityRecord->ability_id = $ability->id;
			
		}
		
		$abilityRecord->amount = $count;
		$abilityRecord->save();
		$this->touchedRecords["CharacterAbility"][] = $abilityRecord->id;
		return $abilityRecord;
	}
	
	public function addAbilityWithSpecialization($ability, $count, $specialization, $name = null) {
		$abilityRecord = $this->addAbility($ability, $count, $name);
		if(!$abilityRecord->specialization) {
			$abilityRecord->specialization = $specialization;
			if($this->editingAsStoryteller()) $abilityRecord->free_points += 1;
		}
		$abilityRecord->save();
	}
	
	public function addBackground(RulebookBackground $background, $amount, $description = null) {
		$query = CharacterBackground::character($this->character_id)
			->version($this->version)
			->where('background_id', $background->id);
		if($description) {
			$query = $query->where('description', $description);
		} else {
			$query = $query->whereNull('description');
		}
		$backgroundRecord = $query->first();
			
		if($backgroundRecord) {			
			$rankDifference = $amount - $backgroundRecord->amount;
			if($rankDifference < 0) {
				if($backgroundRecord->definition->name == "Generation") {
					$generationCost = [1, 2, 4, 8, 16];
					$backgroundRecord->lost_points += $generationCost[$backgroundRecord->amount - 1] - $generationCost[$amount - 1];
				} else {
					$backgroundRecord->lost_points += abs($rankDifference);
				}
			} else if($rankDifference > 0 && $this->editingAsStoryteller()) {
				if($backgroundRecord->definition->name == "Generation") {
					$generationCost = [1, 2, 4, 8, 16];
					$backgroundRecord->free_points += $generationCost[$amount - 1] - $generationCost[$backgroundRecord->amount - 1];
				} else {
					$backgroundRecord->free_points += $rankDifference;
				}
			}
		} else {
			$backgroundRecord = $this->createNewRecord("CharacterBackground");
			
			$backgroundRecord->background_id = $background->id;
			if($description) {
				$backgroundRecord->description = $description;
			}
			if($this->editingAsStoryteller()) {
				if($background->name == "Generation") {
					$generationCost = [1, 2, 4, 8, 16];
					$backgroundRecord->free_points += $generationCost[$amount - 1];
				} else {
					$backgroundRecord->free_points += $amount;
				}
			}
		}
		
		$backgroundRecord->amount = $amount;
		$backgroundRecord->save();
		
		$this->touchedRecords["CharacterBackground"][] = $backgroundRecord->id;
	}
	
	public function updateDiscipline(RulebookDiscipline $discipline, $amount, $path = 0) {
		$disciplineRecord = CharacterDiscipline::character($this->character_id)->version($this->version)
			->where('discipline_id', $discipline->id)
			->where('path_id', $path)
			->where('ranks', '!=', 0)
			->first();
					
		$inClanBasicCount = $this->character->countInClanBasics($this->version);
		if($disciplineRecord) {			
			$rankDifference = $amount - $disciplineRecord->ranks;
			if($rankDifference < 0) {
				//Burn points if we have lost a rank.
				$disciplineRecord->lost_points += 
					$this->character->getDisciplinePathCost($disciplineRecord->definition, $path, $disciplineRecord->ranks, $this->version) -
					$this->character->getDisciplinePathCost($disciplineRecord->definition, $path, $amount, $this->version);
			} else if ($rankDifference > 0 && $this->editingAsStoryteller()) {
				//Storytellers receive free points.
				$disciplineRecord->free_points += 
					$this->character->getDisciplinePathCost($disciplineRecord->definition, $path, $amount, $this->version) -
					$this->character->getDisciplinePathCost($disciplineRecord->definition, $path, $disciplineRecord->ranks, $this->version);
			}
		} else {
			$disciplineRecord = $this->createNewRecord("CharacterDiscipline");
			$disciplineRecord->discipline_id = $discipline->id;
			$disciplineRecord->path_id = $path;
			if($this->editingAsStoryteller()) {
				$disciplineRecord->free_points += 
					$this->character->getDisciplinePathCost($disciplineRecord->definition, $path, $amount, $this->version)
					- ($this->character->isDisciplineInClan($discipline, $this->version) ? min(3 - $inClanBasicCount, min($amount, 2)) * 3 : 0);
			}
		}
		
		$disciplineRecord->ranks = $amount;
		$disciplineRecord->save();
		
		$this->touchedRecords["CharacterDiscipline"][] = $disciplineRecord->id;		
	}
	
	//Player-defined rituals that do not yet exist
	public function addRitualToBook($name, $description, $type) {
		$customRitual = new RulebookRitual;
		$customRitual->name = $name;
		$customRitual->description = $description;
		$customRitual->group = $type;
		$customRitual->isCustom = true;
		$customRitual->owner = $this->character_id;
		$customRitual->save();	
		
		//Add this ritual to the lookup table so that we can use it's fake ID.
		$this->idLookup["rituals"][$ritualData["id"]] = $customRitual->id;
	}
	
	public function addRitual($ritualId) {
		if($ritualId < 0) {
			$ritualId = $this->idLookup["rituals"][$ritualId];
		}
		$ritualRecord = $this->findOneWhere("CharacterRitual", "ritual_id", $ritualId);
		if(!$ritualRecord) {
			$ritualRecord = $this->createNewRecord("CharacterRitual");
			$ritualRecord->ritual_id = $ritualId;
			if($this->editingAsStoryteller()) {
				$ritualRecord->is_free = true;
			}
			$ritualRecord->save();
		}
	}
	
	public function updatePath(RulebookPath $path, $virtue1, $virtue2, $virtue3, $virtue4) {
		$pathRecord = $this->findOne('CharacterPath');
		if($pathRecord) { 
			$virtueDifferences = [
				$virtue1 - $pathRecord->virtue1,
				$virtue2 - $pathRecord->virtue2,
				$virtue3 - $pathRecord->virtue3,
				$virtue4 - $pathRecord->virtue4
			];
			
			foreach($virtueDifferences as $index => $difference) {
				if($difference < 0) {
					//Index 2 = Morality, which only costs 2.
					$pathRecord->lost_points += abs($difference) * ($index == 2 ? 2 : 3);
				} elseif ($difference > 0 && $this->editingAsStoryteller()) {
					$pathRecord->free_points += abs($difference) * ($index == 2 ? 2 : 3);					
				}
			}
		} else {
			$pathRecord = $this->createNewRecord("CharacterPath");
			$total = $virtue1 + $virtue2 + $virtue4;
			if($this->editingAsStoryteller() && $total > 10) {
				//We've purchased additional points.
				$pathRecord->free_points = ($total - 10) * 3;
			}
		}
		
		$pathRecord->path_id = $path->id;
		$pathRecord->virtue1 = $virtue1;
		$pathRecord->virtue2 = $virtue2;
		$pathRecord->virtue3 = $virtue3;
		$pathRecord->virtue4 = $virtue4;
		$pathRecord->save();
	}
	
	public function addDerangement(RulebookDerangement $derangement, $description = null) {
		$derangementRecord = CharacterDerangement::character($this->character_id)
			->version($this->version)
			->where('derangement_id', $derangement->id)
			->where('bought_off', false)
			->first();
		if(!$derangementRecord) {
			$derangementRecord = $this->createNewRecord("CharacterDerangement");
			$derangementRecord->derangement_id = $derangement->id;
			
			if($description && strlen($description) > 0) {
				$derangementRecord->description = $description;
			}
			
			if($this->editingAsStoryteller() || !$this->isNewCharacter()) {
				$derangementRecord->lost_points += 2;
			}
			
			$derangementRecord->save();
		}
		
		$this->touchedRecords["CharacterDerangement"][] = $derangementRecord->id;
	}
	
	public function addFlaw(RulebookFlaw $flaw, $description = null) {
		$query = CharacterFlaw::character($this->character_id)
			->version($this->version)
			->where('flaw_id', $flaw->id)
			->where('bought_off', false);
			
		if($description) {
			$query = $query->where('description', $description);
		} else {
			$query = $query->whereNull('description');
		}
		$flawRecord = $query->first();
		
		if(!$flawRecord) {
			
			$flawRecord = $this->createNewRecord("CharacterFlaw");
			$flawRecord->flaw_id = $flaw->id;
			
			if($this->editingAsStoryteller() || !$this->isNewCharacter()) {
				$flawRecord->lost_points += $flaw->cost;
			}
			
			if($description) {
				$flawRecord->description = $description;
			}
			
			$flawRecord->save();
		}
		
		$this->touchedRecords["CharacterFlaw"][] = $flawRecord->id;
	}
	
	public function addMerit(RulebookMerit $merit, $description = null) {
		$query = CharacterMerit::character($this->character_id)
			->version($this->version)
			->where('merit_id', $merit->id)
			->where('bought_off', false);
			
		if($description) {
			$query = $query->where('description', $description);
		} else {
			$query = $query->whereNull('description');
		}
		$meritRecord = $query->first();
			
		if(!$meritRecord) {
			
			$meritRecord = $this->createNewRecord("CharacterMerit");
			$meritRecord->merit_id = $merit->id;

			if($this->editingAsStoryteller()) {
				$meritRecord->free_points += $merit->cost;
			} else if (!$this->isNewCharacter()) {
				$meritRecord->lost_points = $merit->cost;
			}
			
			if($description) {
				$meritRecord->description = $description;
			}
			
			$meritRecord->save();
		}
		
		$this->touchedRecords["CharacterMerit"][] = $meritRecord->id;
	}
	
	public function addElderPower($elderData) {
		$elderRecord = $this->findOneWhere("CharacterElderPower", "elder_id", $elderData["id"]);
		if(!$elderRecord) {
			$elderDefinition = RulebookElderPower::firstOrCreate([
				'owner_id' => $this->character_id,
				'discipline_id' => $elderData['discipline'],
				'name' => $elderData['name'],
				'description' => $elderData['description']
			]);
			
			$elderRecord = $this->createNewRecord("CharacterElderPower");
			$elderRecord->elder_id = $elderDefinition->id;
			if($this->editingAsStoryteller()) $elderRecord->free_points = 12;
			$elderRecord->save();
		}
		
		$this->touchedRecords["CharacterElderPower"][] = $elderRecord->id;
	}
	
	public function addComboDiscipline($comboData) {
		$comboRecord = $this->findOneWhere('CharacterComboDiscipline', 'combo_id', $comboData['id']);
		if(!$comboRecord) {
			$comboDefinition = RulebookComboDiscipline::firstOrCreate([
				"owner_id" => $this->character_id,
				"name" => $comboData["name"],
				"option1" => $comboData["option1"],
				"option2" => $comboData["option2"],
				"option3" => strlen($comboData["option3"]) == 0 ? null : $comboData["option3"],
				"description" => $comboData["description"]
			]);
			
			$comboRecord = $this->createNewRecord("CharacterComboDiscipline");
			$comboRecord->combo_id = $comboDefinition->id;
			if($this->editingAsStoryteller()) {
				$comboRecord->free_points = $comboDefinition->cost($this->character_id);
			}
			
			$comboRecord->save();
		}
		
		$this->touchedRecords["CharacterComboDiscipline"][] = $comboRecord->id;
	}	
	
	public function clearUntouchedRecords() {
		foreach($this->touchedRecords as $className => $idList) {
			$untouchedRecords = $className::character($this->character_id)->version($this->version)->whereNotIn('id', $idList)->get();
			foreach($untouchedRecords as $untouchedRecord) {
				//Different record types have different rules
				switch($className) {
					case "CharacterAbility":
						$untouchedRecord->lost_points += $untouchedRecord->amount;
						$untouchedRecord->amount = 0;
						$untouchedRecord->save();
						break;
					case "CharacterDiscipline":
						$untouchedRecord->lost_points += $this->character->getDisciplineCost($untouchedRecord->definition, $untouchedRecord->ranks, $this->version);
						$untouchedRecord->ranks = 0;
						$untouchedRecord->save();
						break;
					case "CharacterDerangement":
						if(!$untouchedRecord->bought_off) {
							$untouchedRecord->lost_points += $this->editingAsStoryteller() ? 2 : 4;
							$untouchedRecord->bought_off = true;
							$untouchedRecord->save();
						}
						break;
					case "CharacterMerit":
						if($this->editingAsStoryteller() && !$untouchedRecord->bought_off) {
							$untouchedRecord->lost_points += $untouchedRecord->definition->cost;
							$untouchedRecord->bought_off = true;
							$untouchedRecord->save();
						}
						break;
					case "CharacterFlaw":
						if(!$untouchedRecord->bought_off) {
							$untouchedRecord->lost_points += $untouchedRecord->definition->cost * ($this->editingAsStoryteller() ? 1 : 2);
							$untouchedRecord->bought_off = true;
							$untouchedRecord->save();
						}
						break;
					case "CharacterBackground":
						if($untouchedRecord->definition->name == "Generation") {
							$generationCost = [1, 2, 4, 8, 16];
							$untouchedRecord->lost_points += $generationCost[$untouchedRecord->amount - 1];
						} else {
							$untouchedRecord->lost_points += $untouchedRecord->amount;
						}
						$untouchedRecord->amount = 0;
						$untouchedRecord->save();
						break;
					case "CharacterComboDiscipline":
					case "CharacterElderPower":
						$untouchedRecord->removed = true;
						$untouchedRecord->save();
						break;
				}
			}
		}
	}
				
	private function copyDataFromPreviousVersion() {
		$previousVersion = $this->previousVersion();
		if($previousVersion) {
			$this->copyOne('CharacterSect', $previousVersion);
			$this->copyOne('CharacterClan', $previousVersion);
			$this->copyOne('CharacterClanOptions', $previousVersion);
			$this->copyOne('CharacterNature', $previousVersion);
			$this->copyOne('CharacterWillpower', $previousVersion);			
			$this->copyOne('CharacterAttributes', $previousVersion);			
			$this->copyOne('CharacterAttributeLoss', $previousVersion);	
			$this->copyMany('CharacterAbility', $previousVersion);	
			$this->copyMany('CharacterDiscipline', $previousVersion);
			$this->copyMany('CharacterRitual', $previousVersion);
			$this->copyOne('CharacterPath', $previousVersion);
			$this->copyMany('CharacterDerangement', $previousVersion);
			$this->copyMany('CharacterMerit', $previousVersion);
			$this->copyMany('CharacterFlaw', $previousVersion);
			$this->copyMany('CharacterBackground', $previousVersion);
			$this->copyMany('CharacterElderPower', $previousVersion);
			$this->copyMany('CharacterComboDiscipline', $previousVersion);
			$this->hasDroppedMorality = $previousVersion->hasDroppedMorality;
			$this->save();
		}
	}
	
	private function copyOne($className, $version) {
		$previousRecord = $className::character($this->character_id)->version($version->version)->first();
		if($previousRecord) {
			$newRecord = $previousRecord->replicate();
			$newRecord->version_id = $this->id;
			$newRecord->save();
		}
	}
	
	private function copyMany($className, $version) {
		$previousRecords = $className::character($this->character_id)->version($version->version)->get();
		foreach($previousRecords as $previousRecord) {
			$newRecord = $previousRecord->replicate();
			$newRecord->version_id = $this->id;
			$newRecord->save();
		}
	}
	
	private function findOne($className) {
		return $className::character($this->character_id)->version($this->version)->first();
	}
	
	private function findOneWhere($className, $key, $value) {
		return $className::character($this->character_id)->version($this->version)->where($key, $value)->first();
	}
	private function findOrCreateOne($className) {
		$record = $this->findOne($className);
		if(!$record) {
			$record = $this->createNewRecord($className);
		}
		return $record;
	}
	
	private function createNewRecord($className) {
		$newRecord = new $className();
		$newRecord->character_id = $this->character_id;
		$newRecord->version_id = $this->id;
		return $newRecord;
	}
	
	private function arrayKeyExistsAndHasContent($key, $array) {
		return array_key_exists($key, $array) && strlen($array[$key]) > 0;
	}
		
	
	public function previousVersion() {
		if($this->version != 1)  {
			return Character::find($this->character_id)->version($this->version - 1)->first();	
		}
		return null;
	}
	
	public function isNewCharacter() {
		return $this->version == 1;
	}
	
	public function setDroppedMorality($dropped) {
		$this->hasDroppedMorality = $dropped;
		$this->save();
	}

}
