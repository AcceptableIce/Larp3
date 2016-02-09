<?php
class HomeController extends BaseController {

	public function showWelcome() {
		return View::make('hello');
	}
	
	public function buildRulebook($owner = -1) {
		//Build rulebook or retrieve it from the cache.
		$out = Cache::remember('rulebook', 24*60, function() {
			$baseRulebook = [];
			$baseRulebook['sects'] = $this->expandSects(RulebookSect::get());
			$baseRulebook['clans'] = $this->expandClans(RulebookClan::get());
			$baseRulebook['rituals'] = $this->sortByGroups(RulebookRitual::whereNull('owner')->get(), "rituals");
			$baseRulebook['backgrounds'] = $this->sortByGroups(RulebookBackground::orderBy('name')->get(), "backgrounds");
			$baseRulebook['disciplines'] = $this->expandDisciplines(RulebookDiscipline::orderBy('name')->get());
			$baseRulebook['natures'] = RulebookNature::get();
			$baseRulebook['abilities'] = $this->sortByGroups(RulebookAbility::where('owner', null)->orderBy('name')->get(), "abilities");
			$baseRulebook["paths"] = $this->expandPaths(RulebookPath::get());
			$baseRulebook["derangements"] = RulebookDerangement::get();
			$baseRulebook['merits'] = $this->sortByGroups(RulebookMerit::orderBy("cost")->get(), "merits");
			$baseRulebook['flaws'] = $this->sortByGroups(RulebookFlaw::orderBy("cost")->get(), "flaws");

			return $baseRulebook;
		});
		if($owner != -1) {
			$out['custom_abilities'] = RulebookAbility::where('owner', $owner)->get();
			$out['elder_powers'] = RulebookElderPower::where('owner_id', $owner)->get();	
			$out['combo_disciplines'] = RulebookComboDiscipline::where('owner_id', $owner)->get();
			$out['custom_rituals'] = RulebookRitual::where('owner', $owner)->get();
		}


		return Response::json($out);
	}
	
	private function expandSects($sects) {
		foreach($sects as $s) {
			//Purge data we don't need
			$commons = [];
			$uncommons = [];			
			foreach($s->commonClans() as $c) {
				$commons[] = $c;
				$c['disciplines'] = $this->getDisciplineNames($c->disciplines());
				$this->purge_timestamps($c);
			}
			foreach($s->uncommonClans() as $c) {
				$uncommons[] = $c;
				$c['disciplines'] = $this->getDisciplineNames($c->disciplines());
				$this->purge_timestamps($c);

			}
			$s['common_clans'] = $commons;
			$s['uncommon_clans'] = $uncommons;
		}
		return $sects;
	}
	
	private function getDisciplineNames($disciplines) {
		$out = [];
		foreach($disciplines as $d) {
			$out[] = $d->name;
		}
		return $out;
	}
	private function expandClans($clans) {
		foreach($clans as $c) {
			$c['disciplines'] = $this->expandDisciplines($c->disciplines());
			$this->purge_timestamps($c);
		}
		return $clans;
	}
	
	private function expandDisciplines($disciplines) {
		foreach($disciplines as $d) {
			$paths = $d->paths()->get();
			if(sizeof($paths) > 0) {
				foreach($paths as $p) {
					$p['ranks'] = $p->ranks()->get();
				}
				$d['paths'] = $paths;
			} else {
				$d['ranks'] = $d->ranks()->get();
			}
		}
		return $disciplines;
	}

	private function expandPaths($paths) {
		foreach($paths as $p) {
			$p["sins"] = explode("||", $p["sins"]);
			$p["stats"] = explode("||", $p["stats"]);
		}
		return $paths;
	}

	private function sortByGroups($list, $type) {
		$outList = [];
		$lookupList = [];
		$count = 0;
		foreach($list as $r) {
			if(!isset($lookupList[$r->group])) {
				//New group
				$outList[] = array("name" => $r->group, "options" => [$r]);
				$lookupList[$r->group] = $count++;
			} else {
				$outList[$lookupList[$r->group]]["options"][] = $r;
			}
		}
		usort($outList, function($a, $b) use ($type){
			$ordering = [	
							"merits" => ["Other", "Physical", "Mental", "Social", "Supernatural"],
							"flaws" => ["Other", "Physical", "Mental", "Social", "Supernatural"],
							"backgrounds" => ["Backgrounds", "Influence", "Lores"],
							"abilities" => ["Combat", "Discipline", "Other"],
							"rituals" => ["Basic", "Intermediate", "Advanced"]
						];
			$va = $a["name"];
			$vb = $b["name"];
			return array_search($va, $ordering[$type]) > array_search($vb, $ordering[$type]);
			
		});
		return $outList;
	}

	private function purge_timestamps($value) {
		unset($value['created_at']);
		unset($value['updated_at']);
		return $value;
	}

	public function doLogin() {
		$rules = array(
			'username' => 'required',
			'password' => 'required|min:3'
		);
		$validator = Validator::make(Input::all(), $rules);
		if($validator->fails()) {
			return Redirect::to('login')->withErrors($validator)->withInput(Input::except('password'))->with('mode', 0);
		} else {
			$user = User::where('username', '=', Input::get('username'));
			if($user->count() == 0) return Redirect::to('login')->withErrors(array('message' => 'Invalid username or password.'));
			$user = $user->first(); 
			if($user->password == hash('SHA256', Input::get('password'))) {
				Auth::login($user, true);
				return Redirect::to('/');
			} else {
				return Redirect::to('login')->with('login_errors', true)->withErrors(array('message' => 'Invalid username or password.'));
			}
		}	
	}
	public function createAccount() {
		$rules = array(
			'register_username' => 'required|username|unique:users,username',
			'register_password' => 'required|min:6|confirmed',
			'register_email' => 'required|unique:users,email|email'
		);
		$validator = Validator::make(Input::all(), $rules);
		if($validator->fails()) {
			return Redirect::to('login')->with('mode', 1)->withErrors($validator)->withInput(Input::except('password'));
		} else {
			$user = new User;
			$user->username = Input::get('register_username');
			$user->password = hash('SHA256', Input::get('register_password'));
			$user->email = Input::get('register_email');
			$user->save();
			Auth::login($user, true);
			return Redirect::to('/');
		}
	} 
	
	public function saveSettings() {
		$user = Auth::user();
		foreach(UserSettingDefinition::all() as $definition) {
			$value = Input::get("user-settings-".$definition->id);
			if($definition->type == "checkbox") $value = ($value == "on" ? 1 : 0);
			if(isset($value)) {
				$setting = UserSetting::firstOrNew(['user_id' => $user->id, 'definition_id' => $definition->id]);
				$setting->value = $value;
				$setting->save();
			}
		}
		
		return Redirect::to('/dashboard/settings');
	}

	public function doLogout() {
		Auth::logout();
		return Redirect::to('login');
	}

	public function showUpload($upload) {
		$upload = FileUpload::where('url', $upload)->first();
		if($upload) {
			if($upload->read_permission) {
				$user = Auth::user();
				if(!$user || !$user->hasPermissionById($user)) App::abort(503);
			}
			$fileName = app_path()."/uploads/".$upload->url;
			$file = File::get($fileName);
			$response = Response::make($file, 200);
            // using this will allow you to do some checks on it (if pdf/docx/doc/xls/xlsx)
            $response->header('Content-Type', mime_content_type($fileName));
			return $response;
		} else {
			App::abort(404); 
		}
	}
}
