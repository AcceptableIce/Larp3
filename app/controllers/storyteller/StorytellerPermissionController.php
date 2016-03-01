<?php
class StorytellerPermissionController extends BaseController {
	public function createPermission() {
		$name = Input::get("name");
		$description = Input::get("description");
		if($name && $description) {
			$permission = new PermissionDefinition;
			$permission->name = $name;
			$permission->definition = $description;
			$permission->save();
			return Redirect::to('/dashboard/storyteller/manage/permissions');
		} else {
			return "Invalid data.";
		}
	}	

	public function deletePermission() {
		$id = Input::get("definition");
		PermissionDefinition::find($id)->delete();
		return Redirect::to('/dashboard/storyteller/manage/permissions');
	}
	
	public function grantPermission() {
		$user_id = Input::get("user");
		$permission_id = Input::get("permission");
		$user = User::find($user_id);
		$permission = PermissionDefinition::find($permission_id);
		if($user) {
			if($permission) {
				if(!Permission::where(['permission_id' => $permission_id, 'user_id' => $user_id])->exists()) {
					$new_permission = new Permission;
					$new_permission->permission_id = $permission_id;
					$new_permission->user_id = $user_id;
					$new_permission->save();
				}
				Cache::forget('user-storyteller-'.$user_id);
				return Redirect::to('/dashboard/storyteller/manage/permissions');
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid permission definition.']);					
			}
		} else{
			return Response::json(['success' => false, 'message' => 'Invalid user.']);					

		}
	}
	
	public function removePermission() {
		$user_id = Input::get("user");
		$permission_id = Input::get("permission");
		$user = User::find($user_id);
		$permission = PermissionDefinition::find($permission_id);
		if($user) {
			if($permission) {
				Permission::where(['permission_id' => $permission_id, 'user_id' => $user_id])->delete();
				return Redirect::to('/dashboard/storyteller/manage/permissions');
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid permission definition.']);					
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid user.']);					
		}
	}
}