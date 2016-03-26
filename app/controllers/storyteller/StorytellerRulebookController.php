<?php
class StorytellerRulebookController extends BaseController {
		public function saveRulebookItem($key, $flaw_id) {
		$class = Helpers::$rulebook_items[$key];
		$object = $class::find($flaw_id);
		if(!$object) {
			$object = new $class();
		}
		
		foreach($object->getFillable() as $field) {
			$value = Input::get($field);

			if($value == "on") $value = 1;
			if($value == null) $value = 0;
			
			$object->$field = $value;
		}
		$object->save();
		return Redirect::to('/dashboard/storyteller/rulebook/'.$key);
	}

	public function deleteRulebookItem($key, $id) {
		$class = Helpers::$rulebook_items[$key];
		$object = $class::find($id);
		if($object) {
			$object->delete();
			return Redirect::to('/dashboard/storyteller/rulebook/'.$key);
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid id.']);					
		}
	}
}