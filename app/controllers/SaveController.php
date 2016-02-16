<?php
class SaveController extends BaseController {

	public function saveCharacter() {
		if(!Auth::check()) return Response::json(array("success" => false, "message" => "Unable to validate user."));
		$character_data = Input::get('sheet');
		$user = Auth::user();
		$continue_save = Input::get("continue");
		
		// Make sure we have a name.
		if(strlen(trim($character_data["name"])) == 0) {
			return Response::json(["success" => false, "mode" => 2, "message" => "Characters must have a name."]);
		}
		
		DB::beginTransaction();
		$character_exists = Character::where('id', Input::get('characterId'))->exists();
		if($character_exists) {
			if(Character::where('id', Input::get('characterId'))->first()->user_id != $user->id && !$user->isStoryteller()) {
				return Response::json(array("success" => false, "mode" => 0, "message" => "Unauthorized"));
			}
		}
		try {
			$character = $this->save($character_data, $user);
			if(Input::get("review") && !($character->getOptionValue("Ignore Validation") || $user->isStoryteller())) $character->verify($character->approved_version + 1, $character->approved_version == 0);
		} catch (Exception $e) {
			DB::rollback();
			if(get_class($e) == "CharacterValidationException") {
				return Response::json(array("success" => false, "mode" => 2, "message" => $e->getMessage()));
			}
			return Response::json(array("success" => false, "mode" => 0, "message" => $e->getMessage()."[".$e->getFile()."$".$e->getLine()."]"));
		}

		DB::commit();
		if(Input::get("review") && !$user->isStoryteller()) {
			//41 = Character Changes
			$topic = ($character_exists ? "Character Changes to " : "New Character ")."\"".$character->name."\" from ".$user->username;
			$version = $character->latestVersion()->version;
			Forum::find(41)->post($topic, "[[change/$character->id/$version]]");
		}
		if($user->isStoryteller() && Input::get("review")) {
			$character->approved_version = $character->latestVersion()->version;
			$character->in_review = false;
			if(!$character->is_active) 	$character->approved_at = new DateTime;
			$character->save();
		}
		return Response::json(array("success" => true, "mode" => $continue_save ? 1 : 0, "message" => (Input::get("review") ? "In review queue." : "Saved successfully.")));
	}

	public function deleteCharacter() {
		//The auth for this function is done by the routes controller.
		//Don't expose this method without authenticating!
		$character = Character::find(Input::get("characterId"));
		if($character) {
			$character->delete();
			return Response::json(["success" => true, "message" => "Character deleted."]);		
		} else {
			return Response::json(["success" => false, "message" => "Character could not be found."]);
		}
	}

	public function revertCharacter() {
		$character = Character::find(Input::get("characterId"));
		if($character) {
			$character->revertChanges(Input::get("version"));
			return Response::json(["success" => true, "message" => "Character reverted."]);		
		} else {
			return Response::json(["success" => false, "message" => "Character could not be found."]);
		}
	}

	public function resetCurrentChanges() {
		$character = Character::find(Input::get("characterId"));
		if($character) {
			CharacterVersion::where('character_id', $character->id)->where('version', '>', $character->activeVersion())->delete();
			return Redirect::to('/generator/'.$character->id);	
		} else {
			return Response::json(["success" => false, "message" => "Character could not be found."]);
		}
	}
	
	public function saveBiography($id) {
		$question_ids = Input::get('ids');
		$replies = Input::get('replies');
		$character = Character::find($id);
		if($character) {
			foreach($question_ids as $index => $q) {
				$response = CharacterQuestionnaire::firstOrNew(['character_id' => $character->id, 'questionnaire_id' => $q]);
				$response->response = $replies[$index];
				$response->save();
			}
			if(Input::hasFile('backstory')) {
				$file = Input::file('backstory');
				$fileName = preg_replace("([^\w\d\-_~,;:\[\]\(\).])", '', $character->name."Backstory.".$file->getClientOriginalExtension());
				$file->move(public_path().'/content/backstories/', $fileName);
				$character->backstory_file = $fileName;
				$character->save();
			}
			if($character->active || $character->in_review) {
				//Find the relevant forum post and bump it (or create it if it doesn't exist)
				$post = ForumPost::where('body', "[[questionnaire/$id]]")->first();
				if($post) {
					$topic = $post->topic;
					$topic->postReply($character->owner->id, "Questionnaire responses updated. (Automatic system post)");
				} else {
					//42 = Character Backgrounds
					$topic = Forum::find(42)->post("Character Biography for ".$character->name, "[[questionnaire/$id]]");
					$topic->markAsRead(Auth::user());
				}
				return Redirect::to("/forums/topic/".$topic->id);
			} else {
				return Redirect::to("/dashboard/characters");
			}
		} else {
			return Response::json(["success" => false, "message" => "Character could not be found."]);			
		}
	}

