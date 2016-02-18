<?php
class StorytellerController extends BaseController {
	public function acceptChanges($id) {
		$character = Character::find($id);
		if($character) {
			if($character->in_review) {
				$character->approved_version = $character->latestVersion()->version;
				$character->in_review = false;
				//Inactivate all other characters this player owns.
				if(!$character->owner->isStoryteller()) {
					foreach(Character::where('user_id', $character->owner->id)->get() as $c) {
						$c->active = false;
						$c->save();
					}
					$was_active = $character->active;
					$character->active = true;
					if(!$was_active) {
						//This is a new character
						$character->owner->sendMessage(null, "New Character Accepted", "The Storytellers have accepted your character \"".$character->name."\".".
														" Any of your characters that were previously active have been inactivated. You have been granted access to your new clan's forum,
														and access to your old clan forum has been revoked. If you have any questions, please post in the \"General Messages\" forum.");
						$character->approved_at = new DateTime;
						$xp_cost = $character->getExperienceCost(1);
						$character->experience -= 10 - $xp_cost;
						Cache::forget("character-experience-$id");
						$character->save();
					} else {
						$character->owner->sendMessage(null, "Changes to ".$character->name." Accepted", "The Storytellers have accepted your changes to \"".$character->name."\".".
														" You can now access your character and make further changes as necessary.");					
					}
				}
				$character->save();
			}
			Cache::forget('forum-listing-'.$character->owner->id);
			return Redirect::to($_SERVER['HTTP_REFERER']);
			//return Redirect::to('/dashboard/storyteller/characters');
		} else {
			return "An error occured while accepting this character (Could not be found). Please try again later.";
		}
	}

	public function rejectChanges($id) {
		$character = Character::find($id);
		if($character) {
			if($character->in_review) {
				$character->in_review = false;
				$character->save();
				$character->revertChanges($character->activeVersion());
				if(!$character->active) {
					//This is a new character
					$character->owner->sendMessage(null, "New Character Rejected", "The Storytellers have rejected your character \"".$character->name."\".".
													" You can now access your character and make further changes as necessary. If you have any questions, please post in the \"General Messages\" forum.");
				} else {
					$character->owner->sendMessage(null, "Changes to ".$character->name." Rejected", "The Storytellers have rejected your changes to \"".$character->name."\".".
													" You can now access your character and make further changes as necessary. If you have any questions, please post in the \"General Messages\" forum.");		
				}
			}
			return Redirect::to($_SERVER['HTTP_REFERER']);
			//return Redirect::to('/dashboard/storyteller/characters');
		} else {
			return "An error occured while accepting this character (Could not be found). Please try again later.";
		}
	}

	public function createSession() {
		$date = Input::get("date");
		if($date) {
			$session = new GameSession;
			$session->date = DateTime::createFromFormat('m/d/Y', $date);
			$session->save();
			return Redirect::to('/dashboard/storyteller/manage/sessions');
		} else {
			return "Invalid date.";
		}
	}	

	public function deleteSession() {
		$id = Input::get("id");
		GameSession::find($id)->delete();
		return Redirect::to('/dashboard/storyteller/manage/sessions');
	}	

