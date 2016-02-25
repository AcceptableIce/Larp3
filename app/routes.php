<?php

//Force IDs to be numerical.
Route::pattern('id', '[0-9]+');

// Define the classes that routes can accept.
Route::model('character', 'Character');
Route::model('session', 'GameSession');
Route::model('forum', 'Forum');
Route::model('topic', 'ForumTopic');
Route::model('post', 'ForumPost');

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
		Route::get('markallread', 'MailController@markAllRead');	
		
		Route::post('markread', 'MailController@markRead');	
		Route::post('send', 'MailController@sendMessage');		
		Route::post('delete', 'MailController@deleteMessage');
		
		Route::get('user/lookup/{name}', 'MailController@lookupUser');				
	});

	Route::post('characters/cost', 'SaveController@getCost');


	Route::get('character/{character}/{version?}', function(Character $character, $version = -1) {
		if($character->user_id != Auth::user()->id && !Auth::user()->isStoryteller()) {
			 return App::abort(403);
		}
		if($version == -1) $version = $character->approved_version;

		return Response::json($character->getVersion($version));
	});


	Route::group(['prefix' => 'dashboard'], function() {
		Route::get('/', function() { return View::make('dashboard/mail'); });
		Route::get('/characters', function() { return View::make('dashboard/characters'); });	
		Route::get('/mail', function() { return View::make('dashboard/mail'); });
		Route::get('/settings', function() { return View::make('dashboard/settings'); });
		
		Route::post('/settings/save', 'HomeController@saveSettings');
		
		Route::group(['prefix' => 'character/{character}/', 'before' => 'ownsCharacter'], function() {
			Route::get('/print/{version?}', function(Character $character, $version = -1) { 
				return View::make('dashboard/character/print')->with(["character" => $character, 'version' => $version]); 
			});
			Route::get('/cheatsheet/{version?}', function(Character $character, $version = -1) { 
				return View::make('dashboard/character/cheatSheet')->with(["character" => $character, 'version' => $version]); 
			});
			Route::get('/biography', function(Character $character) { 
				return View::make('dashboard/character/questionnaire')->with(["character" => $character]); 
			});
			Route::get('/versioncontrol', function(Character $character) {
				return View::make('dashboard/character/versioncontrol')->with(["character" => $character]); 
			});
			Route::get('/lores', function(Character $character) { 
				return View::make('dashboard/character/lores')->with(["character" => $character]);
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
			
			
			Route::get('/character/{character}/experience', function(Character $character) { 
				return View::make('dashboard/storyteller/character/awardCharacterExperience')->with('character', $character); 
			});						
			Route::get('/character/{character}/changes', function(Character $character) { 
				return View::make('dashboard/storyteller/character/approveCharacter')->with('character', $character);
			});			
			Route::get('/character/{character}/timeout', function(Character $character) { 
				return View::make('dashboard/storyteller/character/characterTimeout')->with('character', $character); 
			});						
			Route::get('/character/{character}/positions', function(Character $character) { 
				return View::make('dashboard/storyteller/character/manageCharacterPositions')->with('character', $character);
			});
			Route::get('/character/{character}/experience/transfer', function(Character $character) { 
				return View::make('dashboard/storyteller/character/transferExperience')->with('character', $character); 
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
			Route::get('/session/checkin/{session}', function(GameSession $session) { 
				return View::make('dashboard/storyteller/sessions/sessionCheckin')->with('session', $session); 
			});		
			Route::get('/session/experience/', function() { 
				return View::make('dashboard/storyteller/sessions/sessionExperience');
			});		
			Route::get('/session/experience/{session}', function(GameSession $session) { 
				return View::make('dashboard/storyteller/sessions/sessionExperience')->with('session', $session); 
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
			Route::get('/manage/files/{id}/delete', 'StorytellerApplicationController@deleteFile');						

			Route::get('/settings/application', function() { 
				return View::make('dashboard/storyteller/tools/applicationSettings'); 
			});	

			Route::get('/cheatsheet', function() { 
				return View::make('dashboard/storyteller/tools/cheatSheet');
			 });	

			Route::get('/influence/caps', function() { 
				return View::make('dashboard/storyteller/influence/influenceCaps'); 
			});	
			
			Route::get('/manage/forums/{forum}/edit', function(Forum $forum) { 
				return View::make('dashboard/storyteller/forums/manageForums')->with(['mode' => 'edit', 'forum' => $forum]); 
			});
			Route::get('/manage/forums/{forum}/characters', function(Forum $forum) { 
				return View::make('dashboard/storyteller/forums/manageForumCharacters')->with(['forum' => $forum]); 
			});				
			Route::get('/manage/forums/new', function() { 
				return View::make('dashboard/storyteller/forums/manageForums')->with(['mode' => 'edit']); 
			});		
			
			Route::get('/stats', function() { 
				return View::make('dashboard/storyteller/character/stats'); 
			});
			Route::get('/manage/forums/{id}/restore', 'StorytellerForumController@restoreForum');	
			Route::get('/manage/forums/{forum}/delete', 'StorytellerForumController@deleteForum');	

			Route::get('/character/{character}/toggleNPC', 'StorytellerCharacterController@toggleNPCStatus');		
			Route::get('/character/{character}/toggleActive', 'StorytellerCharacterController@toggleActiveStatus');	
			
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
			
			Route::post('/manage/forums/{id}/save', 'StorytellerForumController@saveForum');	
			Route::post('/manage/forums/save', 'StorytellerForumController@saveForum');
			Route::post('/manage/forum/{forum}/character/add', 'StorytellerForumController@grantCharacterForumPermission');	
			Route::post('/manage/forum/{forum}/character/remove', 'StorytellerForumController@removeCharacterForumPermission');	

			Route::post('/settings/application/save', 'StorytellerApplicationController@saveApplicationSettings');		

			Route::post('/experience/character/award', 'StorytellerCharacterController@awardCharacterExperience');		
			Route::post('/experience/journal/award', 'StorytellerCharacterController@awardJournalExperience');		
			Route::post('/experience/biographies/award', 'StorytellerCharacterController@awardBiographyExperience');		
			Route::post('/experience/diablerie/award', 'StorytellerCharacterController@awardDiablerieExperience');		

			Route::post('/session/checkin/{session}/character', 'StorytellerSessionController@checkInCharacter');
			Route::post('/session/experience/{session}/award', 'StorytellerSessionController@awardExperience');		
			
			Route::post('/manage/sessions/create', 'StorytellerSessionController@createSession');		
			Route::post('/manage/sessions/delete', 'StorytellerSessionController@deleteSession');		
			
			Route::post('/manage/positions/create', 'StorytellerPositionController@createPosition');		
			Route::post('/manage/positions/delete', 'StorytellerPositionController@deletePosition');	
			
			Route::post('/manage/permissions/grant', 'StorytellerPermissionController@grantPermission');	
			Route::post('/manage/permissions/remove', 'StorytellerPermissionController@removePermission');	
			Route::post('/manage/permissions/create', 'StorytellerPermissionController@createPermission');	
			Route::post('/manage/permissions/delete', 'StorytellerPermissionController@deletePermission');
			
			Route::post('/manage/forums/categories/update', 'StorytellerForumController@updateForumCategory');
			Route::post('/manage/forums/categories/create', 'StorytellerForumController@createForumCategory');
			Route::post('/manage/forums/categories/remove', 'StorytellerForumController@deleteForumCategory');
			
			Route::post('/manage/cheatsheet/save', 'StorytellerApplicationController@saveCheatSheet');						

			Route::post('/influence/caps/add', 'StorytellerInfluenceController@addInfluenceField');	
			Route::post('/influence/caps/update', 'StorytellerInfluenceController@updateInfluenceFields');	
			Route::post('/influence/caps/remove', 'StorytellerInfluenceController@removeInfluenceField');	

			Route::post('/manage/files/upload', 'StorytellerApplicationController@uploadFile');						

			Route::post('/character/{character}/positions/add', 'StorytellerPositionController@grantCharacterPosition');	
			Route::post('/character/{character}/positions/remove', 'StorytellerPositionController@removeCharacterPosition');		
			Route::post('/character/{character}/timeout/set', 'StorytellerCharacterController@setCharacterTimeoutDate');	
			Route::post('/character/{character}/experience/transfer', 'StorytellerCharacterController@transferExperience');
			Route::post('/character/{character}/accept', 'StorytellerCharacterController@acceptChanges');		
			Route::post('/character/{character}/reject', 'StorytellerCharacterController@rejectChanges');		
			
			Route::post('/rulebook/{key}/{id}/edit', 'StorytellerRulebookController@saveRulebookItem');
			Route::post('/rulebook/{key}/{id}/delete', 'StorytellerRulebookController@deleteRulebookItem');
		});
	});

	Route::group(['prefix' => 'forums', 'before' => 'updateUserLastNoticed'], function() {
		Route::get('/', function() { return View::make('forums/forums'); });
		Route::get('/{forum}', function(Forum $forum) { 
			if(Auth::user()->canAccessForum($forum->id)) {
				return View::make('forums/viewForum')->with('forum', $forum); 
			} else {
				return App::abort(404);
			}
		});
		Route::get('/{forum}/post', function(Forum $forum) { 
			if(Auth::user()->canAccessForum($forum->id)) {
				return View::make('forums/postTopic')->with('forum', $forum); 
			} else {
				return App::abort(404);
			}
		});	
		Route::get('/topic/{topic}/post', function(ForumTopic $topic) { 
			if(Auth::user()->canAccessTopic($topic->id)) {
				return View::make('forums/postReply')->with('topic', $topic); 
			} else {
				return App::abort(404);
			}
		});
		Route::get('/post/{post}/edit', function(ForumPost $post) { 
			if(Auth::user()->canAccessTopic($post->topic_id)) {
				return View::make('forums/postReply')->with('post', $post); 
			} else {
				return App::abort(404);
			}
		});		
		Route::get('/topic/{topic}/edit', function(ForumTopic $topic) { 
			if(Auth::user()->canAccessTopic($topic->id)) {
				return View::make('forums/postTopic')->with('topic', $topic); 
			} else {
				return App::abort(404);
			}
		});

		Route::get('/search/{query}', function($query) {
			if(Auth::user()->isStoryteller()) {
				return View::make('forums/search')->with('query', $query);
			} else {
				return App::abort(403);
			}
		});

		Route::get('/topic/{topic}', 'ForumController@showTopic');

		Route::get('/topic/{id}/toggleComplete', 'ForumController@toggleTopicComplete');
		Route::get('/topic/{id}/toggleSticky', 'ForumController@toggleTopicSticky');
		Route::post('/topic/{id}/toggleLike', 'ForumController@toggleTopicSticky');

		Route::get('/topic/{id}/toggleWatch', 'ForumController@toggleWatch');		

		Route::post('/topic/post', 'ForumController@postTopic');
		Route::post('/reply/post', 'ForumController@postReply');

		Route::get('/{id}/read', 'ForumController@markForumRead');
		Route::get('/category/{id}/read', 'ForumController@markCategoryRead');

		Route::post('/post/delete', 'ForumController@deletePost');
		Route::post('/alert', 'ForumController@alertSTs');
		Route::post('/post/{post}/toggleLike', 'ForumController@toggleLike');
	});



	Route::get('generator', function() { return View::make('generator'); });
	Route::get('generator/beta', function() { return View::make('generator-beta'); });

	Route::group(['before' => 'ownsCharacter'], function() {
		Route::get('generator/{character}', function(Character $character) { return View::make('generator')->with("character", $character); });
		Route::post('generator/{character}/reset', 'SaveController@resetCurrentChanges');
			
		Route::group(['before' => 'storyteller'], function() {
			Route::post('generator/{character}/options/save', 'SaveController@saveStorytellerOptions');
		});
	});
});

Route::get('login', function() { return View::make('login'); });

Route::post('login', ['uses' => 'HomeController@doLogin']);

Route::get('logout', ['uses' => 'HomeController@doLogout']);
Route::post('createAccount', ['uses' => 'HomeController@createAccount']);

Route::get('rulebook', 'HomeController@buildRulebook');

Route::get('character/verify/{character}/{version?}', function(Character $character, $version = -1) {
	if($version == -1) $version = $character->approved_version;
	return Response::json($character->verify($version, true));
});

Route::controller('password', 'RemindersController');

App::missing(function($exception) { 
	return Response::view('errors/404', [], 404); 
});

App::error(function($error, $code) {
	if ($code != 500 || Config::getEnvironment() == 'production') {
		return Response::view('errors.'.$code, [], $code);
	}
});