	public function getCost() {
		if(!Auth::check()) return Response::json(array("success" => false, "message" => "Unable to validate user."));
		$character_data = Input::get('sheet');
		$user = Auth::user();		
		DB::beginTransaction();
		try { 
			$char = $this->save($character_data, $user);
		} catch (Exception $e) {
			DB::rollback();
			return Response::json(array("success" => false, "message" => $e->getMessage()."[".$e->getFile()."$".$e->getLine()."]"));
		}
		$xpc = $char->getExperienceCost($char->approved_version + 1);
		DB::rollback();
		return Response::json(array("success" => true, "cost" => $xpc));
	}

	public function save($character_data, $user) {
			$user_id = $user->id;
			$new_character = false;
			$character = Character::firstOrNew(['id' => Input::get('characterId')]);
			if(!isset($character->user_id)) {
				$character->user_id = $user_id;
				$new_character = true;
			}
			$character->name = $character_data["name"];
			$character->in_review = Input::get("review") == 1;					
			$character->save();
			
			//Inactivate other characters.
			if(!$user->isStoryteller() && $character->in_review) {
				foreach(Character::where(['user_id' => $user_id, 'active' => true])->where('id', '!=', $character->id)->get() as $active_character) {
					$active_character->active = false;
					$active_character->save();
				}
			}

			$character_id = $character->id;
			$active_version = $character->approved_version + 1;
			//Create the version we're going to use for this.
			CharacterVersion::where(array('character_id' => $character_id, 'version'=> $active_version))->delete();

			$version_info = new CharacterVersion;
			$version_info->character_id = $character_id;

			//Dropping humanity doesn't give free XP after character creation.
			if($active_version != 1) {
				$old_version = CharacterVersion::where(array('character_id' => $character_id, 'version'=> $active_version - 1))->first();
				$version_info->hasDroppedMorality = $old_version->hasDroppedMorality;
			} else {
				$version_info->hasDroppedMorality = $character_data["hasDroppedMorality"] == "true" ? 1 : 0;
			}
			$version_info->version = $active_version;
			$version_info->comment = Input::get("comment");
			$version_info->save();

			$active_version_id = $version_info->id;

			//Check to see if we've already set a sect for the previous version
			$sect = CharacterSect::character($character_id)->version($active_version - 1)->first();
			//Delete all sects from the current version
			CharacterSect::character($character_id)->version($active_version)->delete();

			if(isset($sect)) {
				//Only STs can change a character's sect after character creation.
				$newSect = $sect->replicate();
			//	if($user->isStoryteller()) { 
					$newSect->sect_id = $character_data["sect"]["selected"];
					$newSect->hidden_id = $character_data["sect"]["displaying"];
			//	}
				$newSect->version_id = $active_version_id;
				$newSect->save();
			} else if($active_version == 1) {
				//If it's the first version, you can set a sect.
				if($character_data["sect"]["selected"] != null) {
					$sect = new CharacterSect;
					$sect->character_id = $character_id;
					$sect->sect_id = $character_data["sect"]["selected"];
					$sect->hidden_id = $character_data["sect"]["displaying"];
					$sect->version_id = $active_version_id;
					$sect->save();
				}
			}

			
			//Check to see if we've already set a clan for previous version
			$clan = CharacterClan::character($character_id)->version($active_version - 1)->first();
			//Delete all sects from the current version
			CharacterClan::character($character_id)->version($active_version)->delete();
			if(isset($clan)) {
				$newClan = $clan->replicate();
				/*if(!$user->isStoryteller()) {
					//$newClan->clan_id = $character_data["clan"]["selected"];
					$newClan->hidden_id = $character_data["clan"]["displaying"];
				}*/
				$newClan->version_id = $active_version_id;
				$newClan->save();
			} else if($active_version == 1) {
				if($character_data["clan"]["selected"] != null) {
					$clan = new CharacterClan;
					$clan->character_id = $character_id;
					$clan->clan_id = $character_data["clan"]["selected"];
					$clan->hidden_id = $character_data["clan"]["displaying"];
					$clan->version_id = $active_version_id;
					$clan->save();
				}
			}

			//Save our clan options. This can never be changed after character creation, even by Storytellers
			//(it'll mess things up, and you shouldn't need to anyways).
			$clanOptions = CharacterClanOptions::character($character_id)->version($active_version - 1)->first();
			CharacterClanOptions::character($character_id)->version($active_version)->delete();
			if(isset($clanOptions)) {
				$newClanOptions = $clanOptions->replicate();
				$newClanOptions->version_id = $active_version_id;
				$newClanOptions->save();
			} else if($active_version == 1) {
				$clanOptions = new CharacterClanOptions;
				$clanOptions->character_id = $character_id;
				$clanOptions->option1 = isset($character_data["clanOptions"][0]) ? $character_data["clanOptions"][0] : null;
				$clanOptions->option2 = isset($character_data["clanOptions"][1]) ? $character_data["clanOptions"][1] : null;
				$clanOptions->option3 = isset($character_data["clanOptions"][2]) ? $character_data["clanOptions"][2] : null;
				$clanOptions->version_id = $active_version_id;
				$clanOptions->save();
			}

			//Delete any existing nature records for this version. We can change nature at any time.
			CharacterNature::character($character_id)->version($active_version)->delete();
			if($character_data["nature"] != null) {
				$nature = new CharacterNature;
				$nature->character_id = $character_id;
				$nature->nature_id = $character_data["nature"];
				$nature->version_id = $active_version_id;
				$nature->save();
			}

			//Select the willpower for the previous version.
			$old_willpower = CharacterWillpower::character($character_id)->version($active_version - 1)->first();
			if(isset($old_willpower)) {
				$new_willpower = $old_willpower->replicate();
				//Get the difference between the old value and the new value
				$willpower_difference = $character_data["willpower"]["dots"] - $old_willpower->willpower_total;
	
				//If the difference is positive, it is free for Storytellers.
				if($willpower_difference > 0) {
					if($user->isStoryteller()) {
						$new_willpower->amount_free += $willpower_difference;
					}
				} else if($willpower_difference < 0) {
					//Otherwise, we add the total to the amount lost
					if(!$user->isStoryteller()) $new_willpower->amount_lost += abs($willpower_difference);
				}
	
				//Delete any existing willpower records for this version
				CharacterWillpower::character($character_id)->version($active_version)->delete();
				$new_willpower->willpower_total = $character_data["willpower"]["dots"];
				$new_willpower->willpower_current = $character_data["willpower"]["traits"];
				$new_willpower->version_id = $active_version_id;
				$new_willpower->save();
			} else {
				$willpower = new CharacterWillpower;
				$willpower->character_id = $character_id;
				$willpower->willpower_total = $character_data["willpower"]["dots"];
				$willpower->willpower_current = $character_data["willpower"]["traits"];
				//Storytellers can add willpower for free.
				if($user->isStoryteller()) {
					$willpower->amount_free = max(0, $willpower->willpower_total - 4) * 3;
				}
				$willpower->version_id = $active_version_id;
				$willpower->save();
			}
			
			//Select the attributes for the previous verison
			$old_attributes = CharacterAttributes::character($character_id)->version($active_version - 1)->first();
			if(isset($old_attributes)) {
				$physicals_difference = $character_data["attributes"]["physicals"] - $old_attributes->physicals;
				$mentals_difference = $character_data["attributes"]["mentals"] - $old_attributes->mentals;
				$socials_difference = $character_data["attributes"]["socials"] - $old_attributes->socials;
				
				//If we have lost attributes, record the loss for experience calculation purposes.
				
				//First delete our old values
				CharacterAttributeLoss::character($character_id)->version($active_version)->delete();
				if($physicals_difference < 0) {
					for($i = $character_data["attributes"]["physicals"] + 1; $i <= $old_attributes->physicals; $i++) {
						$rankLost = new CharacterAttributeLoss;
						$rankLost->character_id = $character->id;
						$rankLost->rank_lost = $i;
						$rankLost->version_id = $active_version_id;
						$rankLost->save();
					}
				}
				if($mentals_difference < 0) {
					for($i = $character_data["attributes"]["mentals"] + 1; $i <= $old_attributes->mentals; $i++) {
						$rankLost = new CharacterAttributeLoss;
						$rankLost->character_id = $character->id;
						$rankLost->rank_lost = $i;
						$rankLost->version_id = $active_version_id;
						$rankLost->save();
					}
				}
				if($socials_difference < 0) {
					for($i = $character_data["attributes"]["socials"] + 1; $i <= $old_attributes->socials; $i++) {
						$rankLost = new CharacterAttributeLoss;
						$rankLost->character_id = $character->id;
						$rankLost->rank_lost = $i;
						$rankLost->version_id = $active_version_id;
						$rankLost->save();
					}				
				}
				 
				//Check to see if we've already set attributes for this version
				$new_attributes = $old_attributes->replicate();
				
				//Remove the current value for this version.
				CharacterAttributes::character($character_id)->version($active_version)->delete();
				
				$new_attributes->physicals = $character_data["attributes"]["physicals"];
				$new_attributes->mentals = $character_data["attributes"]["mentals"];
				$new_attributes->socials = $character_data["attributes"]["socials"];
				
				if($user->isStoryteller()) {
					if($physicals_difference > 0) {
						for($i = $old_attributes->physicals + 1; $i <= $character_data["attributes"]["physicals"]; $i++) {
							$new_attributes->free_points += max(1, $i - 9);
						}
					}
					if($mentals_difference > 0) {
						for($i = $old_attributes->mentals + 1; $i <= $character_data["attributes"]["mentals"]; $i++) {
							$new_attributes->free_points += max(1, $i - 9);
						}
					}
					if($socials_difference > 0) {
						for($i = $old_attributes->socials + 1; $i <= $character_data["attributes"]["socials"]; $i++) {
							$new_attributes->free_points += max(1, $i - 9);
						}
					}
				}
				$new_attributes->version_id = $active_version_id;
				$new_attributes->save();
			} else {
				$attributes = new CharacterAttributes;
				$attributes->character_id = $character_id;
				$attributes->physicals = $character_data["attributes"]["physicals"];
				$attributes->mentals = $character_data["attributes"]["mentals"];
				$attributes->socials = $character_data["attributes"]["socials"];
				$attributes->version_id = $active_version_id;
				$attributes->save();				
			}

			//Loop through all the abilities.
			
			//Delete all old abilities
			CharacterAbility::character($character_id)->version($active_version)->delete();
			$abilities = isset($character_data["abilities"]) ? $character_data["abilities"] : [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterAbility::character($character_id)->version($active_version - 1)->get() as $cd) {
				$found = false;
				foreach($abilities as $a) {
					if($a["id"] == $cd->ability_id) $found = true;
				}
				if(!$found) {
					$new_cd = $cd->replicate();
					//Burn points.
					$new_cd->lost_points += $new_cd->amount;
					$new_cd->amount = 0;
					$new_cd->version_id = $active_version_id;
					$new_cd->save();
				}
			}
			
			foreach($abilities as $a) {
				$old_ability = CharacterAbility::character($character_id)->where('ability_id', $a["id"])->version($active_version - 1)->first();
				if(isset($old_ability)) {
					$new_ability = $old_ability->replicate();
					$rank_difference = $a["count"] - $old_ability->amount;
					if($rank_difference < 0) {
						$new_ability->lost_points += abs($rank_difference);
					} else if($rank_difference > 0 && $user->isStoryteller()) {
						$new_ability->free_points += $rank_difference;
					}
					$new_ability->amount = $a["count"];
					if(isset($a["specialization"])) {
						$new_ability->specialization = $a["specialization"];
						if($user->isStoryteller()) $new_ability->free_points++;
					}
					$new_ability->version_id = $active_version_id;
					$new_ability->save();
				} else {
					//Custom abilities >>
					$checkAbility = RulebookAbility::find($a["id"]);
					if($checkAbility == null) {
						$checkAbility = new RulebookAbility;
						$checkAbility->name = $a["name"];
						$checkAbility->isCustom = true;
						$checkAbility->owner = $character_id;
						$checkAbility->save();
					}
					$ability = new CharacterAbility;
					$ability->character_id = $character_id;
					$ability->ability_id = $checkAbility->id;
					if(isset($a["specialization"])) $ability->specialization = $a["specialization"];
					$ability->amount = $a["count"];
					if($user->isStoryteller()) {
						$ability->free_points = $a["count"];
					}
					$ability->version_id = $active_version_id;
					$ability->save();
				}
			}
			

			//Remove all outdated disciplines for this version
			CharacterDiscipline::character($character_id)->version($active_version)->delete();
			
			$disciplines = isset($character_data["disciplines"]) ? $character_data["disciplines"] : [];
			
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterDiscipline::character($character_id)->version($active_version - 1)->get() as $cd) {
				$found = false;
				foreach($disciplines as $d) {
					if($d["id"] == $cd->discipline_id && $d["path"] == $cd->path_id) $found = true;
				}
				if(!$found) {
					$new_cd = $cd->replicate();
					//Burn points.
					$new_cd->lost_points += $character->getDisciplineCost($new_cd->definition, $new_cd->ranks, $active_version);
					$new_cd->ranks = 0;
					$new_cd->version_id = $active_version_id;
					$new_cd->save();
				}
			}
			
