<?php
class StorytellerForumController extends BaseController {
	public function saveForum(Forum $forum = null) {
		if($forum == null) $forum = new Forum;
		$forum->name = Input::get("name");
		$forum->description = Input::get("description");
		$forum->category_id = Input::get("category");				
		$forum->sect_id = Input::get("sect") == "NULL" ? null : Input::get("sect");
		$forum->clan_id = Input::get("clan") == "NULL" ? null : Input::get("clan");
		$forum->background_id = Input::get("background") == "NULL" ? null : Input::get("background");
		$forum->read_permission = Input::get("read-permission") == "NULL" ? null : Input::get("read-permission");
		$forum->topic_permission = Input::get("topic-permission") == "NULL" ? null : Input::get("topic-permission");			
		$forum->reply_permission = Input::get("reply-permission") == "NULL" ? null : Input::get("reply-permission");						
		$forum->is_private = Input::get("private") ? 1 : 0;
		$forum->show_on_st_todo_list = Input::get("todo_list") ? 1 : 0;
		$forum->asymmetric_replies = Input::get("asymmetric") ? 1 : 0;
		$forum->time_limited = Input::get("time-limited") ? 1 : 0;
		$forum->player_specific_threads = Input::get("player-specific-threads") ? 1 : 0;
		$forum->position = Input::get("position");
		$forum->list_header = trim(Input::get("list-header"));
		$forum->post_header = trim(Input::get("post-header"));	
		$forum->thread_template = trim(Input::get("thread-template"));				
		$forum->save();
		Cache::flush(); 
		return Redirect::to('dashboard/storyteller/manage/forums');
	}
	
	public function deleteForum(Forum $forum) {
		$forum->delete();
		Cache::flush(); 
		return Redirect::to('dashboard/storyteller/manage/forums');
	}
	public function restoreForum($id) {
		$forum = Forum::withTrashed()->find($id);
		if($forum) {
			$forum->restore();
			Cache::flush(); 
			return Redirect::to('dashboard/storyteller/manage/forums');
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find forum.']);		
		}
	}

	public function grantCharacterForumPermission(Forum $forum) {
		$character = Character::find(Input::get('character'));
		if($character != null) {
			if(!ForumCharacterPermission::where(['character_id' => $character->id, 'forum_id' => $forum->id])->exists()) {
				$permission = new ForumCharacterPermission;
				$permission->character_id = $character->id;
				$permission->forum_id = $forum->id;
				$permission->save();
				Cache::flush(); 
				return Redirect::to("dashboard/storyteller/manage/forums/$forum->id/characters");
			} else {
				return Response::json(['success' => false, 'message' => 'Permission already exists']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);					
		}
	}

	public function removeCharacterForumPermission(Forum $forum) {
		$character = Character::find(Input::get('character'));
		if($character != null) {
			ForumCharacterPermission::where(['character_id' => $character->id, 'forum_id' => $forum->id])->delete();
			Cache::flush(); 
			return Redirect::to("dashboard/storyteller/manage/forums/$forum->id/characters");	
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);					
		}
	}
	
	public function updateForumCategory() {
		$category = ForumCategory::find(Input::get("id"));
		$position = Input::get("order");
		if($category && $position != null) {
			$category->display_order = $position;
			$category->save();
			Cache::flush(); 
			return Redirect::to('/dashboard/storyteller/manage/forums/categories');
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);								
		}
	}

	public function createForumCategory() {
		$name = Input::get("name");
		if($name) {
			$category = new ForumCategory;
			$category->name = $name;
			$category->display_order = 0;
			$category->save();
			Cache::flush(); 
			return Redirect::to('/dashboard/storyteller/manage/forums/categories');
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid data.']);								
		}
	}

	public function deleteForumCategory() {
		$id = Input::get("delete_id");
		ForumCategory::find($id)->delete();
		Cache::flush(); 
		return Redirect::to('/dashboard/storyteller/manage/forums/categories');
	}
}