<?php
/* 
	THESE TESTS RELY ON THE IDEA THAT TREMERE HAVE THE FOLLOWING DISCIPLINES:
		- AUSPEX
		- DOMINATE
		- THAUMATURGY
		
		IF THIS CHANGES, THESE TESTS WILL BREAK. 
		YOU CAN CORRECT THEM BY HARDWIRING IN NEW DISCIPLINE IDS.
*/
class GeneratorTest extends TestCase {
	
	private static $hasSetup = false;
	private static $instance = null;
	
	private static $character = null;
	private static $version = null;
	
	private static $player = null;
	private static $storyteller = null;
	
	public function setUp() {
		if(!self::$hasSetup) {
			echo 'Doing initial setup...';
			parent::setUp();
			DB::beginTransaction();
			self::$character = new Character;
			self::$character->user_id = User::first()->id;
			self::$character->name = "UNIT TEST CHARACTER";
			self::$character->save();
			self::$version = CharacterVersion::createNewVersion(self::$character);
			self::$player = new User;
			self::$storyteller = User::listStorytellers()->first();
			self::$instance = $this;
			self::$hasSetup = true;
		}
	}
	
	public static function tearDownAfterClass() {
		echo 'Doing final teardown...';
		parent::tearDownAfterClass();
		self::$version->rollback();
		self::$character->delete();
		self::$player->delete();
		DB::rollback();
	}
	
	public function testSect() {
		self::$version->setSect(RulebookSect::find(1), RulebookSect::find(2));
		$this->assertEquals(1, self::$character->sect(self::$version->version)->first()->sect_id);
		$this->assertEquals(2, self::$character->sect(self::$version->version)->first()->hidden_id);		
	}
	
	public function testClan() {
		self::$version->setClan(RulebookClan::where('name', 'Tremere')->first(), RulebookClan::find(2));
		$this->assertEquals(RulebookClan::where('name', 'Tremere')->first()->id, self::$character->clan(self::$version->version)->first()->clan_id);
		$this->assertEquals(2, self::$character->clan(self::$version->version)->first()->hidden_id);		
	}
	
	public function testClanOptions() {
		self::$version->setClanOptions("Thaumaturgy", null, null);
		$clanOptionData = self::$character->clanOptions(self::$version->version)->first();
		$this->assertEquals('Thaumaturgy', $clanOptionData->option1);
		$this->assertEquals(null, $clanOptionData->option2);
		$this->assertEquals(null, $clanOptionData->option3);		
	}

	public function testWillpower() {
		self::$version->setWillpower(4, 2);
		$willpowerData = self::$character->willpower(self::$version->version)->first();
		$this->assertEquals(4, $willpowerData->willpower_total);
		$this->assertEquals(2, $willpowerData->willpower_current);
	}
	
	public function testNature() {
		self::$version->setNature(RulebookNature::find(1));
		$this->assertEquals(1, self::$character->nature(self::$version->version)->first()->nature_id);
	}
	
	public function testFreeAttributes() {
		$this->assertCostDifference(0, function() {
			self::$version->setAttributes(3, 5, 7);
		});
	}
	
	public function testPlayerPurchaseAttribute() {
		self::$version->setEditingUser(self::$player);	
		$this->assertCostDifference(1, function() {
			self::$version->setAttributes(3, 5, 8);
		});
	}
	
	public function testStorytellerPurchaseAttribute() {
		self::$version->setEditingUser(self::$storyteller);		
		$this->assertCostDifference(0, function() {
			self::$version->setAttributes(3, 5, 8);
		});
	}
	
	public function testFreeAbilities() {
		self::$version->setEditingUser(self::$player);		
		$this->assertCostDifference(0, function() {
			self::$version->addAbility(RulebookAbility::first(), 5);
		});
	}
	
	public function testPlayerPurchaseAbility() {
		self::$version->setEditingUser(self::$player);		
		$this->assertCostDifference(1, function() {
			self::$version->addAbility(RulebookAbility::skip(1)->first(), 1);
		});
	}
	