			foreach($disciplines as $d) {
				//Look for a discipline record from the previous version
				$old_discipline = CharacterDiscipline::character($character_id)->where(array('discipline_id' => $d["id"], 'path_id' => $d["path"]))->version($active_version - 1)->first();
				if(isset($old_discipline)) {
					$new_discipline = $old_discipline->replicate();
					$rank_difference = $d["count"] - $old_discipline->ranks;
					if($rank_difference < 0) {
						//We burn points if we've lost a discipline rank
						$new_discipline->lost_points += $character->getDisciplineCost($old_discipline->definition, $old_discipline->ranks, $active_version) - $character->getDisciplineCost($old_discipline->definition, $d["count"], $active_version);							
					} else if($user->isStoryteller()) {
						//We receive free points if we're a storyteller.
						$new_discipline->free_points += $character->getDisciplineCost($old_discipline->definition, $d["count"], $active_version) - $character->getDisciplineCost($old_discipline->definition, $old_discipline->ranks, $active_version);
					}
					$new_discipline->ranks = $d["count"];
					$new_discipline->version_id = $active_version_id;
					$new_discipline->save();
				} else {
					$discipline = new CharacterDiscipline;
					$discipline->character_id = $character_id;
					$discipline->discipline_id = $d["id"];
					$discipline->path_id = $d["path"];
					$discipline->ranks = $d["count"];
					if($user->isStoryteller()) {
						$discipline->free_points += $character->getDisciplineCost($discipline->definition, $d["count"], $active_version);
					}
					$discipline->version_id = $active_version_id;
					$discipline->save();

				}
			}
			