	public function setCharacterTimeoutDate($id) {
		$character = Character::find($id);
		$timeout = Input::get("date");
		if($character && $timeout) {
			$character->time_out = DateTime::createFromFormat('m/d/Y', $timeout);
			$character->save();
			return Redirect::to('/dashboard/storyteller/characters');
		} else {
			return 'Invalid data.';
		}
	}
	public function checkInCharacter($id) {
		$session = GameSession::find($id);
		if($session) {
			$character = Character::find(Input::get("id"));
			if($character) {
				if(!GameSessionCheckIn::where(['session_id' => $session->id, 'character_id' => $character->id])->exists()) {
					$checkin = new GameSessionCheckIn;
					$checkin->session_id = $session->id;
					$checkin->character_id = $character->id;
					$checkin->costume = Input::get("costume") == "true" ? 1 : 0;
					$checkin->save();
					$willpower = $character->willpower()->first();
					$willpower->willpower_current += 2;
					if($willpower->willpower_current > $willpower->willpower_total) $willpower->willpower_current = $willpower->willpower_total;
					$willpower->save();
					return Response::json(['success' => true, 'message' => $character->name. " checked in!"]);
				} else {
					return Response::json(['success' => false, 'message' => 'Character already checked in.']);	
				}
			} else {
				return Response::json(['success' => false, 'message' => 'Unable to find character.']);
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find session.']);
		}
	}

	public function awardExperience($id) {
		$session = GameSession::find($id);
		if($session) {
			$ids = Input::get("ids");
			$costumes = Input::get("costumes");
			$nom1s = Input::get("nom1s");
			$nom2s = Input::get("nom2s");
			$overrides = Input::get("overrides");
			$save = Input::get("save");
			$missingCharacters = [];
			foreach($ids as $index => $id) {
				$character = Character::find($id);
				if($character) {
					$checkIn = GameSessionCheckIn::where(['session_id' => $session->id, 'character_id' => $character->id])->first();
					$checkIn->costume = $costumes[$index] == "true" ? 1 : 0;
					$checkIn->nominated = $nom1s[$index] == "true"  ? 1 : 0;
					$checkIn->nominated_twice = $nom2s[$index] == "true"  ? 1 : 0;
					$checkIn->bonus = $overrides[$index];
					$checkIn->total_experience = 1 + ($checkIn->costume ? 1 : 0) + ($checkIn->nominated ? 1 : 0) + ($checkIn->nominated_twice ? 1 : 0) + $checkIn->bonus;
					$checkIn->save();
					if(!isset($save)) {
						$owner = $character->owner;
						$owner->sendMessage(null, "Experience awarded to ".$character->name, "The Storytellers have awarded your character ".$checkIn->total_experience." Experience.  You can now use the character editor to make changes to it at your leisure.  
											Remember, changes should be submitted by 6:00pm on the Wednesday before a game to ensure that they have the chance to review them.");
						$character->awardExperience($checkIn->total_experience);
						$character->save();
						}
				} else {
					$missingCharacters[] = $id;
				}
			}
			if(!isset($save)) {
				$session->submitted = true;
				$session->save();
				foreach(User::listStorytellers() as $st) {
					$st->sendMessage(null, "Experience successfully awarded", "The action to award experience for the session on ".$session->date." has completed.".
											(sizeof($missingCharacters) > 0 ? "\n\n".sizeof($missingCharacters)." characters were not found (".implode(",", $missingCharacters).")" : ''));
				}
				return Redirect::to("/dashboard");
			} else {
				return Redirect::to("/dashboard/storyteller/session/experience/$session->id");
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find session.']);
		}
	}

	public function awardBiographyExperience() {
		$character = Character::find(Input::get('id'));
		if($character) {
			$type = Input::get('type');
			if($type == "questionnaire") {
				$characterExperienceRow = CharacterBiographyExperience::firstOrNew(['character_id' => $character->id]);
				if(!$characterExperienceRow->questionnaire_xp) {
					$characterExperienceRow->questionnaire_xp = true;
					$characterExperienceRow->save();
					$character->awardExperience(1);
					$character->save();
					$character->owner->sendMessage(null, 	"Questionnaire Experience Awarded", "The Storytellers have awarded your character ".$character->name." 1 Experience for answering".
															" the character questionnaire.\n\nThanks for fleshing out your character. Please watch the thread that has been started in the".
															" Character Backgrounds forum for replies from the Storytellers; if they have any questions or concerns on your biography,".
															" they will let you know there.\n\nThanks,\nThe Storytellers");
					return Redirect::to('/dashboard/storyteller/experience/biographies');
				} else {
					return Response::json(['success' => false, 'message' => 'Questionnaire experience has already been awarded.']);							
				}
			} else if ($type == "backstory") {
				$characterExperienceRow = CharacterBiographyExperience::firstOrNew(['character_id' => $character->id]);
				if(!$characterExperienceRow->backstory_xp) {
					$characterExperienceRow->backstory_xp = true;
					$characterExperienceRow->save();
					$character->awardExperience(1);
					$character->save();
					$character->owner->sendMessage(null, 	"Backstory Experience Awarded", "The Storytellers have awarded your character ".$character->name." 1 Experience for providing".
															" a character backstory.\n\nThanks for fleshing out your character. Please watch the thread that has been started in the".
															" Character Backgrounds forum for replies from the Storytellers; if they have any questions or concerns on your biography,".
															" they will let you know there.\n\nThanks,\nThe Storytellers");
					return Redirect::to('/dashboard/storyteller/experience/biographies');
				} else {
					return Response::json(['success' => false, 'message' => 'Questionnaire experience has already been awarded.']);							
				}
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid type.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function awardJournalExperience() {
		$character = Character::find(Input::get('id'));
		if($character) {
			$date = new DateTime;
			$date->setTimestamp(intval(Input::get('month')));
			//Check to make sure we haven't already awarded XP for this month.
			if(!CharacterJournalExperience::where('character_id', $character->id)->whereRaw('MONTH(date) = ?', [date('m', $date->getTimestamp())])->whereRaw('YEAR(date) = ?', [date('Y', $date->getTimestamp())])->exists()) {
				$journal = new CharacterJournalExperience;
				$journal->character_id = $character->id;
				$journal->date = $date;
				$journal->save();
				$character->awardExperience(1);
				$character->save();
				$character->owner->sendMessage(null, "Journal Experience awarded for ".$date->format('F Y'), "The Storytellers have awareded your character ".$character->name. " 1 Experience for your ".
												 $date->format('F Y')." journal.\n\n If you have any questions, please post in the \"General Messages\" forum.\n\nThanks,\nThe Storytellers");
				return Redirect::to("/dashboard/storyteller/experience/journal");
			} else {
				return Response::json(['success' => false, 'message' => 'Experience already awarded for this month']);
			}

		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function awardDiablerieExperience() {
		//This is functionally the same as the journal XP, except it grants 2 and sends a different message.
		$character = Character::find(Input::get('id'));
		if($character) {
			$date = new DateTime;
			$date->setTimestamp(intval(Input::get('month')));
			//Check to make sure we haven't already awarded XP for this month.
			if(!CharacterDiablerieExperience::where('character_id', $character->id)->whereRaw('MONTH(date) = ?', [date('m', $date->getTimestamp())])->whereRaw('YEAR(date) = ?', [date('Y', $date->getTimestamp())])->exists()) {
				$journal = new CharacterDiablerieExperience;
				$journal->character_id = $character->id;
				$journal->date = $date;
				$journal->save();
				$character->awardExperience(2);
				$character->save();
				$character->owner->sendMessage(null, "Diablerie Experience awarded for ".$date->format('F Y'), "The Storytellers have awareded your character ".$character->name. " 2 Experience for ".
													 "diablerizing in ".$date->format('F Y').".\n\n If you have any questions, please post in the \"General Messages\" forum.\n\nThanks,\nThe Storytellers");
				return Redirect::to("/dashboard/storyteller/experience/diablerie");
			} else {
				return Response::json(['success' => false, 'message' => 'Experience already awarded for this month']);
			}

		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function awardCharacterExperience() {
		$character = Character::find(Input::get('id'));
		if($character) {
			$amount = Input::get("amount");
			$character->awardExperience($amount);
			$message = Input::get("message");
			$character->save();			
			$character->owner->sendMessage(Auth::user()->id, "Experience awarded to ".$character->name, $character->name." has been awarded ".$amount. " Experience.".
										  ($message ? "<br><br>".$message : ""));
			return Redirect::to('dashboard/storyteller/characters');
		} else {
			return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
		}
	}

	public function toggleNPCStatus($id) {
		if(Auth::user()->isStoryteller()) {
			$character = Character::find($id);
			if($character) {
				$character->is_npc = $character->is_npc ? '0' : '1';
				$character->save();
				return Redirect::to('dashboard/storyteller/characters');
			} else {
				return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}
	}

	public function toggleActiveStatus($id) {
		if(Auth::user()->isStoryteller()) {
			$character = Character::find($id);
			if($character) {
				$character->active = $character->active ? '0' : '1';
				$character->save();
				return Redirect::to('dashboard/storyteller/characters');
			} else {
				return Response::json(['success' => false, 'message' => 'Unable to find character.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}
	}
	
	public function saveForum($id = -1) {
		if(Auth::user()->isStoryteller()) {
			$forum = Forum::findOrNew($id);
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
			$forum->position = Input::get("position");
			$forum->list_header = trim(Input::get("list-header"));
			$forum->post_header = trim(Input::get("post-header"));	
			$forum->thread_template = trim(Input::get("thread-template"));				
			$forum->save();
			Cache::flush(); 
			return Redirect::to('dashboard/storyteller/manage/forums');
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}
	}
	
	public function deleteForum($id) {
		if(Auth::user()->isStoryteller()) {
			$forum = Forum::find($id);
			if($forum) {
				$forum->delete();
				Cache::flush(); 
				return Redirect::to('dashboard/storyteller/manage/forums');
			} else {
				return Response::json(['success' => false, 'message' => 'Unable to find forum.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}
	public function restoreForum($id) {
		if(Auth::user()->isStoryteller()) {
			$forum = Forum::withTrashed()->find($id);
			if($forum) {
				$forum->restore();
				Cache::flush(); 
				return Redirect::to('dashboard/storyteller/manage/forums');
			} else {
				return Response::json(['success' => false, 'message' => 'Unable to find forum.']);		
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}

	public function grantCharacterForumPermission($id) {
		if(Auth::user()->isStoryteller()) {
			$forum = Forum::find($id);
			$character = Character::find(Input::get('character'));
			if($forum != null && $character != null) {
				if(!ForumCharacterPermission::where(['character_id' => $character->id, 'forum_id' => $id])->exists()) {
					$permission = new ForumCharacterPermission;
					$permission->character_id = $character->id;
					$permission->forum_id = $forum->id;
					$permission->save();
					Cache::flush(); 
					return Redirect::to("dashboard/storyteller/manage/forums/$id/characters");
				} else {
					return Response::json(['success' => false, 'message' => 'Permission already exists']);		
				}
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);					
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}

	public function removeCharacterForumPermission($id) {
		if(Auth::user()->isStoryteller()) {
			$forum = Forum::find($id);
			$character = Character::find(Input::get('character'));
			if($forum != null && $character != null) {
				ForumCharacterPermission::where(['character_id' => $character->id, 'forum_id' => $id])->delete();
				Cache::flush(); 
				return Redirect::to("dashboard/storyteller/manage/forums/$id/characters");	
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);					
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}
	
	public function createPosition() {
		$name = Input::get("name");
		if($name) {
			$position = new RulebookPosition;
			$position->name = $name;
			$position->save();
			return Redirect::to('/dashboard/storyteller/manage/positions');
		} else {
			return "Invalid name.";
		}
	}	

	public function deletePosition() {
		$id = Input::get("id");
		RulebookPosition::find($id)->delete();
		return Redirect::to('/dashboard/storyteller/manage/positions');
	}
	
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
	
	public function grantCharacterPosition($id) {
		if(Auth::user()->isStoryteller()) {
			$position = RulebookPosition::find(Input::get("position"));
			$character = Character::find($id);
			if($position != null && $character != null) {
				if(!CharacterPosition::where(['character_id' => $character->id, 'position_id' => $position->id])->exists()) {
					$newPosition = new CharacterPosition;
					$newPosition->character_id = $character->id;
					$newPosition->position_id = $position->id;
					$newPosition->save();
					return Redirect::to("dashboard/storyteller/character/$id/positions");
				} else {
					return Response::json(['success' => false, 'message' => 'Character already has this position.']);		
				}
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);					
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}

	public function removeCharacterPosition($id) {
		if(Auth::user()->isStoryteller()) {
			$position = RulebookPosition::find(Input::get("position"));
			$character = Character::find($id);
			if($position != null && $character != null) {
				CharacterPosition::where(['character_id' => $character->id, 'position_id' => $position->id])->delete();
				return Redirect::to("dashboard/storyteller/character/$id/positions");	
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);					
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}
	
	public function transferExperience($id) {
		if(Auth::user()->isStoryteller()) {
			$from = Character::find(Input::get("from"));
			$to = Character::find($id);
			if($from != null && $to != null) {
				$to->experience += $from->availableExperience();
				$from->experience -= $from->availableExperience();
				$to->save();
				$from->save();
				Cache::forget("character-experience-".$to->id);
				Cache::forget("character-experience-".$from->id);				
				return Redirect::to("dashboard/storyteller/characters");
			} else {
				return Response::json(['success' => false, 'message' => 'Invalid data.']);					
			}
		} else {
			return Response::json(['success' => false, 'message' => 'Invalid permissions.']);					
		}	
	}

	//This is the only method in this class that is available to non-STs
	public function contactStorytellers() {
		$name = Input::get("name");
		$email = Input::get("email");
		$subject = Input::get("subject");
		$message = Input::get("message");
		$validator = Validator::make(
			['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message],
			['name' => 'required', 'email' => 'required|email', 'subject' => 'required', 'message' => 'required']);
		if($validator->passes()) {
			foreach(User::listStorytellers() as $st) {
				$st->sendMessage(null, 	"Contact the STs Form Message", "The following message was sent via the Contact the Storytellers form.<br><br><b>Name: </b> $name<br><b>Email: </b>".
										"<a href='mailto:$email'>$email</a><br><b>Subject: </b>$subject<br><br>$message"); 
			}
			return View::make('/contact')->with(['response' => true]);
		} else {
			return Redirect::to('/contact')->withErrors($validator);
		}
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
			$data["merits"][] = ["id" => $merit_id, "display" => Input::get("merits-enabled-".$merit_id) ? "on" : "off", "highlight" => Input::get("merits-highlights-".$merit_id)];
		}
		foreach(Input::get("flaws-ids") as $index => $flaw_id) {
			$data["flaws"][] = ["id" => $flaw_id, "display" => Input::get("flaws-enabled-".$flaw_id) ? "on" : "off", "highlight" => Input::get("flaws-highlights-".$flaw_id)];
		}
		foreach(Input::get("derangements-ids") as $index => $derangement_id) {
			$data["derangements"][] = [	"id" => $derangement_id, "display" => Input::get("derangements-enabled-".$derangement_id) ? "on" : "off", 
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