<?php
class SaveController extends BaseController {

	public function saveCharacter() {		
		$user = Auth::user();	
		
		if($user) {
			// Make sure we have a name.
			if(strlen(trim(Input::get("sheet.name"))) == 0) {
				return Response::json(["success" => false, "mode" => 2, "message" => "Characters must have a name."]);
			}
			
			$character = Character::find(Input::get('characterId'));
					
			if($character) {
				if(Character::where('id', Input::get('characterId'))->first()->user_id != $user->id && !$user->isStoryteller()) {
					return Response::json(array("success" => false, "mode" => 0, "message" => "Unauthorized"));
				}
			}
			
			try {
				$version = $this->save();
				$character = $version->character;
				if(Input::get("review") && !($character->getOptionValue("Ignore Validation") || $user->isStoryteller())) {
					$character->verify($character->approved_version + 1, $character->approved_version == 0);
				}
			} catch (Exception $e) {
				$version->rollback();
				if(get_class($e) == "CharacterValidationException") {
					return Response::json(array("success" => false, "mode" => 2, "message" => $e->getMessage()));
				}
				return Response::json(array("success" => false, "mode" => 0, "message" => $e->getMessage()."[".$e->getFile()."$".$e->getLine()."]"));
			}
			
			$version->commit();
			
			if(Input::get("review")) {
				if($user->isStoryteller()) {
					$character->approved_version = $version->version;
					$character->in_review = false;
					if(!$character->is_active) $character->approved_at = new DateTime;
					$character->save();	
				} else {
					//41 = Character Changes
					$topic = (($character->approved_version == 0) ? "Character Changes to " : "New Character ")."\"".$character->name."\" from ".$user->username;
					$versionNumber = $character->latestVersion()->version;
					Forum::find(41)->post($topic, "[[change/$character->id/$versionNumber]]");
				}
			}
			
			return Response::json([	
				"success" => true, 
				"mode" => 0, 
				"message" => (Input::get("review") ? "In review queue." : "Saved successfully.")
			]);
		} else {
			return Response::json(array("success" => false, "message" => "Unable to validate user."));
		}
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

	public function resetCurrentChanges(Character $character) {
		CharacterVersion::where('character_id', $character->id)->where('version', '>', $character->activeVersion())->delete();
		return Redirect::to('/generator/'.$character->id);	
	}
	
	public function saveBiography($character) {
		$question_ids = Input::get('ids');
		$replies = Input::get('replies');
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
				$post = ForumPost::where('body', "[[questionnaire/$character->id]]")->first();
				if($post) {
					$topic = $post->topic;
					$topic->postReply($character->owner->id, "Questionnaire responses updated. (Automatic system post)");
				} else {
					//42 = Character Backgrounds
					$topic = Forum::find(42)->post("Character Biography for ".$character->name, "[[questionnaire/$character->id]]");
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
		if(Auth::check()) {
			$user = Auth::user();		
			try {
				$version = $this->save();
			} catch (Exception $e) {
				$version->rollback();
				return Response::json(array("success" => false, "message" => $e->getMessage()."[".$e->getFile()."$".$e->getLine()."]"));
			}
			$xpc = $version->character->getExperienceCost($version->version);
			$version->rollback();
			return Response::json(array("success" => true, "cost" => $xpc));
		} else {
			return Response::json(array("success" => false, "message" => "Unable to validate user."));
		}
	}
	
	public function save() {
		$user = Auth::user();
		$character = Character::firstOrNew(['id' => Input::get('characterId')]);
		if(!isset($character->user_id)) {
			$character->user_id = $user->id;
		}
		$character->name = Input::get("sheet.name");
		$character->in_review = (Input::get("review") == 1);
		$character->save();
		
		CharacterVersion::where('character_id', $character->id)->where('version', '>', $character->activeVersion())->delete();
		
		$version = CharacterVersion::createNewVersion($character);
		
		if($version->isNewCharacter()) {
			$version->setHasDroppedMorality(Input::get("sheet.hasDroppedMorality") == "true");
		}
		try {
			$version->setEditingUser($user);
			
			if(Input::get("sheet.sect")) {
				$version->setSect(
					RulebookSect::find(Input::get("sheet.sect.selected")), 
					RulebookSect::find(Input::get("sheet.sect.displaying"))
				);
			}
			
			if(Input::get("sheet.clan")) {
				$version->setClan(
					RulebookClan::find(Input::get("sheet.clan.selected")),
					RulebookClan::find(Input::get("sheet.clan.displaying"))
				);
			}
			
			if(Input::get("sheet.clanOptions")) {
				$version->setClanOptions(
					Input::get("sheet.clanOptions.0"),
					Input::get("sheet.clanOptions.1"),
					Input::get("sheet.clanOptions.2")
				);
			}
			
			if(Input::get("sheet.nature")) {
				$version->setNature(
					RulebookNature::find(Input::get("sheet.nature"))
				);
			}
			
			if(Input::get("sheet.willpower")) {
				$version->setWillpower(
					Input::get("sheet.willpower.dots"),
					Input::get("sheet.willpower.traits")
				);
			}
			
			if(Input::get("sheet.attributes")) {
				$version->setAttributes(
					Input::get("sheet.attributes.physicals"),
					Input::get("sheet.attributes.mentals"),
					Input::get("sheet.attributes.socials")
				);
			}
			
			foreach((array) Input::get("sheet.abilities") as $ability) {
				if(array_key_exists("specialization", $ability)) {
					$version->addAbilityWithSpecialization(RulebookAbility::find($ability["id"]), $ability["count"], $ability["specialization"], $ability["name"]);
				} else {
					$version->addAbility(RulebookAbility::find($ability["id"]), $ability["count"], $ability["name"]);
				}
			}
			
			foreach((array) Input::get("sheet.disciplines") as $discipline) {
				$version->updateDiscipline(RulebookDiscipline::find($discipline["id"]), $discipline["count"],
					array_key_exists("path", $discipline) ? $discipline["path"] : null);
			}
						
			foreach((array) Input::get("newRituals") as $newRitualData) {
				$version->addRitualToBook($newRitualData["name"], $newRitualData["description"], $newRitualData["type"]);
			}
			
			foreach((array) Input::get("sheet.rituals") as $ritualId) {
				$version->addRitual($ritualId);
			}
			if(Input::get("sheet.path")) {
				$version->updatePath(
					RulebookPath::find(Input::get("sheet.path")),
					Input::get("sheet.virtues.0"),
					Input::get("sheet.virtues.1"),
					Input::get("sheet.virtues.2"),
					Input::get("sheet.virtues.3")			
				);
			}
			
			foreach((array) Input::get("sheet.merits") as $meritData) {
				$version->addMerit(RulebookMerit::find($meritData["id"]),
					array_key_exists("description", $meritData) ? $meritData["description"] : null);
			}		
			
			foreach((array) Input::get("sheet.flaws") as $flawData) {
				$version->addFlaw(RulebookFlaw::find($flawData["id"]),
					array_key_exists("description", $flawData) ? $flawData["description"] : null);
			}
			
			foreach((array) Input::get("sheet.derangements") as $derangementData) {
				$version->addDerangement(RulebookDerangement::find($derangementData["id"]), 
					array_key_exists("description", $derangementData) ? $derangementData["description"] : null);
			}
			
			foreach((array) Input::get("sheet.backgrounds") as $backgroundData) {
				$version->addBackground(RulebookBackground::find($backgroundData["id"]), $backgroundData["count"],
					array_key_exists("description", $backgroundData) ? $backgroundData["description"] : null);
			}
			
			foreach((array) Input::get("sheet.elderPowers") as $elderData) {
				$version->addElderPower($elderData);
			}
		
			foreach((array) Input::get("sheet.comboDisciplines") as $comboData) {
				$version->addComboDiscipline($comboData);
			}
			
			$version->clearUntouchedRecords();
		} catch (Exception $e) {
			echo $e;
		}
			
		return $version;
	}

	public function saveStorytellerOptions(Character $character) {
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
		return Redirect::to("/generator/$character->id");
	}
	

}