			//I don't think rituals can be lost.
			$newRituals = Input::get("newRituals");
			if($newRituals) {
				$ritualLookup = [];
				foreach($newRituals as $nr) {
					$customRitual = new RulebookRitual;
					$customRitual->name = $nr["name"];
					$customRitual->description = $nr["description"];
					$customRitual->group = $nr["type"];
					$customRitual->isCustom = true;
					$customRitual->owner = $character_id;
					$customRitual->save();	
					$ritualLookup[$nr["id"]] = $customRitual->id;	
				}
			}
			CharacterRitual::character($character_id)->version($active_version)->delete();

			if(isset($character_data["rituals"])) {
				$rituals = $character_data["rituals"];
				foreach($rituals as $r) {
					if($r < 0) $r = $ritualLookup[$r];
					$ritual = new CharacterRitual;
					$ritual->character_id = $character_id;
					$ritual->ritual_id = $r;
					$ritual->version_id = $active_version_id;
					$ritual->save();
				}
			}

			//Recreate the path for this version
			CharacterPath::character($character_id)->version($active_version)->delete();

			//Get the old values
			$old_path = CharacterPath::character($character_id)->version($active_version - 1)->first();
			if(isset($old_path)) {
				$new_path = $old_path->replicate();
				$virtue1_diff = $character_data["virtues"][0] - $old_path->virtue1;
				if($virtue1_diff < 0) {
					$new_path->lost_points += abs($virtue1_diff)*3;
				} else if($user->isStoryteller()) {
					$new_path->free_points += abs($virtue1_diff)*3;
				}

				$virtue2_diff = $character_data["virtues"][1] - $old_path->virtue2;
				if($virtue2_diff < 0) {
					$new_path->lost_points += abs($virtue2_diff)*3;
				} else if($user->isStoryteller()) {
					$new_path->free_points += abs($virtue2_diff)*3;
				}

				//Virtue 3 is morality and only costs 2 per
				$virtue3_diff = $character_data["virtues"][2] - $old_path->virtue3;
				if($virtue3_diff < 0) {
					$new_path->lost_points += abs($virtue3_diff)*2;
				} else if($user->isStoryteller()) {
					$new_path->free_points += abs($virtue3_diff)*2;
				}

				$virtue4_diff = $character_data["virtues"][3] - $old_path->virtue4;
				if($virtue4_diff < 0) {
					$new_path->lost_points += abs($virtue4_diff)*3;
				} else if($user->isStoryteller()) {
					$new_path->free_points += abs($virtue4_diff)*3;
				}

				$new_path->path_id = $character_data["path"];
				$new_path->virtue1 = $character_data["virtues"][0];
				$new_path->virtue2 = $character_data["virtues"][1];
				$new_path->virtue3 = $character_data["virtues"][2];
				$new_path->virtue4 = $character_data["virtues"][3];
				$new_path->version_id = $active_version_id;
				$new_path->save();
			} else {
				if($character_data["path"] != null) {
					$path = new CharacterPath;
					$path->character_id = $character_id;
					$path->path_id = $character_data["path"];
					$path->virtue1 = $character_data["virtues"][0];
					$path->virtue2 = $character_data["virtues"][1];
					$path->virtue3 = $character_data["virtues"][2];
					$path->virtue4 = $character_data["virtues"][3];
					$path->version_id = $active_version_id;
					$path->save();
				}
			}

