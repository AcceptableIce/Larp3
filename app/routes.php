<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function() {
	if(Auth::user()) {
		return Redirect::to('dashboard');
	} else {
		return View::make('handbook/viewPage')->with('name', "Welcome to Carpe Noctem");
	}
});

Route::get("/roster", function() { return View::make('roster'); });
Route::get("/calendar", function() { return View::make('calendar'); });
Route::get("/contact", function() { return View::make('contact'); });
Route::get("/influence", function() { return View::make('influence'); });

Route::get("/larp101", function() { return Redirect::to(ApplicationSetting::get('LARP 101 PDF')); });
Route::get("/larp101/doc", function() { return Redirect::to(ApplicationSetting::get('LARP 101 Google Doc')); });
Route::get("/larp201", function() { return Redirect::to(ApplicationSetting::get('LARP 201 PDF')); });
Route::get("/larp201/doc", function() { return Redirect::to(ApplicationSetting::get('LARP 201 Google Doc')); });

Route::get("/uploads/{file}", 'HomeController@showUpload');
Route::post("/contact/send", 'HomeController@contactStorytellers');

Route::group(['prefix' => 'handbook'], function() {
	Route::get('/', function() { return View::make('handbook/viewPage')->with('name', 'The Handbook'); });
	Route::get('/directory', function() { 
		if(Auth::check()) {
			return View::make('handbook/directory');
		} else {
			return Redirect::to('/');
		} 
	});
	Route::get('/create', function() { return View::make('handbook/editPage')->with(['mode' => 0]); });
	Route::get('/{name}', function($name) { return View::make('handbook/viewPage')->with('name', $name); });
	Route::get('/{name}/create', function($name) { return View::make('handbook/editPage')->with(['name' => $name, 'mode' => 0]); });
	Route::get('/{name}/edit', function($name) { return View::make('handbook/editPage')->with(['name' => $name, 'mode' => 1]); });

	Route::post('/save', 'HandbookController@save');

	Route::group(['before' => 'storyteller'], function() {
		Route::get('/{id}/delete', 'HandbookController@deletePage');
	});
});

