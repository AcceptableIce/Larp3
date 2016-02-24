<?php
class StorytellerPositionController extends BaseController {
	public function createPosition() {
		$name = Input::get("name");
		if($name) {
			$position = new RulebookPosition;
			$position->name = $name;
			$position->save();
			return Redirect::to('/dashboard/storyteller/manage/positions');
		} else {
			return "Invalid name.";
		}
	}	

	public function deletePosition() {
		$id = Input::get("id");
		RulebookPosition::find($id)->delete();
		return Redirect::to('/dashboard/storyteller/manage/positions');
	}
	
	public function grantCharacterPosition(Character $character) {
		$position = RulebookPosition::find(Input::get("position"));
		if($position != null &&) {
			if(!CharacterPosition::where(['character_id' => $character->id, 'position_id' => $position->id])->exists()) {
				$newPosition = new CharacterPosition;
				$newPosition->character_id = $character->id;
				$newPosition->position_id = $position->id;
				$newPosition->save();
				return Redirect::to("dashboard/storyteller/character/$character->id/positions");
			} else {
				return Response::json(['success' => false, 'message' => 'Character already has this position.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);					
		}
	}

	public function removeCharacterPosition(Character $character) {
		$position = RulebookPosition::find(Input::get("position"));
		if($position != null) {
			CharacterPosition::where(['character_id' => $character->id, 'position_id' => $position->id])->delete();
			return Redirect::to("dashboard/storyteller/character/$character->id/positions");	
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);					
		}
	}
}