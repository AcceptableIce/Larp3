<?php
class StorytellerApplicationController extends BaseController {
		public function saveApplicationSettings() {
		foreach(ApplicationSetting::all() as $definition) {
			$value = Input::get("application-setting-".$definition->id);
			if($definition->type == "checkbox") $value = ($value == "on" ? 1 : 0);
			if(isset($value)) {
				$definition->value = $value;
				$definition->save();
			}
		}
		return Redirect::to("/dashboard/storyteller/settings/application");
	}

	public function saveCheatSheet() {
		$data = [];
		$data["showClan"] = Input::get("clan");
		$data["showGeneration"] = Input::get("generation");
		$data["showVentrueRestriction"] = Input::get("ventrue-restriction");
		$data["showPath"] = Input::get("path");		
		$data["merits"] = [];
		$merits_enabled = Input::get("merits-enabled");

		foreach(Input::get("merits-ids") as $index => $merit_id) {
			$data["merits"][] = ["id" => $merit_id, 
								 "display" => Input::get("merits-enabled-".$merit_id) ? "on" : "off", 
								 "highlight" => Input::get("merits-highlights-".$merit_id)];
		}
		foreach(Input::get("flaws-ids") as $index => $flaw_id) {
			$data["flaws"][] = ["id" => $flaw_id, 
								"display" => Input::get("flaws-enabled-".$flaw_id) ? "on" : "off", 
			 					"highlight" => Input::get("flaws-highlights-".$flaw_id)];
		}
		foreach(Input::get("derangements-ids") as $index => $derangement_id) {
			$data["derangements"][] = [	"id" => $derangement_id, 
										"display" => Input::get("derangements-enabled-".$derangement_id) ? "on" : "off", 
										"highlight" => Input::get("derangements-highlights-".$derangement_id)];
		}
		File::put(app_path()."/config/cheatSheet.json", json_encode($data));
		return Redirect::to("/dashboard/storyteller/manage/cheatsheet");
	}

	public function uploadFile() {
		$name = Input::get('name');
		$permission = Input::get('permisson');
		$id = Input::get("file_id");
		if($id) {
			$record = FileUpload::find($id);
			if($name && $record) {
				$record->name = $name;
				$record->read_permission = $permission != -1 ? $permission : null;
				if(Input::hasFile('fileUpload')) {
					$file = Input::file('fileUpload');
					$fileName = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $name);
					$fileName = preg_replace("([\.]{2,})", '', $fileName);
					$fileName .= ".".$file->getClientOriginalExtension();
					$file->move(app_path()."/uploads", $fileName);
					$record->url = $fileName;
				}
				$record->save();
				return Redirect::to("/dashboard/storyteller/manage/files");
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);								
			}
		} else {
			if($name && Input::hasFile('fileUpload')) {
				$file = Input::file('fileUpload');
				$newFile = new FileUpload;
				$newFile->name = $name;
				$newFile->read_permission = $permission != -1 ? $permission : null;
				$fileName = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $name);
				$fileName = preg_replace("([\.]{2,})", '', $fileName);
				$fileName .= ".".$file->getClientOriginalExtension();
				$file->move(app_path()."/uploads", $fileName);
				$newFile->url = $fileName;
				$newFile->created_by = Auth::user()->id;
				$newFile->save();
				return Redirect::to("/dashboard/storyteller/manage/files");
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);								
			}
		}
	}

	public function deleteFile($id) {
		FileUpload::find($id)->delete();
		return Redirect::to("/dashboard/storyteller/manage/files");
	}
}