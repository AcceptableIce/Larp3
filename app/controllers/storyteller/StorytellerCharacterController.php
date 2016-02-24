<?php
class StorytellerCharacterController extends BaseController {
public function acceptChanges(Character $character) {
		if($character->in_review) {
			$character->approved_version = $character->latestVersion()->version;
			$character->in_review = false;
			//Inactivate all other characters this player owns.
			if(!$character->owner->isStoryteller()) {
				$was_active = $character->active;
				foreach(Character::where('user_id', $character->owner->id)->get() as $c) {
					$c->active = false;
					$c->save();
				}
				$character->active = true;
				if(!$was_active) {
					//This is a new character
					$character->owner->sendMessage(null, 
						"New Character Accepted", 
						"The Storytellers have accepted your character \"".$character->name."\".".
						" Any of your characters that were previously active have been inactivated.".
						" You have been granted access to your new clan's forum, and access to your old".
						" clan forum has been revoked. If you have any questions, please post in the ".
						" \"General Messages\" forum."
					);
					$character->approved_at = new DateTime;
					$xp_cost = $character->getExperienceCost(1);
					$character->experience -= 10 - $xp_cost;
					Cache::forget("character-experience-$character->id");
					$character->save();
				} else {
					$character->owner->sendMessage(null, 
						"Changes to ".$character->name." Accepted", 
						"The Storytellers have accepted your changes to \"".$character->name."\".".
						" You can now access your character and make further changes as necessary."
					);					
				}
			}
			$character->save();
		}
		Cache::forget('forum-listing-'.$character->owner->id);
		return Redirect::to($_SERVER['HTTP_REFERER']);
	}

	public function rejectChanges(Character $character) {
		if($character->in_review) {
			$character->in_review = false;
			$character->save();
			$character->revertChanges($character->activeVersion());
			if(!$character->active) {
				//This is a new character
				$character->owner->sendMessage(null, 
					"New Character Rejected", 
					"The Storytellers have rejected your character \"".$character->name."\".".
					" You can now access your character and make further changes as necessary.".
					" If you have any questions, please post in the \"General Messages\" forum."
				);
			} else {
				$character->owner->sendMessage(null, 
					"Changes to ".$character->name." Rejected", 
					"The Storytellers have rejected your changes to \"".$character->name."\".".
					" You can now access your character and make further changes as necessary.".
					" If you have any questions, please post in the \"General Messages\" forum."
				);		
			}
		}
		return Redirect::to($_SERVER['HTTP_REFERER']);
	}
	
	public function setCharacterTimeoutDate(Character $character) {
		$timeout = Input::get("date");
		if($timeout) {
			$character->time_out = DateTime::createFromFormat('m/d/Y', $timeout);
			$character->save();
			return Redirect::to('/dashboard/storyteller/characters');
		} else {
			return 'Invalid data.';
		}
	}
	
