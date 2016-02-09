<?
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class HandbookPage extends Eloquent {
	use SoftDeletingTrait;

	protected $table = 'handbook_pages';
    protected $dates = ['deleted_at'];

	public static function getByTitle($title, $initial = true) {
		if($initial) Session::forget('redirect');
		$page = HandbookPage::where('title', 'LIKE', $title)->first();
		if($page && Session::get('redirect') != $title) {
			$found = null;
			preg_replace_callback("/\[\[([\w\W]+?)\]\]/", function($match) use(&$found) {
				$parts = explode("/", $match[1]);
				if(sizeof($parts) > 1) {
					//This is a command
					switch(strtolower($parts[0])) {
						case "redirect":
							$source_page = HandbookPage::where('title', $parts[1])->first();
							if($source_page) {
								if(Input::get('redirect') != "no") {
									$found = $parts[1];	
								}
							}
					}
				}
			}, $page->body);
			if($found) {
				Session::set('redirect', $page->title);
				return HandbookPage::getByTitle($found, false);
			}
		}
		return $page;
	}
	public function body() {
		$body = $this->body;
		$body = preg_replace_callback("/\[\[([\w\W]+?)\]\]/", function($match) {
			$parts = explode("/", $match[1]);
			if(sizeof($parts) > 1) {
				//This is a command
				switch(strtolower($parts[0])) {
					case "redirect":
						$source_page = HandbookPage::where('title', $parts[1])->first();
						if($source_page) {
							if(Input::get('redirect') == "no") {
								return 'This page redirects to <a href="/handbook/'.HandbookPage::getURLReadyLink($parts[1]).'">'.$source_page->title.'</a>.';
							} else {
								return 'Redirecting...';
							}
						} else {
							return 'This page has an invalid redirect.';
						}
				}
			}
			//Look for the relevant page
			$name = $match[1];
			if(strrpos($name, '{')) {
				$parts = explode('{', $name);
				$name = $parts[0];
				$link = str_replace('}', '', $parts[1]);
			} else {
				$link = $name;
			}
			$page = HandbookPage::where('title', $link)->exists();
			$rawLink = HandbookPage::getURLReadyLink($link);
			return "<a class='page-link ".(!$page ? 'invalid' : '')."' href='/handbook/".$rawLink."'>$name</a>";
		}, $body);
		return $body;
	}
	
	public function updatedBy() {
		return $this->hasOne('User', 'id', 'updated_by');
	}
	
	public function createdBy() {
		return $this->hasOne('User', 'id', 'created_by');
	}

	public function readPermission() {
		return $this->hasOne('HandbookPagePermission', 'id', 'read_permission');
	}

	public function writePermission() {
		return $this->hasOne('HandbookPagePermission', 'id', 'write_permission');
	}

	public function userCanRead($user) {
		return $this->checkPermission("read", $user);
	}

	public function userCanWrite($user) {
		return $this->checkPermission("write", $user);
	}

	public function checkPermission($type, $user) {
		$permission = $this->{$type."Permission"};
		if(!$user) return !($permission && $permission->hasRestrictions()) && $type == "read";
		if($permission == null || $this->created_by == $user->id) return true;
		$activeCharacter = $user->activeCharacter();
		$canView = true;
		if($permission->sect_id) {
			if($activeCharacter) {
				$activeSect = $activeCharacter->sect()->first();
				$activeSectId = $activeSect ? $activeSect->sect_id : -1;
				$canView = $canView && $permission->sect_id == $activeSectId;
			} else {
				$canView = false;
			}
		}
		if($permission->clan_id) {
			if($activeCharacter) {
				$activeClan = $activeCharacter->clan()->first();
				$activeClanId = $activeClan ? $activeClan->clan_id : -1;
				$canView = $canView && $permission->clan_id == $activeClanId;
			} else {
				$canView = false;
			}
		}
		if($permission->background_id) {
			if($activeCharacter) {
				$canView = $canView && $activeCharacter->getBackgroundDots($permission->background->name) > 0;
			} else {
				$canView = false;
			}
		}
		if($permission->permission_id) {
			$canView = $canView && $user->hasPermissionById($permission->permission_id);
		}			
		return $canView;
	}

	public function permissionList($type) {
		$list = [];
		$permission = $this->{$type."Permission"};
		if($permission->permission_id) $list[] = str_plural($permission->userPermission->name);
		if($permission->sect_id) $list[] = $permission->sect->name;
		if($permission->clan_id) $list[] = $permission->clan->name;
		if($permission->background_id) $list[]= "Characters with ".$permission->background->name;
		return $list;
	}

	public function readPermissionList($glue = ", ") {
		return implode($glue, $this->permissionList("read"));
	}

	public function writePermissionList($glue = ", ") {
		return implode($glue, $this->permissionList("write"));
	}

	public static function getURLReadyLink($link) {
		$rawLink = rawurlencode($link);
		$rawLink = str_replace("%20", "_", $rawLink);
		return $rawLink;
	}

}