			//Loop through all the derangements
			CharacterDerangement::character($character_id)->version($active_version)->delete();
			$derangements = isset($character_data["derangements"]) ? $character_data["derangements"] : [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterDerangement::character($character_id)->version($active_version - 1)->get() as $cd) {
				$found = false;
				foreach($derangements as $d) {
					if($d == $cd->derangement_id) $found = true;
				}
				if(!$found) {
					$new_cd = $cd->replicate();
					//Burn points and buy it off. STs can burn for free.
					if(!$cd->bought_off) {
						$new_cd->lost_points += $user->isStoryteller() ? 2 : 4;
					}
					$new_cd->bought_off = true;
					$new_cd->version_id = $active_version_id;
					$new_cd->save();
				}
			}
			if(isset($character_data["derangements"])) {
				$derangements = $character_data["derangements"];
				foreach($derangements as $dr) {
					$old_derangement = CharacterDerangement::character($character_id)->version($active_version - 1)->where('derangement_id', $dr)->first();
					if(isset($old_derangement)) {
						$new_derangement = $old_derangement->replicate();
						$new_derangement->version_id = $active_version_id;
						$new_derangement->save();
					} else {
						$derangement = new CharacterDerangement;
						$derangement->character_id = $character_id;
						$derangement->derangement_id = $dr;
						if($user->isStoryteller() || $active_version > 1) {
							//If a ST added this or it was added after character gen, do not award experience for it.
							$derangement->lost_points += 2;
						}
						$derangement->version_id = $active_version_id;
						$derangement->save();
					}
				}
			}