	public function testStorytellerPurchaseAbility() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addAbility(RulebookAbility::skip(1)->first(), 1);
		});
	}
	
	public function testCustomAbility() {
		self::$version->setEditingUser(self::$player);		
		$this->assertCostDifference(1, function() {
			self::$version->addAbility(null, 1, 'Custom Ability');
		});
	}
	
	public function testPlayerAddSpecialization() {
		self::$version->setEditingUser(self::$player);		
		$this->assertCostDifference(1, function() {
			self::$version->addAbilityWithSpecialization(RulebookAbility::first(), 1, "Test Specialization");
		});
	}

	public function testStorytellerAddSpecialization() {
		self::$version->setEditingUser(self::$storyteller);		
		$this->assertCostDifference(0, function() {
			self::$version->addAbilityWithSpecialization(RulebookAbility::skip(1)->first(), 1, "Test Specialization");
		});
	}
		
	public function testFreeBackgrounds() {
		self::$version->setEditingUser(self::$player);		
		$this->assertCostDifference(0, function() {
			self::$version->addBackground(RulebookBackground::where('name', '!=', 'Generation')->first(), 5);
		});
	}
	
	public function testPlayerPurchaseBackground() {
		self::$version->setEditingUser(self::$player);		
		$this->assertCostDifference(1, function() {
			self::$version->addBackground(RulebookBackground::where('name', '!=', 'Generation')->skip(1)->first(), 1);
		});
	}
	
	public function testStorytellerPurchaseBackground() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addBackground(RulebookBackground::where('name', '!=', 'Generation')->skip(1)->first(), 1);
		});
	}
	
	public function testPlayerPurchaseGeneration() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(1, function() {
			self::$version->addBackground(RulebookBackground::where('name', 'Generation')->first(), 1);
		});
		$this->assertCostDifference(1, function() {
			self::$version->addBackground(RulebookBackground::where('name', 'Generation')->first(), 2);
		});
		$this->assertCostDifference(2, function() {
			self::$version->addBackground(RulebookBackground::where('name', 'Generation')->first(), 3);
		});	
		$this->assertCostDifference(4, function() {
			self::$version->addBackground(RulebookBackground::where('name', 'Generation')->first(), 4);
		});	
		$this->assertCostDifference(8, function() {
			self::$version->addBackground(RulebookBackground::where('name', 'Generation')->first(), 5);
		});
	}
	
	public function testStorytellerPurchaseGeneration() {
		self::$version->setEditingUser(self::$storyteller);
		foreach([1, 2, 3, 4, 5] as $index) {
			$this->assertCostDifference(0, function() use ($index) {
				self::$version->addBackground(RulebookBackground::where('name', 'Generation')->first(), $index);
			});
		}
	}
	
	public function testPlayerPurchaseInClanDiscipline() {
		self::$version->setEditingUser(self::$player);
		//First two are free.
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 1);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 2);
		});
		$this->assertCostDifference(6, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 3);
		});
		$this->assertCostDifference(6, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 4);
		});
		$this->assertCostDifference(9, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 5);
		});								
	}
	
	public function testStorytellerPurchaseInClanDiscipline() {
		self::$version->setEditingUser(self::$storyteller);
		CharacterDiscipline::character(self::$character->id)->version(self::$version->version)->delete();		
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 1);
		});
		
		CharacterDiscipline::character(self::$character->id)->version(self::$version->version)->delete();		
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 2);
		});
		
		CharacterDiscipline::character(self::$character->id)->version(self::$version->version)->delete();				
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 3);
		});
		
		CharacterDiscipline::character(self::$character->id)->version(self::$version->version)->delete();				
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 4);
		});
		
		CharacterDiscipline::character(self::$character->id)->version(self::$version->version)->delete();				
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(1), 5);
		});								
	}
	
	public function testPlayerPurchaseOutOfClanBasicDiscipline() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(3, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 1);
		});
		$this->assertCostDifference(3, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 2);
		});
		$this->assertCostDifference(7, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 3);
		});
		$this->assertCostDifference(7, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 4);
		});
		$this->assertCostDifference(11, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 5);
		});								
	}
	
	public function testStorytellerPurchaseOutOfClanBasicDiscipline() {
		self::$version->setEditingUser(self::$storyteller);
		self::$version->updateDiscipline(RulebookDiscipline::find(6), 0);
		
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 1);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 2);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 3);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 4);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(6), 5);
		});								
	}
	
	public function testPlayerPurchaseOutOfClanAdvancedDiscipline() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(4, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 1);
		});
		$this->assertCostDifference(4, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 2);
		});
		$this->assertCostDifference(8, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 3);
		});
		$this->assertCostDifference(8, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 4);
		});
		$this->assertCostDifference(12, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 5);
		});								
	}
	
	public function testStorytellerPurchaseOutOfClanAdvancedDiscipline() {
		self::$version->setEditingUser(self::$storyteller);
		self::$version->updateDiscipline(RulebookDiscipline::find(10), 0);		
		
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 1);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 2);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 3);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 4);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(10), 5);
		});								
	}
	
	public function testPlayerPurchaseHardPath() {
		self::$version->setEditingUser(self::$player);
		//This is another free basic.
		$this->assertCostDifference(1, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 1, 21);
		});
		$this->assertCostDifference(4, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 2, 21);
		});
		$this->assertCostDifference(7, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 3, 21);
		});
		$this->assertCostDifference(7, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 4, 21);
		});
		$this->assertCostDifference(10, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 5, 21);
		});								
	}

	public function testStorytellerPurchaseHardPath() {
		self::$version->setEditingUser(self::$storyteller);
		self::$version->updateDiscipline(RulebookDiscipline::find(4), 0, 21);
		//This is another free basic.
		$this->assertCostDifference(-3, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 1, 21);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 2, 21);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 3, 21);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 4, 21);
		});
		$this->assertCostDifference(0, function() {
			self::$version->updateDiscipline(RulebookDiscipline::find(4), 5, 21);
		});								
	}
	
	public function testPlayerPurchaseRitual() {
		self::$version->setEditingUser(self::$player);
		//The first one should be free
		$this->assertCostDifference(0, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Basic')->first()->id);
		});
		$this->assertCostDifference(2, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Basic')->skip(1)->first()->id);
		});		
		$this->assertCostDifference(4, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Intermediate')->first()->id);
		});		
		$this->assertCostDifference(6, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Advanced')->first()->id);
		});						
	}
	
	public function testStorytellerPurchaseRitual() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Basic')->skip(2)->first()->id);
		});		
		$this->assertCostDifference(0, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Intermediate')->skip(1)->first()->id);
		});		
		$this->assertCostDifference(0, function() {
			self::$version->addRitual(RulebookRitual::where('group', 'Advanced')->skip(1)->first()->id);
		});						
	}
	
	public function testPlayerPurchaseVirtue() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(3, function() {
			self::$version->updatePath(RulebookPath::find(1), 5, 4, 5, 2);
		});
	}
	
	public function testPlayerPurchaseDerangement() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(-2, function() {
			self::$version->addDerangement(RulebookDerangement::first());
		});
	}
	
	public function testStorytellerPurchaseDerangement() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addDerangement(RulebookDerangement::skip(1)->first());
		});
	}
		
	public function testStorytellerPurchaseVirtue() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->updatePath(RulebookPath::find(1), 5, 4, 5, 3);
		});
	}
	
	public function testPlayerPurchaseMerit() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(1, function() {
			self::$version->addMerit(RulebookMerit::where('cost', 1)->first());
		});
	}
	
	public function testStorytellerPurchaseMerit() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addMerit(RulebookMerit::where('cost', 1)->skip(1)->first());
		});
	}

	public function testPlayerPurchaseFlaw() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(-1, function() {
			self::$version->addFlaw(RulebookFlaw::where('cost', 1)->first());
		});
	}
	
	public function testStorytellerPurchaseFlaw() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addFlaw(RulebookFlaw::where('cost', 1)->skip(1)->first());
		});
	}
		
	public function testPlayerPurchaseElderPower() {
		self::$version->setEditingUser(self::$player);
		$this->assertCostDifference(12, function() {
			self::$version->addElderPower(["id" => null, "discipline" => 1, "name" => "Test Elder", "description" => "Test Elder Desc"]);
		});
	}
	
	public function testStorytellerPurchaseElderPower() {
		self::$version->setEditingUser(self::$storyteller);
		$this->assertCostDifference(0, function() {
			self::$version->addElderPower(["id" => null, "discipline" => 1, "name" => "Test Elder 2", "description" => "Test Elder Desc 2"]);
		});
	}
			
	public function testPlayerPurchaseComboDiscipline() {
		self::$version->setEditingUser(self::$player);
		$ability1 = RulebookDisciplineRank::where('name', 'Aura Perception')->first();
		$ability2 = RulebookDisciplineRank::where('name', 'Telepathy')->first();
		$this->assertCostDifference(9, function() use ($ability1, $ability2) {

			self::$version->addComboDiscipline(["id" => null, "name" => "Test Combo", "option1" => $ability1->id, 
																					"option2" => $ability2->id, "option3" => null, "description" => "Test Combo Desc"]);
		});
	}
	
	public function testStorytellerPurchaseComboDiscipline() {
		self::$version->setEditingUser(self::$storyteller);
		$ability1 = RulebookDisciplineRank::where('name', 'Aura Perception')->first();
		$ability2 = RulebookDisciplineRank::where('name', 'Telepathy')->first();
		$this->assertCostDifference(0, function() use ($ability1, $ability2) {

			self::$version->addComboDiscipline(["id" => null, "name" => "Test Combo 2", "option1" => $ability1->id, 
																					"option2" => $ability2->id, "option3" => null, "description" => "Test Combo Desc 2"]);
		});
	}
		
	protected function assertCostDifference($difference, $method) {
		$preCost = @self::$character->getExperienceCost(self::$version->version);
		call_user_func($method);
		$postCost = self::$character->getExperienceCost(self::$version->version);
		$this->assertEquals($difference, $postCost - $preCost);
	}
	
}