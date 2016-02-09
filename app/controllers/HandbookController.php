<?php
class HandbookController extends BaseController {
	
	public function save() {
		$title = Input::get("title");
		$body = Input::get("body");
		$mode = Input::get("mode");
		$user = Auth::user();
		$page = HandbookPage::where('title', $title)->first();
		if($mode == 1) {
			//Editing
			if($page) {
				if(	$page->userCanRead($user) && $page->userCanWrite($user)) {
					$page->body = $body;
					$page->read_permission = $this->createPermissionObject($page->id, 'read', $page->read_permission)->id;
					$page->write_permission = $this->createPermissionObject($page->id, 'write', $page->write_permission)->id;
					$page->updated_by = $user->id;
					$page->save();
					return Redirect::to('/handbook/'.HandbookPage::getURLReadyLink($title));
				} else {
					return Response::json(["success" => false, "message" => "Insufficient permissions."]);			
				}
			} else {
				return Response::json(["success" => false, "message" => "Page not found"]);			
			}
		} else if(!$page) {
			$page = new HandbookPage;
			$page->title = $title;
			$page->body = $body;
			$page->created_by = $user->id;
			$page->save();

			$page->read_permission = $this->createPermissionObject($page->id, 'read')->id;
			$page->write_permission = $this->createPermissionObject($page->id, 'write')->id;
			$page->save();
			return Redirect::to('/handbook/'.HandbookPage::getURLReadyLink($title));
		} else {
			return Redirect::to('/handbook/'.HandbookPage::getURLReadyLink($title));
		}
	}

	public function createPermissionObject($page_id, $type, $existing_permission_id = null) {
		$user_permission = Input::get("$type-user-permission");
		$sect_permission = Input::get("$type-sect-permission");
		$clan_permission = Input::get("$type-clan-permission");
		$background_permission = Input::get("$type-background-permission");

		$permission = $existing_permission_id == null ? new HandbookPagePermission : HandbookPagePermission::find($existing_permission_id);

		$permission->page_id = $page_id;
		$permission->permission_id = $user_permission != 0 ? $user_permission : null;
		$permission->sect_id = $sect_permission != 0 ? $sect_permission : null;
		$permission->clan_id = $clan_permission != 0 ? $clan_permission : null;
		$permission->background_id = $background_permission != 0 ? $background_permission : null;

		$permission->save();
		return $permission;
	}

	public function deletePage($id) {
		$page = HandbookPage::find($id);
		if($page) {
			$url = HandbookPage::getURLReadyLink($page->title);
			$page->delete();
			return Redirect::to("/handbook/$url");
		} else {
			return 'No page found.';
		}
	}
}
?>