			//Loop through all the merits
			CharacterMerit::character($character_id)->version($active_version)->delete();
			$merits = isset($character_data["merits"]) ? $character_data["merits"]: [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterMerit::character($character_id)->version($active_version - 1)->get() as $cd) {
				$found = false;
				foreach($merits as $d) {
					if($d["id"] == $cd->merit_id) $found = true;
				}
				if(!$found) {
					$new_cd = $cd->replicate();
					//Burn points and remove it. Only STs can do this
					if($user->isStoryteller() || $cd->bought_off == false) {
						$new_cd->lost_points += $new_cd->definition->cost;
						$new_cd->bought_off = true;
					}
					$new_cd->version_id = $active_version_id;
					$new_cd->save();
				}
			}
			if(isset($character_data["merits"])) {
				$merits = $character_data["merits"];
				foreach($merits as $dr) {
					$old_merit = CharacterMerit::character($character_id)->version($active_version - 1)->where('merit_id', $dr["id"])->where('description', isset($dr['description']) && strlen($dr['description']) > 0 ? $dr['description'] : null)->first();
					if(isset($old_merit)) {
						$new_merit = $old_merit->replicate();
						$new_merit->version_id = $active_version_id;
						$new_merit->save(); 
					} else {
						$merit = new CharacterMerit;
						$merit->character_id = $character_id;
						$merit->merit_id = $dr["id"];
						//Storytellers can add for free
						if($user->isStoryteller()) {
							$merit->free_points += $merit->definition->cost;
						} else if($active_version > 1) {
							//If it was added after character gen, charge double.
							$merit->lost_points += $merit->definition->cost;
						}
						if(isset($dr["description"]) && strlen($dr["description"]) > 0) $merit->description = $dr["description"];
						$merit->version_id = $active_version_id;
						$merit->save();
					}
				}
			}