Route::group(['before' => 'auth'], function() {
	Route::get('rulebook/{owner}', 'HomeController@buildRulebook');

	Route::post('characters/save', 'SaveController@saveCharacter');

	Route::group(['before' => 'ownsCharacter'], function() {
		Route::post('characters/delete', 'SaveController@deleteCharacter');
		Route::post('characters/revert', 'SaveController@revertCharacter');		
	});

	Route::group(['prefix' => 'mail'], function() {
		Route::post('markread', 'MailController@markRead');
		Route::get('markallread', 'MailController@markAllRead');		
		Route::post('send', 'MailController@sendMessage');		
		Route::post('delete', 'MailController@deleteMessage');
		
		Route::get('user/lookup/{name}', 'MailController@lookupUser');				
	});

	Route::post('characters/cost', 'SaveController@getCost');


	Route::get('character/{id}/{version?}', function($id, $version = -1) {
		$character = Character::find($id);
		if(!$character || ($character->user_id != Auth::user()->id && !Auth::user()->isStoryteller())) return Redirect::to("/");
		if($version == -1) $version = $character->approved_version;

		return Response::json($character->getVersion($version));
	});


	Route::group(['prefix' => 'dashboard'], function() {
		Route::get('/', function() { return View::make('dashboard/mail'); });
		Route::get('/characters', function() { return View::make('dashboard/characters'); });	
		Route::get('/mail', function() { return View::make('dashboard/mail'); });
		Route::get('/settings', function() { return View::make('dashboard/settings'); });
		
		Route::post('/settings/save', 'HomeController@saveSettings');
		
		Route::group(['prefix' => 'character/{id}/', 'before' => 'ownsCharacter'], function() {
			Route::get('/print/{version?}', function($id, $version = -1) { 
				return View::make('dashboard/character/print')->with(["character_id" => $id, 'version' => $version]); 
			});
			Route::get('/cheatsheet/{version?}', function($id, $version = -1) { 
				return View::make('dashboard/character/cheatSheet')->with(["character_id" => $id, 'version' => $version]); 
			});
			Route::get('/biography', function($id) { 
				return View::make('dashboard/character/questionnaire')->with(["character_id" => $id]); 
			});
			Route::get('/versioncontrol', function($id) {
				return View::make('dashboard/character/versioncontrol')->with(["character_id" => $id]); 
			});
			Route::get('/lores', function($id) { 
				return View::make('dashboard/character/lores')->with(["character_id" => $id]);
			});
			Route::post('/biography/submit', 'SaveController@saveBiography');
		});

		Route::group(['prefix' => 'storyteller', 'before' => 'storyteller'], function() {
			Route::get('/', function() { 
				return View::make('dashboard/storyteller/storytellerHome'); 
			});
			
			Route::get('/characters/all', function() { 
				return View::make('dashboard/storyteller/character/listCharacters')->with('filter', 'all'); 
			});		
			Route::get('/characters', function() { 
				return View::make('dashboard/storyteller/character/listCharacters')->with('filter', 'complete'); 
			});		
			Route::get('/characters/changed', function() { 
				return View::make('dashboard/storyteller/character/listCharacters')->with('filter', 'changed'); 
			});		
			Route::get('/characters/new', function() { 
				return View::make('dashboard/storyteller/character/listCharacters')->with('filter', 'new'); 
			});		
			Route::get('/characters/npcs', function() { 
				return View::make('dashboard/storyteller/character/listCharacters')->with('filter', 'npcs'); 
			});		
			Route::get('/characters/npcs/active', function() { 
				return View::make('dashboard/storyteller/character/listCharacters')->with('filter', 'npcs-active'); 
			});	
			
			
			Route::get('/character/{id}/experience', function($id) { 
				return View::make('dashboard/storyteller/character/awardCharacterExperience')->with('id', $id); 
			});						
			Route::get('/character/{id}/changes', function($id) { 
				return View::make('dashboard/storyteller/character/approveCharacter')->with('id', $id); 
			});			
			Route::get('/character/{id}/timeout', function($id) { 
				return View::make('dashboard/storyteller/character/characterTimeout')->with('id', $id); 
			});						
			Route::get('/character/{id}/positions', function($id) { 
				return View::make('dashboard/storyteller/character/manageCharacterPositions')->with('id', $id); 
			});
			Route::get('/character/{id}/experience/transfer', function($id) { 
				return View::make('dashboard/storyteller/character/transferExperience')->with('id', $id); 
			});	

			Route::get('/experience/journal', function() { 
				return View::make('dashboard/storyteller/character/journalExperience'); 
			});		
			Route::get('/experience/biographies', function() { 
				return View::make('dashboard/storyteller/character/biographyExperience'); 
			});		
			Route::get('/experience/diablerie', function() { 
				return View::make('dashboard/storyteller/character/diablerieExperience'); 
			});		

			Route::get('/session/checkin/', function() { 
				return View::make('dashboard/storyteller/sessions/sessionCheckin'); 
			});		
			Route::get('/session/checkin/{id}', function($id) { 
				return View::make('dashboard/storyteller/sessions/sessionCheckin')->with('id', $id); 
			});		
			Route::get('/session/experience/', function() { 
				return View::make('dashboard/storyteller/sessions/sessionExperience');
			});		
			Route::get('/session/experience/{id}', function($id) { 
				return View::make('dashboard/storyteller/sessions/sessionExperience')->with('id', $id); 
			});	
				
			Route::get('/manage/positions', function() { 
				return View::make('dashboard/storyteller/tools/managePositions'); 
			});		
			Route::get('/manage/sessions', function() { 
				return View::make('dashboard/storyteller/sessions/manageSessions'); 
			});		
			Route::get('/manage/forums', function() { 
				return View::make('dashboard/storyteller/forums/manageForums')->with('mode', 'management'); 
			});				
			Route::get('/manage/permissions', function() { 
				return View::make('dashboard/storyteller/tools/manageUserPermissions'); 
			});				
			Route::get('/manage/forums/categories', function() { 
				return View::make('dashboard/storyteller/forums/manageForumCategories'); 
			});						
			Route::get('/manage/cheatsheet', function() { 
				return View::make('dashboard/storyteller/tools/manageCheatSheet'); 
			});		
							
			Route::get('/manage/files', function() { 
				return View::make('dashboard/storyteller/tools/manageFiles')->with('mode', 'manage'); 
			});						
			Route::get('/manage/files/new', function() { 
				return View::make('dashboard/storyteller/tools/manageFiles')->with('mode', 'edit'); 
			});						
			Route::get('/manage/files/{id}/edit', function($id) { 
				return View::make('dashboard/storyteller/tools/manageFiles')->with(['mode' => 'edit', 'id' => $id]); 
			});						
			Route::get('/manage/files/{id}/delete', 'StorytellerController@deleteFile');						

			Route::get('/settings/application', function() { 
				return View::make('dashboard/storyteller/tools/applicationSettings'); 
			});	

			Route::get('/cheatsheet', function() { 
				return View::make('dashboard/storyteller/tools/cheatSheet');
			 });	

			Route::get('/influence/caps', function() { 
				return View::make('dashboard/storyteller/influence/influenceCaps'); 
			});	
			
			Route::get('/manage/forums/{id}/edit', function($id) { 
				return View::make('dashboard/storyteller/forums/manageForums')->with(['mode' => 'edit', 'id' => $id]); 
			});
			Route::get('/manage/forums/{id}/characters', function($id) { 
				return View::make('dashboard/storyteller/forums/manageForumCharacters')->with(['id' => $id]); 
			});				
			Route::get('/manage/forums/new', function() { 
				return View::make('dashboard/storyteller/forums/manageForums')->with(['mode' => 'edit']); 
			});		
			
			Route::get('/stats', function() { 
				return View::make('dashboard/storyteller/character/stats'); 
			});
			Route::get('/manage/forums/{id}/restore', 'StorytellerController@restoreForum');	
			Route::get('/manage/forums/{id}/delete', 'StorytellerController@deleteForum');	

			Route::get('/character/{id}/toggleNPC', 'StorytellerController@toggleNPCStatus');		
			Route::get('/character/{id}/toggleActive', 'StorytellerController@toggleActiveStatus');	
			
			Route::get('/rulebook', function() {
				return View::make('dashboard/storyteller/rulebook/viewAll');
			});
		
			Route::get('/rulebook/{key}', function($key) {
				return View::make('dashboard/storyteller/rulebook/viewType')->with("key", $key);
			});
			
			Route::get('/rulebook/{key}/new', function($key) {
				return View::make('dashboard/storyteller/rulebook/editItem')->with(["key" => $key]);
			});
			
			Route::get('/rulebook/{key}/{id}', function($key, $id) {
				return View::make('dashboard/storyteller/rulebook/editItem')->with(["key" => $key, "id" => $id]);
			});
			
			Route::get('/cache/clear', function() { 
				Cache::flush(); 
				return Redirect::to('/dashboard/storyteller'); 
			});
			
			Route::post('/manage/forums/{id}/save', 'StorytellerController@saveForum');	
			Route::post('/manage/forums/save', 'StorytellerController@saveForum');
			Route::post('/manage/forum/{id}/character/add', 'StorytellerController@grantCharacterForumPermission');	
			Route::post('/manage/forum/{id}/character/remove', 'StorytellerController@removeCharacterForumPermission');	

			Route::post('/settings/application/save', 'StorytellerController@saveApplicationSettings');		

			Route::post('/experience/character/award', 'StorytellerController@awardCharacterExperience');		
			Route::post('/experience/journal/award', 'StorytellerController@awardJournalExperience');		
			Route::post('/experience/biographies/award', 'StorytellerController@awardBiographyExperience');		
			Route::post('/experience/diablerie/award', 'StorytellerController@awardDiablerieExperience');		

			Route::post('/session/checkin/{id}/character', 'StorytellerController@checkInCharacter');
			Route::post('/session/experience/{id}/award', 'StorytellerController@awardExperience');		
			
			Route::post('/manage/sessions/create', 'StorytellerController@createSession');		
			Route::post('/manage/sessions/delete', 'StorytellerController@deleteSession');		
			Route::post('/manage/positions/create', 'StorytellerController@createPosition');		
			Route::post('/manage/positions/delete', 'StorytellerController@deletePosition');	
			Route::post('/manage/permissions/grant', 'StorytellerController@grantPermission');	
			Route::post('/manage/permissions/remove', 'StorytellerController@removePermission');	
			Route::post('/manage/permissions/create', 'StorytellerController@createPermission');	
			Route::post('/manage/permissions/delete', 'StorytellerController@deletePermission');
			Route::post('/manage/forums/categories/update', 'StorytellerController@updateForumCategory');
			Route::post('/manage/forums/categories/create', 'StorytellerController@createForumCategory');
			Route::post('/manage/forums/categories/remove', 'StorytellerController@deleteForumCategory');
			Route::post('/manage/cheatsheet/save', 'StorytellerController@saveCheatSheet');						

			Route::post('/influence/caps/add', 'StorytellerController@addInfluenceField');	
			Route::post('/influence/caps/update', 'StorytellerController@updateInfluenceFields');	
			Route::post('/influence/caps/remove', 'StorytellerController@removeInfluenceField');	

			Route::post('/manage/files/upload', 'StorytellerController@uploadFile');						

			Route::post('/character/{id}/positions/add', 'StorytellerController@grantCharacterPosition');	
			Route::post('/character/{id}/positions/remove', 'StorytellerController@removeCharacterPosition');		
			Route::post('/character/{id}/timeout/set', 'StorytellerController@setCharacterTimeoutDate');	
			Route::post('/character/{id}/experience/transfer', 'StorytellerController@transferExperience');
	
			Route::post('/character/{id}/accept', 'StorytellerController@acceptChanges');		
			Route::post('/character/{id}/reject', 'StorytellerController@rejectChanges');		
			
			Route::post('/rulebook/{key}/{id}/edit', 'StorytellerController@saveRulebookItem');
			Route::post('/rulebook/{key}/{id}/delete', 'StorytellerController@deleteRulebookItem');
		});
	});

	Route::group(['prefix' => 'forums', 'before' => 'updateUserLastNoticed'], function() {
		Route::get('/', function() { return View::make('forums/forums'); });
		Route::get('/{id}', function($id) { 
			if(Auth::user()->canAccessForum($id)) {
				return View::make('forums/viewForum')->with('id', $id); 
			} else {
				return "Access denied.";
			}
		});
		Route::get('/{id}/post', function($id) { 
			if(Auth::user()->canAccessForum($id)) {
				return View::make('forums/postTopic')->with('id', $id); 
			} else {
				return "Access denied.";
			}
		});	
		Route::get('/topic/{id}/post', function($id) { 
			$topic = ForumTopic::find($id);
			if($topic && Auth::user()->canAccessTopic($id)) {
				return View::make('forums/postReply')->with('id', $id); 
			} else {
				return "Access denied.";
			}
		});
		Route::get('/post/{id}/edit', function($id) { 
			$post = ForumPost::find($id);
			if($post && Auth::user()->canAccessTopic($post->topic_id)) {
				return View::make('forums/postReply')->with('post_id', $id); 
			} else {
				return "Access denied.";
			}
		});		
		Route::get('/topic/{id}/edit', function($id) { 
			$topic = ForumTopic::find($id);
			if($topic && Auth::user()->canAccessTopic($id)) {
				return View::make('forums/postTopic')->with('topic_id', $id); 
			} else {
				return "Access denied.";
			}
		});

		Route::get('/search/{query}', function($query) {
			if(Auth::user()->isStoryteller()) {
				return View::make('forums/search')->with('query', $query);
			} else {
				return "Access denied.";
			}
		});

		Route::get('/topic/{id}', 'ForumController@showTopic');

		Route::get('/topic/{id}/toggleComplete', 'ForumController@toggleTopicComplete');
		Route::get('/topic/{id}/toggleSticky', 'ForumController@toggleTopicSticky');

		Route::get('/topic/{id}/toggleWatch', 'ForumController@toggleWatch');		

		Route::post('/topic/post', 'ForumController@postTopic');
		Route::post('/reply/post', 'ForumController@postReply');

		Route::get('/{id}/read', 'ForumController@markForumRead');
		Route::get('/category/{id}/read', 'ForumController@markCategoryRead');

		Route::post('/post/delete', 'ForumController@deletePost');
		Route::post('/alert', 'ForumController@alertSTs');

	});



	Route::get('generator', function() { return View::make('generator'); });
	Route::get('generator/beta', function() { return View::make('generator-beta'); });

	Route::group(['before' => 'ownsCharacter'], function() {
		Route::get('generator/{id}', function($id) { return View::make('generator')->with("character_id", $id); });
		Route::post('generator/{id}/reset', 'SaveController@resetCurrentChanges');
			
		Route::group(['before' => 'storyteller'], function() {
			Route::post('generator/{id}/options/save', 'SaveController@saveStorytellerOptions');
		});
	});
});

Route::get('login', function() { return View::make('login'); });
Route::post('login', ['uses' => 'HomeController@doLogin']);

Route::get('logout', ['uses' => 'HomeController@doLogout']);
Route::post('createAccount', ['uses' => 'HomeController@createAccount']);

Route::get('rulebook', 'HomeController@buildRulebook');

Route::get('character/verify/{id}/{version?}', function($id, $version = -1) {
	$character = Character::find($id);
	if($version == -1) $version = $character->approved_version;

	return Response::json($character->verify($version, true));
});

Route::controller('password', 'RemindersController');

App::missing(function($exception) { return Response::view('errors/404', [], 404); });

/* This is a comment! -rabyrd */
