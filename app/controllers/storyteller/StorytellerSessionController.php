<?php
class StorytellerSessionController extends BaseController {
	public function createSession() {
		$date = Input::get("date");
		if($date) {
			$session = new GameSession;
			$session->date = DateTime::createFromFormat('m/d/Y', $date);
			$session->save();
			return Redirect::to('/dashboard/storyteller/manage/sessions');
		} else {
			return "Invalid date.";
		}
	}	

	public function deleteSession() {
		$id = Input::get("id");
		GameSession::find($id)->delete();
		return Redirect::to('/dashboard/storyteller/manage/sessions');
	}	

	public function checkInCharacter(GameSession $session) {
		$character = Character::find(Input::get("id"));
		if($character) {
			if(!GameSessionCheckIn::where(['session_id' => $session->id, 'character_id' => $character->id])->exists()) {
				$checkin = new GameSessionCheckIn;
				$checkin->session_id = $session->id;
				$checkin->character_id = $character->id;
				$checkin->costume = Input::get("costume") == "true" ? 1 : 0;
				$checkin->save();
				$willpower = $character->willpower()->first();
				$willpower->willpower_current += 2;
				if($willpower->willpower_current > $willpower->willpower_total) {
					$willpower->willpower_current = $willpower->willpower_total;
				}
				$willpower->save();
				
				return Response::json(['success' => true, 'message' => $character->name. " checked in!"]);
			} else {
				return Response::json(['success' => false, 'message' => 'Character already checked in.']);	
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);
		}
	}

	public function awardExperience(Session $session) {
		$ids = Input::get("ids");
		$costumes = Input::get("costumes");
		$nom1s = Input::get("nom1s");
		$nom2s = Input::get("nom2s");
		$overrides = Input::get("overrides");
		$save = Input::get("save");
		$missingCharacters = [];
		foreach($ids as $index => $id) {
			$character = Character::find($id);
			if($character) {
				$checkIn = GameSessionCheckIn::where(['session_id' => $session->id, 'character_id' => $character->id])->first();
				$checkIn->costume = $costumes[$index] == "true" ? 1 : 0;
				$checkIn->nominated = $nom1s[$index] == "true"  ? 1 : 0;
				$checkIn->nominated_twice = $nom2s[$index] == "true"  ? 1 : 0;
				$checkIn->bonus = $overrides[$index];
				$checkIn->total_experience = 1 + ($checkIn->costume ? 1 : 0) + ($checkIn->nominated ? 1 : 0) + 
											 ($checkIn->nominated_twice ? 1 : 0) + $checkIn->bonus;
				$checkIn->save();
				if(!isset($save)) {
					$owner = $character->owner;
					$owner->sendMessage(null, 
						"Experience awarded to ".$character->name, 
						"The Storytellers have awarded your character ".$checkIn->total_experience.
						" Experience. You can now use the character editor to make changes to it at".
						" your leisure. Remember, changes should be submitted by 6:00pm on the Wednesday".
						" before a game to ensure that they have the chance to review them."
					);
					$character->awardExperience($checkIn->total_experience);
					$character->save();
				}
			} else {
				$missingCharacters[] = $id;
			}
		}
		if(!isset($save)) {
			$session->submitted = true;
			$session->save();
			foreach(User::listStorytellers() as $st) {
				$st->sendMessage(null, 
					"Experience successfully awarded", 
					"The action to award experience for the session on ".$session->date." has completed.".
					(sizeof($missingCharacters) > 0 ? "\n\n".sizeof($missingCharacters)." characters were".
					" not found (".implode(",", $missingCharacters).")" : ''));
			}
			return Redirect::to("/dashboard");
		} else {
			return Redirect::to("/dashboard/storyteller/session/experience/$session->id");
		}
	}
}