			//Loop through all the flaws
			CharacterFlaw::character($character_id)->version($active_version)->delete();
			$flaws = isset($character_data["flaws"]) ? $character_data["flaws"] : [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterFlaw::character($character_id)->version($active_version - 1)->get() as $cd) {
				$found = false;
				foreach($flaws as $d) {
					if($d["id"] == $cd->flaw_id) $found = true;
				}
				if(!$found) {
					$new_cd = $cd->replicate();
					//Burn points and buy it off. STs can burn for free.
					if(!$cd->bought_off) {
						$new_cd->lost_points += $user->isStoryteller() ? $new_cd->definition->cost : $new_cd->definition->cost * 2;
						$new_cd->bought_off = true;
					}
					$new_cd->version_id = $active_version_id;
					$new_cd->save();
				}
			}
			if(isset($character_data["flaws"])) {
				$flaws = $character_data["flaws"];
				foreach($flaws as $dr) {
					$old_flaw = CharacterFlaw::character($character_id)->version($active_version - 1)->where('flaw_id', $dr["id"])->where('description', isset($dr['description']) && strlen($dr['description']) > 0 ? $dr['description'] : null)->first();
					if(isset($old_flaw)) {
						$new_flaw = $old_flaw->replicate();
						$new_flaw->version_id = $active_version_id;
						$new_flaw->save();
					} else {
						$flaw = new CharacterFlaw;
						$flaw->character_id = $character_id;
						$flaw->flaw_id = $dr['id'];
						if($user->isStoryteller() || $active_version > 1) {
							//If a ST added this or it was added after character gen, do not award experience for it.
							$flaw->lost_points += $flaw->definition->cost;
						}
						if(isset($dr["description"]) && strlen($dr["description"]) > 0) $flaw->description = $dr["description"];
						$flaw->version_id = $active_version_id;
						$flaw->save();
					}
				}
			}

			//Backgrounds follow the same principle as abilities, except Generation.
			//Delete all old backgrounds
			CharacterBackground::character($character_id)->version($active_version)->delete();
			
			$backgrounds = isset($character_data["backgrounds"]) ? $character_data["backgrounds"] : [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterBackground::character($character_id)->version($active_version - 1)->get() as $cd) {
				$found = false;
				foreach($backgrounds as $b) {
					if($b["id"] == $cd->background_id && (isset($b["description"]) ? $b["description"] == $cd->description : true)) $found = true;
				}
				if(!$found) {
					$new_cd = $cd->replicate();
					//Burn points.
					$new_cd->lost_points += $new_cd->amount;
					$new_cd->amount = 0;
					$new_cd->version_id = $active_version_id;
					$new_cd->save();
				}
			}
			
			foreach($backgrounds as $b) {
				$old_background = CharacterBackground::character($character_id)->version($active_version - 1)->where(['background_id' => $b["id"], 
								  'description' => (isset($b['description']) && strlen($b['description']) > 0 ? $b['description'] : null)])->first();
				if(isset($old_background)) {
					$new_background = $old_background->replicate();
					$rank_difference = $b["count"] - $old_background->amount;
					if($rank_difference < 0) {
						if($old_background->definition->name == "Generation") {
							$generation_cost = [1, 2, 4, 8, 16];
							$new_background->lost_points += $generation_cost[$old_background->amount - 1] - $generation_cost[$b["count"] - 1];
						} else {
							$new_background->lost_points += abs($rank_difference);
						}
					} else if($rank_difference > 0 && $user->isStoryteller()) {
						if($old_background->definition->name == "Generation") {
							$generation_cost = [1, 2, 4, 8, 16];
							$new_background->free_points += $generation_cost[$b["count"] - 1] - $generation_cost[$old_background->amount - 1];
						} else {
							$new_background->free_points += $rank_difference;
						}
					}
					$new_background->amount = $b["count"];
					$new_background->version_id = $active_version_id;
					$new_background->save();
				} else {			
					$background = new CharacterBackground;
					$background->character_id = $character_id;
					$background->background_id = $b["id"];
					if(isset($b["description"]) && strlen($b["description"]) > 0) $background->description = $b["description"];
					$background->amount = $b["count"];
					if($user->isStoryteller()) {
						if($background->definition->name == "Generation") {
							$generation_cost = [1, 2, 4, 8, 16];
							$background->free_points += $generation_cost[$b["count"] = 1];
						} else {
							$background->free_points += $b["count"];
						}
					}
					$background->version_id = $active_version_id;
					$background->save();
				}
			}