	public function awardBiographyExperience() {
		$character = Character::find(Input::get('id'));
		if($character) {
			$type = Input::get('type');
			if($type == "questionnaire") {
				$characterExperienceRow = CharacterBiographyExperience::firstOrNew(['character_id' => $character->id]);
				if(!$characterExperienceRow->questionnaire_xp) {
					$characterExperienceRow->questionnaire_xp = true;
					$characterExperienceRow->save();
					$character->awardExperience(1);
					$character->save();
					$character->owner->sendMessage(null, 	
						"Questionnaire Experience Awarded", 
						"The Storytellers have awarded your character ".$character->name.
						" 1 Experience for answering the character questionnaire.\n\nThanks".
						" for fleshing out your character. Please watch the thread that has been".
						" started in the Character Backgrounds forum for replies from the Storytellers;".
						" if they have any questions or concerns on your biography, they will let you".
						" know there.\n\nThanks,\nThe Storytellers"
					);
					return Redirect::to('/dashboard/storyteller/experience/biographies');
				} else {
					return Response::json(['success' => false, 'message' => 'Questionnaire experience has already been awarded.']);							
				}
			} else if ($type == "backstory") {
				$characterExperienceRow = CharacterBiographyExperience::firstOrNew(['character_id' => $character->id]);
				if(!$characterExperienceRow->backstory_xp) {
					$characterExperienceRow->backstory_xp = true;
					$characterExperienceRow->save();
					$character->awardExperience(1);
					$character->save();
					$character->owner->sendMessage(null, 	
						"Backstory Experience Awarded", 
						"The Storytellers have awarded your character ".$character->name.
						" 1 Experience for providing a character backstory.\n\nThanks for".
						" fleshing out your character. Please watch the thread that has been".
						" started in the Character Backgrounds forum for replies from the Storytellers;".
						" if they have any questions or concerns on your biography, they will let you".
						" know there.\n\nThanks,\nThe Storytellers"
					);
					return Redirect::to('/dashboard/storyteller/experience/biographies');
				} else {
					return Response::json(['success' => false, 'message' => 'Questionnaire experience has already been awarded.']);							
				}
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid type.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function awardJournalExperience() {
		$character = Character::find(Input::get('id'));
		if($character) {
			$date = new DateTime;
			$date->setTimestamp(intval(Input::get('month')));
			//Check to make sure we haven't already awarded XP for this month.
			if(!CharacterJournalExperience::where('character_id', $character->id)
			->whereRaw('MONTH(date) = ?', [date('m', $date->getTimestamp())])
			->whereRaw('YEAR(date) = ?', [date('Y', $date->getTimestamp())])
			->exists()) {
				$journal = new CharacterJournalExperience;
				$journal->character_id = $character->id;
				$journal->date = $date;
				$journal->save();
				$character->awardExperience(1);
				$character->save();
				$character->owner->sendMessage(null, 
					"Journal Experience awarded for ".$date->format('F Y'), 
					"The Storytellers have awareded your character ".$character->name. 
					" 1 Experience for your ".$date->format('F Y')." journal.\n\n If you have any".
					" questions, please post in the \"General Messages\" forum.\n\nThanks,\nThe Storytellers"
				);
				return Redirect::to("/dashboard/storyteller/experience/journal");
			} else {
				return Response::json(['success' => false, 'message' => 'Experience already awarded for this month']);
			}

		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function awardDiablerieExperience() {
		//This is functionally the same as the journal XP, except it grants 2 and sends a different message.
		$character = Character::find(Input::get('id'));
		if($character) {
			$date = new DateTime;
			$date->setTimestamp(intval(Input::get('month')));
			//Check to make sure we haven't already awarded XP for this month.
			if(!CharacterDiablerieExperience::where('character_id', $character->id)
			->whereRaw('MONTH(date) = ?', [date('m', $date->getTimestamp())])
			->whereRaw('YEAR(date) = ?', [date('Y', $date->getTimestamp())])
			->exists()) {
				$journal = new CharacterDiablerieExperience;
				$journal->character_id = $character->id;
				$journal->date = $date;
				$journal->save();
				$character->awardExperience(2);
				$character->save();
				$character->owner->sendMessage(null, 
					"Diablerie Experience awarded for ".$date->format('F Y'), 
					"The Storytellers have awareded your character ".$character->name. 
					" 2 Experience for diablerizing in ".$date->format('F Y').".\n\n If you have any".
					" questions, please post in the \"General Messages\" forum.\n\nThanks,\nThe Storytellers"
				);
				return Redirect::to("/dashboard/storyteller/experience/diablerie");
			} else {
				return Response::json(['success' => false, 'message' => 'Experience already awarded for this month']);
			}

		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function awardCharacterExperience() {
		$character = Character::find(Input::get('id'));
		if($character) {
			$amount = Input::get("amount");
			$character->awardExperience($amount);
			$message = Input::get("message");
			$character->save();			
			$character->owner->sendMessage(Auth::user()->id, 
				"Experience awarded to ".$character->name, $character->name.
				" has been awarded ".$amount. " Experience.".($message ? "<br><br>".$message : "")
			);
			return Redirect::to('dashboard/storyteller/characters');
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function toggleNPCStatus(Character $character) {
		$character->is_npc = $character->is_npc ? '0' : '1';
		$character->save();
		return Redirect::to('dashboard/storyteller/characters');

	}

	public function toggleActiveStatus(Character $character) {
		$character->active = $character->active ? '0' : '1';
		$character->save();
		return Redirect::to('dashboard/storyteller/characters');
	}
	
	public function transferExperience(Character $to) {
		$from = Character::find(Input::get("from"));
		if($from != null && $to != null) {
			$to->experience += $from->availableExperience();
			$from->experience -= $from->availableExperience();
			$to->save();
			$from->save();
			Cache::forget("character-experience-".$to->id);
			Cache::forget("character-experience-".$from->id);				
			return Redirect::to("dashboard/storyteller/characters");
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);					
		}
	}
}