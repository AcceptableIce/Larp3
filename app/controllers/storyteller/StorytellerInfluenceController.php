<?php
class StorytellerInfluenceController extends BaseController {
	public function addInfluenceField() {
		$background_id = Input::get("background");
		if($background_id) {
			$cap = new InfluenceCap;
			$cap->background_id = $background_id;
			$cap->capacity = 1;
			$cap->delta = '';
			$cap->save();
			return Redirect::to('/dashboard/storyteller/influence/caps');
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);					
		}
	}
	
	public function updateInfluenceFields() {
		$influences = Input::get("influences");
		$capacities = Input::get("capacities");
		$deltas = Input::get("deltas");
		if($influences && $capacities && $deltas) {
			foreach($influences as $index => $value) {
				$cap = InfluenceCap::find($value);
				$cap->capacity = $capacities[$index];
				$cap->delta = $deltas[$index];
				$cap->save();
			}
			return Redirect::to('/dashboard/storyteller/influence/caps');
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);								
		}
	}
	
	public function removeInfluenceField() {
		$id = Input::get("delete_id");
		InfluenceCap::find($id)->delete();
		return Redirect::to('/dashboard/storyteller/influence/caps');
	}
}