			CharacterElderPower::character($character_id)->version($active_version)->delete();
			$elderPowers = isset($character_data["elderPowers"]) ? $character_data["elderPowers"] : [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterElderPower::character($character_id)->version($active_version - 1)->get() as $ep) {
				$found = false;
				foreach($elderPowers as $e) {
					if($e["id"] == $ep->elder_id) $found = true;
				}
				if(!$found) {
					$new_ep = $ep->replicate();
					$new_ep->removed = true;
					$new_ep->version_id = $active_version_id;
					$new_ep->save();
				} 
			}
			foreach($elderPowers as $ep) {
				$old_power = CharacterElderPower::character($character_id)->where('elder_id', $ep["id"])->version($active_version - 1)->first();
				if(isset($old_power)) {
					$new_power = $old_power->replicate();
					$new_power->version_id = $active_version_id;
					$new_power->save();
				} else {
					$elder = RulebookElderPower::firstOrCreate(['owner_id' => $character_id, 'discipline_id' => $ep['discipline'], 
																'name' => $ep['name'], 'description' => $ep['description']]);
					$power = new CharacterElderPower;
					$power->character_id = $character_id;
					$power->elder_id = $elder->id;
					if($user->isStoryteller()) $power->free_points = 12;
					$power->version_id = $active_version_id;
					$power->save();
				}	
			}

			CharacterComboDiscipline::character($character_id)->version($active_version)->delete();
			$comboDisciplines = isset($character_data["comboDisciplines"]) ? $character_data["comboDisciplines"] : [];
			//Duplicate the records that are now gone and burn their points for XP tracking purposes
			foreach(CharacterComboDiscipline::character($character_id)->version($active_version - 1)->get() as $ep) {
				$found = false;
				foreach($comboDisciplines as $e) {
					if($e["id"] == $ep->combo_id) $found = true;
				}
				if(!$found) {
					$new_ep = $ep->replicate();
					$new_ep->removed = true;
					$new_ep->version_id = $active_version_id;
					$new_ep->save();
				} 
			}
			foreach($comboDisciplines as $ep) {
				$old_power = CharacterComboDiscipline::character($character_id)->where('combo_id', $ep["id"])->version($active_version - 1)->first();
				if(!isset($old_power)) {
					$opt3 = strlen($ep['option3']) == 0 ? null : $ep['option3'];
					$disc = RulebookComboDiscipline::firstOrCreate(['owner_id' => $character_id, 'option1' => $ep['option1'], 'option2' => $ep['option2'], 'option3' => $opt3, 
																	 'name' => $ep['name'], 'description' => $ep['description']]);
					$power = new CharacterComboDiscipline;
					$power->character_id = $character_id;
					$power->combo_id = $disc->id;
					if($user->isStoryteller()) $power->free_points = $disc->cost($character_id);
					$power->version_id = $active_version_id;
					$power->save();
				}	
			}

			return $character;
	}


	public function saveStorytellerOptions($id) {
		$character = Character::find($id);
		foreach(RulebookStorytellerOption::all() as $definition) {
			$value = Input::get("storyteller-option-".$definition->id);
			if($definition->type == "checkbox") $value = ($value == "on" ? 1 : 0);
			if(isset($value)) {
				$setting = CharacterStorytellerOption::firstOrNew(['character_id' => $character->id, 'option_id' => $definition->id]);
				$setting->character_id = $character->id;
				$setting->value = $value;
				$setting->save();
			}
		}
		return Redirect::to("/generator/$id");
	}
	

}
