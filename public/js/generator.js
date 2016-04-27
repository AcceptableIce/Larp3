function chargenVM() {
		//Blank character sheet
		var self = this;
		self.characterSheet = {
			name: ko.observable(""),
			sect: {
				selected: ko.observable(undefined),
				displaying: ko.observable(undefined)
			},
			clan: {
				selected: ko.observable(undefined),
				displaying: ko.observable(undefined)
			},
			willpower: {
				traits: ko.observable(2),
				dots: ko.observable(4)
			},
			nature: ko.observable(undefined),
			attributes: ko.observableArray([3,3,3]),
			abilities: ko.observableArray([]),
			disciplines: ko.observableArray([]),
			elderPowers: ko.observableArray([]),
			comboDisciplines: ko.observableArray([]),
			rituals: ko.observableArray([]),
			backgrounds: ko.observableArray([]),
			path: ko.observable(undefined),
			virtues: ko.observableArray([1,1,1,1]),
			derangements: ko.observableArray([]),
			merits: ko.observableArray([]),
			flaws: ko.observableArray([]),
			hasDroppedMorality: ko.observable(false),
			clanOptions: [ko.observable(), ko.observable(), ko.observable()]
		};

		self.customAbility = ko.observable("");

		self.activeSect = ko.observable();
		self.activeClan = ko.observable();
		self.activeNature = ko.observable();
		self.activeAbility = ko.observable();
		self.activeDiscipline = ko.observable();
		self.activeDiscipline.subscribe(function(oldVal) {
			if(oldVal && (oldVal.Text == "Necromancy" || oldVal.Text == "Thaumaturgy")) {
				self.activeDisciplinePath("");
			}
		}, this, 'beforeChange')
		self.activeDisciplinePath = ko.observable();
		self.activeBackground = ko.observable();
		self.activePath = ko.observable();
		self.activeDerangement = ko.observable();
		self.activeDerangementData = ko.computed(function() {
			return self.activeDerangement() ? self.getDerangementById(self.activeDerangement()) : undefined;
		});
		self.activeMerit = ko.observable();
		self.activeFlaw = ko.observable();
		self.activeRitual = ko.observable();
		self.activeRitualData = ko.computed(function() {
			return self.activeRitual() ? self.getRitualById(self.activeRitual().Value) : undefined;		
		});

		self.activeTab = ko.observable("sect");
		self.editingId = ko.observable(-1);
		self.editingVersion = ko.observable(0);
		self.approvedVersion = ko.observable(0);
		self.isStoryteller = ko.observable(false);

		self.versionComment = ko.observable();

		self.rulebook = {
			sects: ko.observableArray([]),
			clans: ko.observableArray([]),
			natures: ko.observableArray([]),
			abilities: ko.observableArray([]),
			disciplines: ko.observableArray([]),
			backgrounds: ko.observableArray([]),
			paths: ko.observableArray([]),
			merits: ko.observableArray([]),
			flaws: ko.observableArray([]),
			rituals: ko.observableArray([]),
			customAbilities: ko.observableArray([]),
			elderPowers: ko.observableArray([]),
			comboDisciplines: ko.observableArray([]),
			customRituals: ko.observableArray([])
		};

		self.modal = {
			title: ko.observable(""),
			body: ko.observable(""),
			mode: ko.observable(0),
			callback: null
		}

		self.inputModal = {
			title: ko.observable(""),
			body: ko.observable(""),
			placeholder: ko.observable(""),
			callback: null,
			value: ko.observable("")
		}

		self.elderModal = {
			discipline: ko.observable(null),
			name: ko.observable(""),
			description: ko.observable("")
		}

		self.ritualModal = {
			type: ko.observable(""),
			name: ko.observable(""),
			description: ko.observable("")
		}

		self.comboModal = {
			name: ko.observable(""),
			description: ko.observable(""),
			option1: ko.observable(null),
			option2: ko.observable(null),
			option3: ko.observable(null)
		}
		

 		// Clan options oh no
		self.clanOptionValues = {
			brujah: [ko.observable()],
			caitiff: [ko.observable(), ko.observable(), ko.observable()],
			malkavian: [ko.observable(), ko.observable()],
			toreador: [ko.observable(), ko.observable(), ko.observable(), ko.observable()],
			ventrue: [ko.observable(), ko.observable()],
			lasombra: [ko.observable(),  ko.observable()],
			ravnos: [ko.observable(), ko.observable()],
			daughters: [ko.observable(), ko.observable()],
			setites: [ko.observable()],
			giovanni: [ko.observable(), ko.observable()],
			tremere: [ko.observable()]
		}

		self.lockXPUpdate = false;
		self.lockClanUpdate = false;
		self.lockSectUpdate = false;
		var update = 0;

		var loadingMessages = [
			"Disabling Salubri...",
			"Rewording Rulebook...",
			"Deciding Which Message To Display Next...",
			"Blood Cursing Assamites...",
			"Poking Werewolves With Sticks...",
			"Testing Limits Of Immortality...",
			"Lowering Skin Tones...",
			"Making Everything A Bit Edgier...",
			"Inviting Hunters...",
			"Popping Aegis...",
			"Modernizing Everything...",
			"Corrupting The Youth With The Teachings Of Set...",
			"Fleshcrafting Up Appearance...",
			"Regretting Everything...",
			"Asking Malkov For Advice...",
			"Generally Disliking Tremere...",
			"Blatantly Disregarding The Masquerade...",
			"Dubiously Lowering Generation...",
			"Encoding Umlauts...",
			"Rewriting Embarrassing Memories...",
			"Nerfing Elders...",
			"Controlling The World Over Downtime...",
			"Frantically Writing Loading Messages...",
			"Deciding Which Character To Kill Next...",		
			"Retesting With Computers...",
			"Reticulating Splines..."
		];

		var lastMessage = null;
		
		function cycleLoadingMessages() {
			var selectedMessage = loadingMessages[Math.floor(Math.random() * loadingMessages.length)];
			while(selectedMessage == lastMessage) selectedMessage = loadingMessages[Math.floor(Math.random() * loadingMessages.length)];
			lastMessage = selectedMessage;
			$(".load-text-content").text(selectedMessage);
			setTimeout(cycleLoadingMessages, 2500);
		}

		cycleLoadingMessages();

		self.lockAndClearClanOptions = function() {
			if(self.lockClanUpdate) return;
			self.lockClanUpdate = true;
			self.lockXPUpdate = true;
			self.clearClanOptions();
			self.lockClanUpdate = false;
		}

		self.unlockAndUpdateClanOptions = function() {
			if(self.lockClanUpdate) return;
			self.lockClanUpdate = true;
			self.updateClanOptions();
			self.lockXPUpdate = false;
			self.lockClanUpdate = false;
		}


		self.lockAndClearSectOptions = function() {
			if(self.lockSectUpdate) return;

			self.lockSectUpdate = true;
			self.lockXPUpdate = true;
			self.clearSectOptions();
			self.lockSectUpdate = false;
		}

		self.unlockAndUpdateSectOptions = function() {
			if(self.lockSectUpdate) return;
			self.lockSectUpdate = true;
			self.updateSectOptions();
			self.lockXPUpdate = false;
			self.lockSectUpdate = false;
		}

		//IDK why this has to be seperate
		self.derangements = ko.observableArray([]);
		
		self.totalExperience = ko.observable(10);
		
		self.experienceSpent = ko.observable(0);
		self.updateExperienceSpent = function() {

			if(self.lockXPUpdate) return;

			$.ajax({
				url: "/characters/cost",
				type: 'post',
				data: {
					characterId: preloaded.characterId,
					sheet: self.buildCharacterSheet(),
					comment: self.versionComment(),
					newRituals: self.newRituals,
					review: false //workaround
				},
				success: function(data) {
					if(data.success) {
						self.experienceSpent(data.cost);
					} else {
						if(data.message.indexOf("Serialization failure") != -1) {
							toastr.error("You're moving too quickly for the server to calculate experience.");
						} else {
							self.modal.title("Something went wrong.");
							self.modal.body("<p>" + data.message + "</p>");
							$('#main-modal').foundation('reveal', 'open');
						}
					}
				}
			})
		}

		ko.observableArray.fn.refresh = function() {
			var temp = this();
			this([]);
			this(temp);
		}

		self.hasPaths = ko.computed(function() { 
			return self.activeDiscipline() && self.getDisciplineById(self.activeDiscipline().Value).paths;
		});

		var brujahAssociativeAbilities = {
			"University": "Academics",
			"Politics": "Politics",
			"Neighborhood": "Streetwise"
		}

		self.updateSectOptions = function() {
			var sect = self.characterSheet.sect.selected();	
			if(sect) {
				switch(sect.name) {
					case 'Camarilla':
						self.giveClanOptionBackgroundByName('Kindred Lore', 2);
						self.giveClanOptionBackgroundByName('Camarilla Lore', 2);
						break;
					case 'Sabbat':
						self.giveClanOptionBackgroundByName('Kindred Lore', 2);
						self.giveClanOptionBackgroundByName('Sabbat Lore', 2);
						break;
					case 'Independents':
						self.giveClanOptionBackgroundByName('Kindred Lore', 3);
						break;
				}
			}
		}

		//Malkavians trigger the "explain your derangment" prompt when they change 
		//disciplines if not accounted for.
		var previousMalkavianData = null;

		self.updateClanOptions = function() {
			var clan = self.characterSheet.clan.selected();	
			if(clan) {	
				switch(clan.name) {
					case "Brujah":
						self.giveClanOptionAbilityByName(brujahAssociativeAbilities[self.clanOptionValues.brujah[0]()], 1);						
						self.giveClanOptionBackgroundByName(self.clanOptionValues.brujah[0](), 1);
						break;
					case "Country Gangrel":
					case "City Gangrel":						
					case "Gangrel":
						self.giveClanOptionAbilityByName("Animal Ken", 1);
						self.giveClanOptionAbilityByName("Survival", 1);
						self.giveClanOptionMeritByName("Inoffensive to Animals");
						break;
					case "Malkavian":
						self.giveClanOptionAbilityByName("Awareness", 1);
						if(previousMalkavianData && previousMalkavianData.data.name == self.clanOptionValues.malkavian[0]()) {
							self.characterSheet.derangements.push(previousMalkavianData);
						} else {
							self.giveClanOptionDerangementByName(self.clanOptionValues.malkavian[0]());	
						}					
						break;
					case "Nosferatu":
						self.giveClanOptionAbilityByName("Stealth", 1);
						self.giveClanOptionAbilityByName("Survival", 1);
						break;
					case "Toreador":
						//Check selection 1 1
						if((self.clanOptionValues.toreador[0]() == "Crafts" || self.clanOptionValues.toreador[0]() == "Perform")) {
							if(self.clanOptionValues.toreador[2]()) {
								if(self.clanOptionValues.toreador[0]() == self.clanOptionValues.toreador[1]() 
								&& self.clanOptionValues.toreador[2]() == self.clanOptionValues.toreador[3]()) {
									self.giveClanOptionCustomAbilityByName(self.clanOptionValues.toreador[0]() + ": " + self.clanOptionValues.toreador[2](), 2);
									break;
								} else {
									self.giveClanOptionCustomAbilityByName(self.clanOptionValues.toreador[0]() + ": " + self.clanOptionValues.toreador[2](), 1);
								}
							}
						} else if(self.clanOptionValues.toreador[0]()) {
							if(self.clanOptionValues.toreador[0]() == self.clanOptionValues.toreador[1]()) {
								self.giveClanOptionAbilityByName(self.clanOptionValues.toreador[0](), 2);	
								break;
							} else {
								self.giveClanOptionAbilityByName(self.clanOptionValues.toreador[0](), 1);								
							}					
						}
						if((self.clanOptionValues.toreador[1]() == "Crafts" || self.clanOptionValues.toreador[1]() == "Perform")) {
							if(self.clanOptionValues.toreador[3]()) {
								self.giveClanOptionCustomAbilityByName(self.clanOptionValues.toreador[1]() + ": " + self.clanOptionValues.toreador[3](), 1);
							}
						} else if(self.clanOptionValues.toreador[1]()) {
							self.giveClanOptionAbilityByName(self.clanOptionValues.toreador[1](), 1);						
						}
						break;
					case "Tremere":
						self.giveClanOptionAbilityByName("Occult", 1);
						self.giveClanOptionBackgroundByName("Occult", 1);
						self.giveClanOptionRitualByName("Rite of Introduction", 1);
						break;
					case "Ventrue":
						self.giveClanOptionBackgroundByName("Resources", 1);
						self.giveClanOptionBackgroundByName(self.clanOptionValues.ventrue[1](), 1);
						break;
					case "Daughters of Cacophony":
						if(self.clanOptionValues.daughters[0]() == "High Society (Influence)") {
							self.giveClanOptionBackgroundByName("High Society", 1);
							self.giveClanOptionCustomAbilityByName("Performance: Singing", 1);
						} else if(self.clanOptionValues.daughters[0]() == "Performance (Ability)") {
							self.giveClanOptionCustomAbilityByName("Performance: Singing", 2);
						}
					break;
					case "Gargoyle":
						self.giveClanOptionAbilityByName("Awareness", 1);
						break;
					case "Lasombra":
						self.giveClanOptionBackgroundByName(self.clanOptionValues.lasombra[0](), 1);
						break;								
					case "Tzimisce":
						self.giveClanOptionAbilityByName("Occult", 1);
						break;	
					case "Ravnos":
						self.giveClanOptionAbilityByName("Streetwise", 1);
						self.giveClanOptionBackgroundByName(self.clanOptionValues.ravnos[1](), 1);
						break;
					case "Giovanni":
						if(self.clanOptionValues.giovanni[0]() == self.clanOptionValues.giovanni[1]()) {
							self.giveClanOptionBackgroundByName(self.clanOptionValues.giovanni[0](), 2);
						} else {
							self.giveClanOptionBackgroundByName(self.clanOptionValues.giovanni[0](), 1);
							self.giveClanOptionBackgroundByName(self.clanOptionValues.giovanni[1](), 1);
						}	
						break;				
					case "Assamite":
						self.giveClanOptionAbilityByName("Melee", 1);	
						self.giveClanOptionAbilityByName("Brawl", 1);	
						break;
					case "Followers of Set":
						self.giveClanOptionAbilityByName("Streetwise", 1);
						self.giveClanOptionBackgroundByName(self.clanOptionValues.setites[0](), 1);
						break;						
				}
				self.characterSheet.abilities.refresh();
				self.characterSheet.backgrounds.refresh();
			}
		}

		self.clearSectOptions = function() {
			var sect = self.characterSheet.sect.selected();	
			if(sect) {
				switch(sect.name) {
					case 'Camarilla':
						self.removeClanOptionBackgroundByName('Kindred Lore', 2);
						self.removeClanOptionBackgroundByName('Camarilla Lore', 2);
						break;
					case 'Sabbat':
						self.removeClanOptionBackgroundByName('Kindred Lore', 2);
						self.removeClanOptionBackgroundByName('Sabbat Lore', 2);
						break;
					case 'Independents':
						self.removeClanOptionBackgroundByName('Kindred Lore', 3);
						break;
				}
			}
		}

		self.clearClanOptions = function() {
			var clan = self.characterSheet.clan.selected();	
			if(clan) {		
				switch(clan.name) {
					case "Brujah":
						console.log('Clear:', self.clanOptionValues.brujah[0]());
						self.removeClanOptionAbilityByName(brujahAssociativeAbilities[self.clanOptionValues.brujah[0]()], 1);						
						self.removeClanOptionBackgroundByName(self.clanOptionValues.brujah[0](), 1);
						break;
					case "Country Gangrel":						
					case "City Gangrel":
					case "Gangrel":
						self.removeClanOptionAbilityByName("Animal Ken", 1);
						self.removeClanOptionAbilityByName("Survival", 1);
						self.removeClanOptionMeritByName("Inoffensive to Animals");
						break;
					case "Malkavian":
						self.removeClanOptionAbilityByName("Awareness", 1);		
						previousMalkavianData = _.findWhere(self.characterSheet.derangements(), function(item) {
							return item.data.name == self.clanOptionValues.malkavian[0]();
						})
						self.removeClanOptionDerangementByName(self.clanOptionValues.malkavian[0]());	
						break;
					case "Nosferatu":
						self.removeClanOptionAbilityByName("Stealth", 1);
						self.removeClanOptionAbilityByName("Survival", 1);
						break;
					case "Toreador":
						if((self.clanOptionValues.toreador[0]() == "Crafts" || self.clanOptionValues.toreador[0]() == "Perform")) {
							if(self.clanOptionValues.toreador[0]() == self.clanOptionValues.toreador[1]() 
							&& self.clanOptionValues.toreador[2]() == self.clanOptionValues.toreador[3]()) {
								self.removeClanOptionCustomAbilityByName(self.clanOptionValues.toreador[0]() + ": " + self.clanOptionValues.toreador[2](), 2);
								break;
							} else {
								self.removeClanOptionCustomAbilityByName(self.clanOptionValues.toreador[0]() + ": " + self.clanOptionValues.toreador[2](), 1);
							}
						} else if(self.clanOptionValues.toreador[0]()) {
							if(self.clanOptionValues.toreador[0]() == self.clanOptionValues.toreador[1]()) {
								self.removeClanOptionAbilityByName(self.clanOptionValues.toreador[0](), 2);
								break;
							} else {
								self.removeClanOptionAbilityByName(self.clanOptionValues.toreador[0](), 1);						
							}						
						}
						if((self.clanOptionValues.toreador[1]() == "Crafts" || self.clanOptionValues.toreador[1]() == "Perform")) {
							self.removeClanOptionCustomAbilityByName(self.clanOptionValues.toreador[1]() + ": " + self.clanOptionValues.toreador[3](), 1);
						} else if(self.clanOptionValues.toreador[1]()) {
							self.removeClanOptionAbilityByName(self.clanOptionValues.toreador[1](), 1);						
						}						
						break;						
					case "Tremere":
						self.removeClanOptionAbilityByName("Occult", 1);
						self.removeClanOptionBackgroundByName("Occult", 1);
						self.removeClanOptionRitualByName("Rite of Introduction", 1);
						break;
					case "Ventrue":
						self.removeClanOptionBackgroundByName("Resources", 1);
						self.removeClanOptionBackgroundByName(self.clanOptionValues.ventrue[1](), 1);
						break;
					case "Daughters of Cacophony":
						if(self.clanOptionValues.daughters[0]() == "High Society (Influence)") {
							self.removeClanOptionBackgroundByName("High Society", 1);
							self.removeClanOptionCustomAbilityByName("Performance: Singing", 1);
						} else if(self.clanOptionValues.daughters[0]() == "Performance (Ability)") {
							self.removeClanOptionCustomAbilityByName("Performance: Singing", 2);
						}
					break;
					case "Gargoyle":
						self.removeClanOptionAbilityByName("Awareness", 1);
						break;
					case "Lasombra":
						self.removeClanOptionBackgroundByName(self.clanOptionValues.lasombra[0](), 1);
						break;	
					case "Tzimisce":
						self.removeClanOptionAbilityByName("Occult", 1);
						break;	
					case "Ravnos":
						self.removeClanOptionAbilityByName("Streetwise", 1);
						self.removeClanOptionBackgroundByName(self.clanOptionValues.ravnos[1](), 1);
						break;
					case "Giovanni":
						if(self.clanOptionValues.giovanni[0]() == self.clanOptionValues.giovanni[1]()) {
							self.removeClanOptionBackgroundByName(self.clanOptionValues.giovanni[0](), 2);
						} else {
							self.removeClanOptionBackgroundByName(self.clanOptionValues.giovanni[0](), 1);
							self.removeClanOptionBackgroundByName(self.clanOptionValues.giovanni[1](), 1);
						}	
						break;	
					case "Assamite":
						self.removeClanOptionAbilityByName("Melee", 1);	
						self.removeClanOptionAbilityByName("Brawl", 1);	
						break;
					case "Followers of Set":
						self.removeClanOptionAbilityByName("Streetwise", 1);
						self.removeClanOptionBackgroundByName(self.clanOptionValues.setites[0](), 1);
						break;				
				}
			}
		}

		self.giveClanOptionDisciplineByName = function(name, slot) {
			if(name && name.length > 0) {
				var discipline = self.getDisciplineByName(name);
				if(discipline) {
					self.characterSheet.clan.selected().disciplines[slot] = discipline;
					self.characterSheet.disciplines([]);
					//Refreshing the rulebook tricks the availableDisciplines computed into recomputing its value,
					//thus learning about our newly selected discipline.
					self.rulebook.disciplines.valueHasMutated();
				} 
			}
		}

		self.giveClanOptionBackgroundByName = function(name, amount) {
			if(name && name.length > 0) {
				var background = self.getBackgroundByName(name);
				if(background) {
					if(self.getBackgroundCount(background.id) == 0) {
						self.addBackground(background.id);
						if(amount > 1) self.tickBackground(background, amount - 1);
					} else {
						self.tickBackground(background, amount);
					}
					self.addLimit("background", background.id, amount);
				} 
			}
		}
		self.removeClanOptionBackgroundByName = function(name, amount) {
			if(name && name.length > 0) {
				var background = self.getBackgroundByName(name);
				if(background) {
					if(self.getBackgroundCount(background.id) == amount) {
						for(var k in self.characterSheet.backgrounds()) {
							var kItem = self.characterSheet.backgrounds()[k];
							if(kItem.id == background.id) {
								self.characterSheet.backgrounds().splice(k, 1);

								//Lazy refresh
								self.characterSheet.backgrounds.refresh();
								k--;
							}
						}
					} else {
						self.tickBackground(background.id, -1 * amount);
					}
					self.removeLimit("background", background.id, amount);
				} 
			}
		}


		self.giveClanOptionAbilityByName = function(name, amount) {
			if(name && name.length > 0) {
				var ability = self.getAbilityByName(name);
				if(ability) {
					if(self.getAbilityCount(ability.id) == 0) {
						self.addAbility(ability.id);
						if(amount > 1) self.tickAbility(ability, amount - 1);
					} else {
						self.tickAbility(ability, amount);
					}
					self.addLimit("ability", ability.id, amount);
				} 
			}
		}

		self.removeClanOptionAbilityByName = function(name, amount) {
			if(name && name.length > 0) {
				var ability = self.getAbilityByName(name);
				if(ability) {
					self.removeLimit("ability", ability.id, amount);
					if(self.getAbilityCount(ability.id) == amount) {
						for(var k in self.characterSheet.abilities()) {
							var kItem = self.characterSheet.abilities()[k];
							if(kItem.id == ability.id) {
								self.characterSheet.abilities().splice(k, 1);

								self.characterSheet.abilities.refresh();
								k--;
							}
						}
					} else {
						self.tickAbility(ability, -1 * amount);
					}
				} 
			}
		}

		self.giveClanOptionCustomAbilityByName = function(name, amount) {
			console.log("Giving custom ability ", name, " (", amount, ")");
			if(name && name.length > 0) {
				var cstId = self.addCustomAbility(name);
				if(amount > 1) self.tickAbility({id: cstId}, amount - 1);
				self.addLimit("ability", cstId, amount);
			}
		}

		self.removeClanOptionCustomAbilityByName = function(name, amount) {
			if(name && name.length > 0) {
				for(var k in self.characterSheet.abilities()) {
					var kItem = self.characterSheet.abilities()[k];
					if(kItem.name == name) {
						self.removeLimit("ability", kItem.id, amount);
						if(kItem.count == amount) {
							self.characterSheet.abilities().splice(k, 1);
							//Lazy refresh
							self.characterSheet.abilities.refresh();
							k--;
						} else {
							self.tickAbility({id: kItem.id}, -1 * amount);
						}
					}
				} 
			}
		}

		self.giveClanOptionMeritByName = function(name) {
			if(name && name.length > 0) {
				var merit = self.getMeritByName(name);
				if(merit) {
					self.addMerit({Value: merit.id});
					self.addLimit("merit", merit.id, 1);
				} 
			}
		}

		self.removeClanOptionMeritByName = function(name) {
			if(name && name.length > 0) {
				var merit = self.getMeritByName(name);
				if(merit) {
					for(var i = 0; i < self.characterSheet.merits().length; i++) {
						var csMerit = self.characterSheet.merits()[i];
						if(csMerit.data.id == merit.id) {
							self.removeLimit("merit", merit.id, 1);					
							self.removeMerit(csMerit);
						}
					}
				} 
			}
		}

		self.giveClanOptionDerangementByName = function(name) {
			if(name && name.length > 0) {
				var derangement = self.getDerangementByName(name);
				if(derangement) {
					self.addDerangement(derangement.id);
					self.addLimit("derangement", derangement.id, 1);
				} 
			}
		}

		self.removeClanOptionDerangementByName = function(name) {
			if(name && name.length > 0) {
				var derangement = self.getDerangementByName(name);
				if(derangement) {
					for(var i = 0; i < self.characterSheet.derangements().length; i++) {
						var csDerangement = self.characterSheet.derangements()[i];
						if(csDerangement.data.id == derangement.id) {
							self.removeLimit("derangement", derangement.id, 1);					
							self.removeDerangement(csDerangement);
						}
					}
				} 
			}
		}

		self.giveClanOptionRitualByName = function(name) {
			if(name && name.length > 0) {
				var ritual = self.getRitualByName(name);
				if(ritual) {
					self.addRitual(ritual.id);
					self.addLimit("ritual", ritual.id, 1);
				} 
			}
		}

		self.removeClanOptionRitualByName = function(name) {
			if(name && name.length > 0) {
				var ritual = self.getRitualByName(name);
				if(ritual) {
					self.removeLimit("ritual", ritual.id, 1);					
					self.removeRitual(ritual.id);
				} 
			}
		}

		self.addLimit = function(type, id, amount, message) {
			self.clanSelectionDependentLimits.push({
				"type": type, 
				"id": id, 
				"amount": amount, 
				"message": message 
			});
			console.log("[Limit] Added limit", {"type" : type, "id": id, "amount": amount});
		}

		self.removeLimit = function(type, id, amount) {
			//Find all limits which match
			var matches = _.where(self.clanSelectionDependentLimits, {type: type, id: id, amount: amount});
			//And take the difference between the returned array and our list.
			self.clanSelectionDependentLimits = _.difference(self.clanSelectionDependentLimits, matches);
		}

		self.selectSect = function(value) {
			self.characterSheet.sect.selected(value);
			self.masqueradeSect(value);
			if(!self.isStoryteller()) {
				self.characterSheet.clan.selected(null);
				self.characterSheet.clan.displaying(null);
			}
			self.characterSheet.disciplines([]);
			self.activeDiscipline(null);

		}

		self.masqueradeSect = function(value) {
			self.characterSheet.sect.displaying(value);
			if(self.characterSheet.sect.selected() === undefined) {
				self.selectSect(value);
			}
		}

		self.selectClan = function(value) {
			self.characterSheet.disciplines([]);
			self.characterSheet.rituals([]);
			self.characterSheet.clan.selected(value);
			self.masqueradeClan(value);		
			self.activeDiscipline(null);	
			self.updateExperienceSpent();
		}

		self.selectNature = function(value) {
			self.characterSheet.nature(value);
		}
		
		self.selectPath = function(value) {
			self.characterSheet.path(value);
			if(self.approvedVersion() == 0) {

				//Zero out the zero-starting paths.
				var virtue1 = value.stats[0];
				var virtue2 = value.stats[1];
				if(virtue1 == "Conviction" || virtue1 == "Instinct") {
					self.characterSheet.virtues()[0] = 0;
				}
				if(virtue2 == "Conviction" || virtue2 == "Instinct") {
					self.characterSheet.virtues()[1] = 0;
				}

				self.characterSheet.virtues.refresh();
			}
		}

		self.virtuePointsSpent = ko.computed(function() {
			var path = self.characterSheet.path();
			var points_spent = 0;
			if(path) {
				var virtue1 = path.stats[0];
				var virtue2 = path.stats[1];
				if(virtue1 == "Conviction" || virtue1 == "Instinct") {
					points_spent++;
				}
				if(virtue2 == "Conviction" || virtue2 == "Instinct") {
					points_spent++;
				}
				points_spent +=  self.characterSheet.virtues()[0] + self.characterSheet.virtues()[1] + self.characterSheet.virtues()[3] - 3;
			}
			return points_spent;
		});

		self.abilityPointsSpent = ko.computed(function() {
			var total = _.reduce(self.characterSheet.abilities(), function(memo, item) { return memo + item.count; }, 0);
			var limits = _.where(self.clanSelectionDependentLimits, {type: "ability"});
			var limitPoints = _.reduce(limits, function(memo, item) { return memo + item.amount; }, 0);
			return total - limitPoints;
		});

		self.backgroundPointsSpent = ko.computed(function() {
			var total = _.reduce(self.characterSheet.backgrounds(), function(memo, item) { return memo + item.count; }, 0);
			var limits = _.where(self.clanSelectionDependentLimits, {type: "background"});
			var limitPoints = _.reduce(limits, function(memo, item) { return memo + item.amount; }, 0);
			return total - limitPoints;
		});

		self.attributePointsSpent = ko.computed(function() {
			return _.reduce(self.characterSheet.attributes(), function(memo, item) { return memo + Number(item); }, 0);
		});

		self.backgroundName = function(data) {
			return data.name + (data.description ? (': ' + data.description) : '') + ' (' + data.count + ')'
		}
		
		self.masqueradeClan = function(value) {
			self.characterSheet.clan.displaying(value);
			if(self.characterSheet.clan.selected() === undefined) {
				self.selectClan(value);
			}
		}

		self.setAttribute = function(type, amount) {
			if(self.approvedVersion() == 0 && (amount + 1) < 3 && !self.isStoryteller()) {
				self.showModal(
					"Invalid Purchase", 
					"Characters must have at least 3 Dots in every Attribute at character creation."
				);
			} else {
				self.characterSheet.attributes()[type] = amount + 1;
				self.characterSheet.attributes.valueHasMutated();
				self.updateExperienceSpent();
			}
		}

		self.addAbility = function(id) {
			var item = self.getAbilityById(id);
			self.characterSheet.abilities.push({ 
				id: item.id, 
				name: item.name, 
				count: 1 
			});
			self.activeAbility(undefined);
			self.updateExperienceSpent();
		}

		var customAbilityId = -1;
		var elderPowerId = -1;
		var comboDisciplineId = -1;
		var customRitualId = -1;

		self.addCustomAbility = function(name) {
			if(name.trim().length == 0) return;
			var ability = _.findWhere(self.characterSheet.abilities(), { name: name });
			if(ability) {
				self.tickAbility(ability, 1);
			} else {
				var id = self.getCustomAbilityId(name);
				self.characterSheet.abilities.push({ 
					id: id, 
					name: name, 
					count: 1 
				});
			}
			self.characterSheet.abilities.refresh();
			self.updateExperienceSpent();	
			return id;		
		}

		self.getCustomAbilityId = function(name) {
			var item = _.findWhere(self.rulebook.customAbilities(), {name: name});
			return item ? item.id : customAbilityId--;
		}
		
		self.getElderPowerId = function(name) {
			var item = _.findWhere(self.rulebook.elderPowers(), {name: name});
			return item ? item.id : elderPowerId--;
		}

		self.getCustomRitualId = function(name) {
			var item = _.findWhere(self.rulebook.customRituals(), {name: name});
			return item ? item.id : customRitualId--;
		}

		self.getComboDisciplineId = function(name) {
			var item = _.findWhere(self.rulebook.comboDisciplines(), {name: name});
			return item ? item.id : comboDisciplineId--;
		}

		self.getElderPowerById = function(id) {
			return _.findWhere(self.rulebook.elderPowers(), {id: id});
		}

		self.getComboDisciplineById = function(id) {
			return _.findWhere(self.rulebook.comboDisciplines(), {id: id});
		}

		self.removeComboDiscipline = function(id) {
			var disciplineSearch = _.where(self.characterSheet.comboDisciplines(), {id: id});
			self.characterSheet.comboDisciplines(_.difference(self.characterSheet.comboDisciplines(), disciplineSearch));
			self.updateExperienceSpent();	
		}

		self.addBackground = function(id) {
			var item = self.getBackgroundById(id);
			if(!self.isStoryteller() && _.findWhere(self.clanSelectionDependentLimits, {type: "background", id: id, amount: 0})) {
				self.showModal(
					"Cannot add Background.", 	
					limit.message ? limit.message : "Your selected Clan Options means you must have at least " + limit.amount + " Dot" + 
						(limit.amount > 1 ? "s" : "") + " of the <i>" + item.name + "</i> Ability."
				);
				return;
			}
			if(item.name == "Ghouls" || item.name == "Mentor") {
				var nameData = item.name == "Ghouls" ? ["Ghoul", "ghoul"] : ["Mentor", "mentor"];
				self.showInputModal(
					"Enter " + nameData[0] + " Name", 
					"Please enter the name of the " + nameData[1] + " you wish to add.", 
					nameData[0] + " name...", 
					function(value) {
						var found = false;
						var background = _.findWhere(self.characterSheet.backgrounds(), {name: item.name, description: value});
						if(background) {
							background.count++;
							self.characterSheet.backgrounds.refresh();
						} else {
							self.characterSheet.backgrounds.push({ id: item.id, name: item.name, description: value, count: 1 });
						}
						self.activeBackground(undefined);
						self.updateExperienceSpent();	
					}
				);
			} else {
				if(item.group == "Influence") {
					var influenceCount = self.getInfluenceCount();
					var influenceLimit = self.getInfluenceLimit();
					if(influenceCount + 1 > influenceLimit && !self.isStoryteller()) {
						self.showModal(
							"Cannot increase Influence.", 
							"You cannot have more Dots of Influence than the total of your " +
									"Attributes plus Dots of Ghouls plus your Dots of Retainers.");
						return;
					}
				}
				self.characterSheet.backgrounds.push({ 
					id: item.id, 
					name: item.name, 
					description: "", 
					count: 1 
				});
				self.activeBackground(undefined);
				self.updateExperienceSpent();	
			}
		
		}
		
		self.addMerit = function(data) {
			var merit = self.getMeritById(data.Value);
			var meritName = merit.name;
			var clanName = self.characterSheet.clan.selected() ? self.characterSheet.clan.selected().name : null;
			if(clanName && !self.isStoryteller()) {
				//Some merits are restricted to certain clans.
				if(meritName == "Gliding" && clanName != "Gargoyle") {
					self.showModal(
						"Cannot purchase Merit.", 
						"Only Gargoyles can have the Merit <i>Gliding</i>."
					);
					return;
				} else if(meritName == "Disembodied Mentor" && clanName != "Malkavian") {
					self.showModal(
						"Cannot purchase Merit.", 
						"Only Malkavians can have the Merit <i>Disembodied Mentor</i>."
					);
					return;
				} else if (meritName == "Calm Heart" && clanName == "Brujah") {
					self.showModal(
						"Cannot purchase Merit.", 
						"Brujah cannot have the Merit <i>Calm Heart</i>."
					);
					return;
				}
			}
			var meritCost = clanName != null && clanName.indexOf("Gangrel") !== -1 && meritName == "Inoffensive to Animals" ? 0 : Number(merit.cost);
			if(self.getMeritTotal() + meritCost <= 7 || self.approvedVersion() > 0 || self.isStoryteller()) {
				if(merit.requires_description == 1) {
					self.showInputModal(
						"Enter Merit Description", 
						"Please enter a short description for this merit.", 
						"Description...", 
						function(value) {
							self.characterSheet.merits.push({
								data: merit, 
								description: value
							});
							self.activeMerit(undefined);
							self.updateExperienceSpent();
						}
					);
				} else {
					self.characterSheet.merits.push({
						data: merit, 
						description: ""
					});
					self.activeMerit(undefined);
					self.updateExperienceSpent();
				}
			} else {
				self.showModal(
					"Invalid Purchase", 
					"You cannot have more than 7 points of Merits at character creation."
				);						
			}
		}
		
		self.addFlaw = function(data) {
			var flaw = self.getFlawById(data.Value);
			var flawName = flaw.name;
			var clanName = self.characterSheet.clan.selected() ? self.characterSheet.clan.selected().name : null;
			if(clanName && !self.isStoryteller()) {
				//Some merits are restricted to certain clans.
				if(flawName == "Flightless" && clanName != "Gargoyle") {
					self.showModal(
						"Cannot purchase Flaw.", 
						"Only Gargoyles can have the Flaw <i>Flightless</i>."
					);
					return;
				} else if((flawName == "Disfigured" || flawName == "Permanent Fangs" || flawName == "Monstrous") && 
									(clanName == "Nosferatu" || clanName == "Gargoyle" || clanName == "Samedi" )) {
					self.showModal(
						"Cannot purchase Flaw.", 
						clanName + (clanName == "Gargoyle" ? "s" : "") + " cannot have the Flaw <i>" + flawName +"</i>."
					);
					return;
				} else if (flawName == "Prey Exclusion" && clanName == "Ventrue") {
					self.showModal(
						"Cannot purchase Flaw.", 
						"Ventrue cannot have the Flaw <i>Prey Exclusion</i>."
					);
					return;
				} else if (flawName == "Cannot Cross the Threshold" && clanName == "Tzimisce") {
					self.showModal(
						"Cannot purchase Flaw.", 
						"Tzimisce cannot have the Flaw <i>Cannot Cross the Threshold</i>."
					);
					return;
				} else if (flawName == "Cast No Reflection" && clanName == "Lasombra") {
					self.showModal(
						"Cannot purchase Flaw.", 
						"Lasombra cannot have the Flaw <i>Cast No Reflection</i>."
					);
					return;
				} else if (flawName == "Grip of the Damned" && clanName == "Giovanni") {
					self.showModal(
						"Cannot purchase Flaw.", 
						"Giovanni cannot have the Flaw <i>Grip of the Damned</i>."
					);
					return;
				}
			}
			console.log(self.getFlawTotal(), flaw.cost);
			if(self.getFlawTotal() + Number(flaw.cost) <= 7 || self.approvedVersion() > 0 || self.isStoryteller()) {
				if(flawName == "Sect Ignorance") {
					var camData = self.getBackgroundByName("Camarilla Lore");
					var sabData = self.getBackgroundByName("Sabbat Lore");
					self.lockXPUpdate = true;

					self.removeLimit("background", camData.id, 2);
					self.tickBackground(camData, -5);
					self.addLimit("background",  camData.id, 0, "The Flaw <i>Sect Ignorance</i> prevent you from owning any Camarilla Lore.");

					self.removeLimit("background", sabData.id, 2);
					self.tickBackground(sabData, -5);
					self.addLimit("background", sabData.id, 0, "The Flaw <i>Sect Ignorance</i> prevent you from owning any Sabbat Lore.");

					self.lockXPUpdate = false;
				} else if(flawName == "Kindred Ignorance") {
					var kindData = self.getBackgroundByName("Kindred Lore");
					self.lockXPUpdate = true;

					self.removeLimit("background", kindData.id, 2);
					self.removeLimit("background", kindData.id, 3);
					self.tickBackground(kindData, -5);
					self.addLimit("background",  kindData.id, 0, "The Flaw <i>Kindred Ignorance</i> prevent you from owning any Kindred Lore.");

					self.lockXPUpdate = false;
				}

				if(flaw.requires_description == 1) {
					self.showInputModal(
						"Enter Flaw Description", 
						"Please enter a short description for this flaw.", 
						"Description...", 
						function(value) {
							self.characterSheet.flaws.push({data: flaw, description: value});
							self.activeFlaw(undefined);
							self.updateExperienceSpent();
						}
					);
				} else {
						self.characterSheet.flaws.push({data: flaw, description: ""});
						self.activeFlaw(undefined);
						self.updateExperienceSpent();
				} 
			} else {
				self.showModal(
					"Invalid Purchase", 
					"You cannot have more than 7 points of Flaws at character creation."
				);				
			}
		}
		
		self.getFlawTotal = function() {
			var total = _.reduce(self.characterSheet.flaws(), function(memo, item) { return memo + Number(item.data.cost); }, 0);
			total += self.characterSheet.derangements().length * 2;
			if(self.characterSheet.clan.selected() && self.characterSheet.clan.selected().name == "Malkavian") total -= 2;
			return total;
		}
		
		self.getMeritTotal = function() {
			var isGangrel = self.characterSheet.clan.selected() && self.characterSheet.clan.selected().name.indexOf("Gangrel") !== -1;
			return _.reduce(self.characterSheet.merits(), function(memo, item) {
				if(item.data.name == "Inoffensive to Animals" && isGangrel) {
						return memo;
					} else {
						return memo + Number(item.data.cost); 
					}
			}, 0);
		}
		
		self.addDerangement = function(id) {
			var derangement = self.getDerangementById(id);
			if(self.getFlawTotal() <= 5 || self.approvedVersion() > 0) {
					if(derangement.requires_description == 1) {
					self.showInputModal(
						"Enter Derangement Description", 
						"Please enter a short description for this derangement.", 
						"Description...",
						function(value) {
							self.characterSheet.derangements.push({data: derangement, description: value});
							self.activeDerangement(undefined);
							self.updateExperienceSpent();
						}
					);
				} else {
					self.characterSheet.derangements.push({
						data: derangement, 
						description: ""
					});
					self.activeDerangement(undefined);
					self.updateExperienceSpent();
				} 

			} else {
				self.showModal(
					"Invalid Purchase", 
					"You cannot have more than 7 points of Flaws/Derangements at character creation."
				);				
			}	
		}
		
		self.addRitual = function(id) {
			var definition = self.getRitualById(id);
			var groupLevel = { 
				"Basic" : 1, 
				"Intermediate": 2, 
				"Advanced": 3
			
			}
			if(groupLevel[definition.group] > self.getMaximumRitualRank() && definition.name != "Rite of Introduction") {
				self.showModal(
					"Invalid Purchase", 
					"You cannot buy rituals that are more advanced than the Thaumaturgy or Necromancy you know."
				);
			} else {
				self.characterSheet.rituals.push(id);
				self.activeRitual(undefined);
				self.updateExperienceSpent();
			}			
		}

		
		self.tickVirtue = function(place, amount) {
			var counts = self.characterSheet.virtues();
			if(place === 2 && self.approvedVersion() == 0) {
				//Trying to tick morality
				if(self.characterSheet.hasDroppedMorality()) {
					if(amount == -1 && counts[place] != 1) {
						self.showModal(
							"Cannot drop Morality.", 
							"You cannot drop your Morality more than one Dot at character creation."
						);
						return;
					} else {
						self.characterSheet.hasDroppedMorality(false);
					}
				} else {
					if(amount == 1) {
						self.showModal(
							"Cannot drop Morality.", 
							"You can only raise your Morality at character creation if you have previously lowered it."
						);
						return;
					} else {
						self.characterSheet.hasDroppedMorality(true);
					}
				}
			}
			counts[place] += amount;
			if(counts[place] < 1) counts[place] = 1;
			if(counts[place] > 5 ) counts[place] = 5;
			//Recalculate morality if we're in gen
			if(self.approvedVersion() == 0) {
				counts[2] = Math.ceil((counts[0] + counts[1]) / 2) - (self.characterSheet.hasDroppedMorality() ? 1 : 0) ;
			}
			//Update character sheet
			self.characterSheet.virtues(counts);
			self.updateExperienceSpent();		
		}
		
		self.onVirtueMinus = function(index) {
			self.tickVirtue(index, -1);
		}
		
		self.onVirtuePlus = function(index) {
			self.tickVirtue(index, 1);
		}

		self.getMaximumRitualRank = function() {
			var limit = 0;
			for(var i in self.characterSheet.disciplines()) {
				var disc = self.characterSheet.disciplines()[i];
				if(disc.name == "Thaumaturgy" || disc.name == "Necromancy") {
					if(disc.count > limit) limit = disc.count;
				}
			}
			return limit >= 5 ? 3 : limit >= 3 ? 2 : limit >= 1 ? 1 : 0;
		}

		self.validRitualOptions = ko.computed(function() {
			var groupLevel = ["Basic", "Intermediate", "Advanced"];
			var rank = self.getMaximumRitualRank();
			return groupLevel.splice(0, rank);
		});

		
		self.hasClanOptions = ko.computed(function() {
			var selClan = self.characterSheet.clan.selected();
			if(selClan) {
				var clanName = selClan.name;
				switch(clanName) {
					case "Brujah":
					case "Caitiff":
					case "Malkavian":
					case "Toreador":
					case "Ventrue":
					case "Lasombra":
					case "Ravnos":
					case "Daughters of Cacophony":
					case "Followers of Set":
					case "Giovanni":
					case "Tremere":
						return true;
				}
			}
			return false;
		});
		

		self.clanSelectionDependentLimits = [];

		self.showClanOption = function(clanName) {
			var selClan = self.characterSheet.clan.selected();
			if(selClan) {
				if(selClan.name == clanName) return true;
			}
			return false;
		}
		
		self.clanOptions = ko.computed(function() {
			var selClan = self.characterSheet.clan.selected();
			if(selClan) {
				var clanName = selClan.name;
				switch(clanName) {
					case "Toreador":
					case "Ventrue":
					case "Lasombra":
					case "Ravnos":
					case "Daughters of Cacophony":
					case "Followers of Set":
					case "Giovanni":
				}
			}
		});
		
		self.removeDerangement = function(item) {
			if(!self.isStoryteller()) {
				if(_.findWhere(self.clanSelectionDependentLimits, {type: "derangement", id: item.data.id})) {
					self.showModal(
						"Cannot remove Derangement.", 
						"Your selected Clan Options means you must have have the Derangement <i>" + item.data.name + "</i>."
					);
					return;
				}
				if(self.approvedVersion() > 0) {
					self.showModal(
						"Cannot remove Derangement.", 
						"Only Storytellers can remove derangements after character creation."
					);
					return;
				}
			}
			self.characterSheet.derangements.remove(item);	
			self.updateExperienceSpent();		
		}		
		
		self.removeRitual = function(id) {
			var ritual = _.indexOf(self.characterSheet.rituals(), {id: id});
			if(ritual) {
				if(!self.isStoryteller() && _.findWhere(self.clanSelectionDependentLimits, {type: "ritual", id: id})) {
					self.showModal(
						"Cannot remove Ritual.", 
						"Tremere must always have the Rite of Introduction ritual."
					);
					return;
				}
				self.characterSheet.rituals.remove(id);
				self.updateExperienceSpent();			
			}	
		}
		
		self.removeMerit = function(item) {
			if(!self.isStoryteller()) {
				if(_.findWhere(self.clanSelectionDependentLimits, {type: "merit", id: item.data.id})) {
					self.showModal(
						"Cannot remove Merit.", 	
						"Your selected Clan Options means you must have have the Merit <i>" + item.data.name + "</i>."
					);
					return;
				}
				if(self.approvedVersion() > 0) {
					self.showModal(
						"Cannot remove Merit.", 
						"Only Storytellers can remove merits after character creation."
					);
					return;
				}
			}
			self.characterSheet.merits.remove(item);	
			self.updateExperienceSpent();			
		}
		
		self.removeFlaw = function(data) {
			self.characterSheet.flaws(_.difference(self.characterSheet.flaws(), [data]));
			self.updateExperienceSpent();			
		}

		self.purchaseDiscipline = function(id, path) {
			var path = path || null;
			var myDisc = self.getCharacterDiscipline(id, path);
			if(myDisc) {
				myDisc.count++;
				if(self.approvedVersion() == 0 && myDisc.count > 3 && !self.isStoryteller()) {
					self.showModal(
						"Invalid Purchase", 
						"Characters cannot possess Disciplines past the third rank at character creation."
					);
					myDisc.count--;
				}
				self.characterSheet.disciplines.refresh();
				self.updateExperienceSpent();
			} else {
				var data = self.getDisciplineById(id);
				if(self.getDisciplineSplit().inClan.indexOf(data) == -1 && !self.isStoryteller()) {
					self.showInputModal(
						"Name Teacher", 
						"The Discipline <i>" + data.name + "</i> is out-of-clan for you, so you must list who taught it to you. " +
						"This can be either a PC or an NPC.", "Teacher's name...", 
						function(input) {
							self.characterSheet.disciplines.push({ id: data.id, name: data.name, count: 1, path: path, mentor: [input] });
							self.characterSheet.disciplines.refresh();
							self.updateExperienceSpent();
						}
					);
				} else {
					var data = self.getDisciplineById(id);
					self.characterSheet.disciplines.push({ id: data.id, name: data.name, count: 1, path: path });
					var list = self.characterSheet.disciplines.refresh();
					self.updateExperienceSpent();
				}
			}
				
		}

		self.getCharacterDisciplineRanks = function(id) {
			var discipline = self.getCharacterDiscipline(id);
			var count = discipline.count + _.where(self.characterSheet.elderPowers(), {discipline: id}).length;
			return count;
		}

		self.removeDisciplineRank = function(id, path) {
			var myDisc = self.getCharacterDiscipline(id, path);
			var items = self.getDisciplineList(id, path);				
			if(myDisc) {
				if(items.length > myDisc.count) {
					var item = items[items.length - 1];
					self.characterSheet.elderPowers.remove(item);
				} else {
					myDisc.count--;
					if(myDisc.count == 0) {
						//Remove it
						self.characterSheet.disciplines.remove(myDisc);
					}
				}
			}
			var list = self.characterSheet.disciplines.refresh();
			self.updateExperienceSpent();				
		}
		
		self.availableClans = ko.computed(function() {
			var out = [{ Label: "Common Clans", Options: [] }, { Label: "Uncommon Clans", Options: [] }];
			var ref = [];
			if(!self.characterSheet.sect.selected()) return [];
			out[0].Options = _.map(self.characterSheet.sect.selected().common_clans, function(item) { 
				return { 
					Text: item.name, 
					Value: item.id
				}; 
			});
			out[1].Options = _.map(self.characterSheet.sect.selected().uncommon_clans, function(item) { 
				return { 
					Text: item.name, 
					Value: item.id
				}; 
			});
			if(self.isStoryteller()) {
				var associatedList = _.union(_.pluck(self.characterSheet.sect.selected().common_clans, "id"), 
											 _.pluck(self.characterSheet.sect.selected().uncommon_clans, "id"));
				var unassociatedList = _.reject(self.rulebook.clans(), function(item) { return associatedList.indexOf(item.id) !== -1; });
				out.push({ 
					Label: "Unassociated Clans", 
					Options: _.map(unassociatedList, function(item) { return { Text: item.name, Value: item.id} })
				});
			}
			return out;
		});
	
		self.getDisciplineSplit = function() {
			var out = {"inClan": [], "offClan": [] };
			if(!self.characterSheet.clan.selected()) return out;
			var clanName = self.characterSheet.clan.selected().name;
			for(var i in self.rulebook.disciplines()) {
				var disc = self.rulebook.disciplines()[i];
				var found = false;
				if(clanName != "Caitiff") {
					for(var j in self.characterSheet.clan.selected().disciplines) {
						var clanDisc = self.characterSheet.clan.selected().disciplines[j];
						if(clanName == "Malkavian" && (disc.name == "Dominate" || disc.name == "Dementation")) {
							found = disc.name == self.clanOptionValues.malkavian[1]();
						} else if (clanName == "Tremere" && (disc.name == "Thaumaturgy" || disc.name == "Countermagic")) {
							found = disc.name == self.clanOptionValues.tremere[0]();
						} else if(clanDisc.id == disc.id) {
							found = true;
						}
					}
				} else {
					if(disc.name == self.clanOptionValues.caitiff[0]() || 
						 disc.name == self.clanOptionValues.caitiff[1]() || 
						 disc.name == self.clanOptionValues.caitiff[2]()) {
						 	found = true;
					}
				}
				out[found ? "inClan" : "offClan"].push(disc);
			}
			return out;
		}

		self.availableDisciplines = ko.computed(function() {
			var disciplines = self.getDisciplineSplit();
			var out = [
				{ Label: "Clan Disicplines", Options:  _.map(disciplines.inClan, function(item) { return { Text: item.name, Value: item.id } }) }, 
				{ Label: "Off-Clan Disciplines", Options: _.map(disciplines.offClan, function(item) { return { Text: item.name, Value: item.id } }) }
			];
			return out;
		});


		self.availablePathDisciplines = ko.computed(function() {
			var out = [{ Label: "Easy Paths", Options: [] }, { Label: "Hard Paths", Options: [] }];
			if(!self.activeDiscipline()) return out;
			var discipline = self.getDisciplineById(self.activeDiscipline().Value);
			_.map(discipline.paths, function(path) { out[path.hard_path == "0" ? 0 : 1].Options.push({ Text: path.name, Value: path.id }) });
			return out;
		});

		self.hasThaumaturgy = function() {
			return _.findIndex(self.characterSheet.disciplines(), function(item) { return item.name == "Thaumaturgy"; }) !== -1;
		}

		self.hasNecromancy = function() {
			return _.findIndex(self.characterSheet.disciplines(), function(item) { return item.name == "Necromancy"; }) !== -1;
		}

		self.canUseRituals = ko.computed(function() {
			return self.hasThaumaturgy() || self.hasNecromancy();
		});

		self.availableAbilities = ko.computed(function() {
			//omg it's beautiful
			return _.map(self.rulebook.abilities(), function(item) {
				return { 
					Label: item.name,
					Options: _.difference(_.map(item.options, function(option) {
						if(!_.findWhere(self.characterSheet.abilities(), {id: option.id})) {
							return {Text: option.name, Value: option.id };
						}
					}), [undefined])
				};
			});
					});

		self.listAllDisciplines = ko.computed(function() {
			return _.map(self.characterSheet.disciplines(), function(item) {
				return {
					Label: item.name,
					Options: _.map(self.getDisciplineList(item.id, item.path == 0 ? null : item.path), function(disc) {
							return { Text: disc.name, Value: disc.id };
						})
				}
			});
		});
		
		self.availableRituals = ko.computed(function() {
			return _.map(self.rulebook.rituals(), function(item) {
				return {
					Label: item.name,
					Options: _.difference(_.map(item.options, function(ritual) {
						if(_.indexOf(self.characterSheet.rituals(), ritual.id) === -1) {
							if((ritual.is_thaumaturgy == 1 && self.hasThaumaturgy()) || (ritual.is_thaumaturgy == 0 && self.hasNecromancy())) {
								return { Text: ritual.name, Value: ritual.id }
							}
						}
					}), [undefined])
				}
			});

		});
		
		self.availableBackgrounds = ko.computed(function() {
			return _.map(self.rulebook.backgrounds(), function(item) {
				return {
					Label: item.name,
					Options: _.difference(_.map(item.options, function(background) {
						var charBackground = _.findWhere(self.characterSheet.backgrounds(), {id: background.id});
						if(!charBackground || (charBackground.description != null && charBackground.description.length > 0)) {
							return { Text: background.name, Value: background.id };
						}
					}), [undefined])
				}
			});

		});
		
		self.availableDerangements = ko.computed(function() {
			return _.difference(_.map(self.derangements(), function(item) {
				var found = _.find(self.characterSheet.derangements(), function(characterDerangement) {
					return characterDerangement.data.id == item.id;
				});
				if(!found) return item;
			}), [undefined]);
		});
				
		self.availableMerits = ko.computed(function() {
			return _.map(self.rulebook.merits(), function(item) {
				return {
					Label: item.name,
					Options: _.difference(_.map(item.options, function(merit){
						var found = _.find(self.characterSheet.merits(), function(characterMerit) {
							return characterMerit.data.id == merit.id;
						});
						
						if(merit.name == "Feature" || !found) {
							if(merit.name != "Infernalist 2" || self.isStoryteller()) {
								return { Text: merit.name + " (" + merit.cost + ")", Name: merit.name, Value: merit.id };
							}
						}
					}), [undefined])
				}
			})
		});
		
		self.availableFlaws = ko.computed(function() {
			return _.map(self.rulebook.flaws(), function(item) {
				return {
					Label: item.name,
					Options: _.difference(_.map(item.options, function(flaw) {
						var found = _.find(self.characterSheet.flaws(), function(characterFlaw) {
							return characterFlaw.data.id == flaw.id;
						});
						if(flaw.name == "Feature" || !found) {
							return { Text: flaw.name + " (" + flaw.cost + ")", Name: flaw.name, Value: flaw.id }; 
						}
					}), [undefined])
				}
			});
		});

		self.getNextDisciplineRank = function(id, path) {
			var baseDisc = self.getDisciplineById(id, path);
			var myDisc = self.getCharacterDiscipline(id, path);
			var count = myDisc ? myDisc.count : 0;
			if(self.hasPaths()) {
				for(var j = 0; j < baseDisc.paths.length; j++) {
					var pth = baseDisc.paths[j];
					if(pth.id == path) return pth.ranks[count];
				}
			}
			return baseDisc.ranks[count];
		}

		self.getAbilityCount = function(id) {
			var ability = _.findWhere(self.characterSheet.abilities(), {id: id});
			return ability ? ability.count : 0;
		}
		
		self.tickAbility = function(ability, amount) {
			var ability = _.findWhere(self.characterSheet.abilities(), { id: ability.id });
			if(ability) {
				ability.count = Number(ability.count) + amount;
				if(ability.count > 5) ability.count = 5;
				var limit = _.filter(_.where(self.clanSelectionDependentLimits, { type: "ability", id: ability.id}), function(item) {
					return ability.count < item.amount;
				});
				if(limit.length && !self.isStoryteller()) {
					ability.count = limit[0].amount;
					self.showModal("Cannot lower Ability.", 
						"Your selected Clan Options means you must have at least " + 
						limit[0].amount + " Dot" + (limit[0].amount > 1 ? "s" : "") + " of the <i>" + ability.name + "</i> Ability."
					);
				}
				if(ability.count == 0) {
					self.characterSheet.abilities.remove(ability);
				}
				self.characterSheet.abilities.refresh();
				self.updateExperienceSpent();			
			}
		}


		self.getBackgroundCount = function(id) {
			var item = _.findWhere(self.characterSheet.backgrounds(), {id: id})
			return item ? item.count : 0;
		}
				
		self.getInfluenceCount = function() {
			var items = _.filter(self.characterSheet.backgrounds(), function(item) { 
				return self.getBackgroundById(item.id).group == "Influence"; 
			});
			return _.reduce(items, function(memo, item) { return memo + Number(item.count); }, 0);
		}

		
		self.getInfluenceLimit = function() {
			var items = _.filter(self.characterSheet.backgrounds(), function(item) { 
				return item.name == "Retainers" || item.name.indexOf("Ghoul") !== -1; 
			});
			return self.attributePointsSpent() + _.reduce(items, function(memo, item) { return memo + Number(item.count); }, 0);
		}


		
		self.tickBackground = function(background, amount) {
			var item = _.findWhere(self.characterSheet.backgrounds(), { id: background.id, description: background.description });
			if(item) {
				var ref = self.getBackgroundById(background.id);
				if(ref.group == "Influence") {
					var influenceCount = self.getInfluenceCount();
					var influenceLimit = self.getInfluenceLimit();
					if(influenceCount + 1 > influenceLimit && !self.isStoryteller()) {
						self.showModal("Cannot increase Influence.", 
							"You cannot have more Dots of Influence than the total of your \
							Attributes plus Dots of Ghouls plus your Dots of Retainers."
						);
						return;
					}
				}
				item.count = Number(item.count) + Number(amount);
				if(item.count > 5) item.count = 5;
				//Check if we're limited by our clan.
				var limit = _.filter(_.where(self.clanSelectionDependentLimits, { type: "background", id: background.id}), function(item) {
					return background.count < item.amount;
				});
				if(limit.length && !self.isStoryteller()) {
					item.count = limit.amount;
					self.showModal("Cannot lower Background.", 	
						limit[0].message ?
							limit[0].message : 
							("Your selected Sect or Clan Options means you must have at least " + limit[0].amount + " Dot" + 
							(limit[0].amount > 1 ? "s" : "") + " of the Background <i>" + background.name + "</i>.")
					);
					return;
				}
				if(item.count <= 0) {
					self.characterSheet.backgrounds.remove(item);
				}
			}
			//Lazy refresh
			var vals = self.characterSheet.backgrounds.refresh();
			self.updateExperienceSpent();			
		}

		self.getTraitMax = function() {
			return 13;
		}

		self.getTraitLoop = ko.computed(function() {
			var sub = self.characterSheet.attributes()[0]; //Lazy subscription
			var out = [];
			for(var i = 0; i < self.getTraitMax(); i++) {
				out.push(i);
			}
			return out;	
		});

		self.tickWillpower = function(amount, type) {
			if(type == 'traits') {
				self.characterSheet.willpower.traits(self.characterSheet.willpower.traits() + amount);
				if(self.characterSheet.willpower.traits() < 0) self.characterSheet.willpower.traits(0);
			} else {
				self.characterSheet.willpower.dots(self.characterSheet.willpower.dots() + amount);
				if(self.characterSheet.willpower.dots() > 10) self.characterSheet.willpower.dots(10);
				if(self.characterSheet.willpower.dots() < 0) self.characterSheet.willpower.dots(0);
				self.updateExperienceSpent();
			}
			if(self.characterSheet.willpower.traits() > self.characterSheet.willpower.dots()) {
				self.characterSheet.willpower.traits(self.characterSheet.willpower.dots());
			}
		}

		self.getCharacterDiscipline = function(id, path) {
			if(path) {
				return _.findWhere(self.characterSheet.disciplines(), { id: id, path: path });
			}
			return _.findWhere(self.characterSheet.disciplines(), { id: id });		
		}

		self.getDisciplineList = function(id, path) {
			var out = [];
			var disc = self.getCharacterDiscipline(id, path);
			if(disc) {
				var data = self.getDisciplineById(id);
				if(path && path != 0) {
					data = self.getDisciplinePathById(id, path);
				}
				for(var i = 0; i < disc.count; i++) {
					out.push(data.ranks[i]);
				}
				_.each(_.where(self.characterSheet.elderPowers(), {discipline: id}), function(power) {
					out.push(power);
				})
			}
			return out;
		}

		self.getDisciplineById = function(id) {
			return _.findWhere(self.rulebook.disciplines(), {id: id});
		}

		self.getDisciplineByName = function(name) {
			return _.findWhere(self.rulebook.disciplines(), {name: name});
		}

		self.getDisciplinePathById = function(discipline_id, id) {
			var disc = self.getDisciplineById(discipline_id);
			return _.findWhere(disc.paths, {id: id});
		}

		self.getSectById = function(id) {
			return _.findWhere(self.rulebook.sects(), {id: id});
		}

		self.getClanById = function(id) {
			return _.findWhere(self.rulebook.clans(), {id: id});
		}

		self.getNatureById = function(id) {
			return _.findWhere(self.rulebook.natures(), {id: id});
		}

		self.getPathById = function(id) {
			return _.findWhere(self.rulebook.paths(), {id: id});
		}

		self.getAbilityById = function(id) {
			return _.findWhere(_.flatten(_.pluck(self.rulebook.abilities(), "options"), true), {id: id});
		}

		self.getAbilityByName = function(name) {
			return _.findWhere(_.flatten(_.pluck(self.rulebook.abilities(), "options"), true), {name: name});
		}
		
		self.getCustomAbilityById = function(id) {
			return _.findWhere(self.rulebook.customAbilities(), {id: id});			
		}
		
		self.getBackgroundById = function(id) {
			return _.findWhere(_.flatten(_.pluck(self.rulebook.backgrounds(), "options"), true), {id: id});

		}

		self.getBackgroundByName = function(name) {
			return _.findWhere(_.flatten(_.pluck(self.rulebook.backgrounds(), "options"), true), {name: name});
		}

		self.getCompleteRitualList = function() {
			var rituals =  _.flatten(_.pluck(self.rulebook.rituals(), "options"), true);
			return _.union(rituals, self.rulebook.customRituals());
		}
		
		self.getRitualById = function(id) {
			return _.findWhere(self.getCompleteRitualList(), {id: id});
		}
		
		self.getRitualByName = function(name) {
			return _.findWhere(self.getCompleteRitualList(), {name: name});
		}

		self.getDerangementById = function(id) {
			return _.findWhere(self.derangements(), {id: id});
		}
	
		self.getDerangementByName = function(name) {
			return _.findWhere(self.derangements(), {name: name});

		}
				
		self.getMeritById = function(id) {
			return _.findWhere(
				_.flatten(
					_.pluck(self.rulebook.merits(), "options"),
				true), 
			{id: id});
		}
		
		self.getMeritByName = function(name) {
			return _.findWhere(
				_.flatten(
					_.pluck(self.rulebook.merits(), "options"),
				true), 
			{name: name});
		}

		self.getFlawById = function(id) {
			return _.findWhere(
				_.flatten(
					_.pluck(self.rulebook.flaws(), "options"), 
				true), 
			{id: id});
		}

		self.showModal = function(title, body) {
			self.modal.mode(0);
			self.modal.title(title);
			self.modal.body(body);
			$('#main-modal').foundation('reveal', 'open');
		}

		self.showInputModal = function(title, body, placeholder, callback) {
			self.inputModal.title(title);
			self.inputModal.body(body);
			self.inputModal.placeholder(placeholder);
			self.inputModal.callback = callback;
			self.inputModal.value("");
			$('#input-modal').foundation('reveal', 'open');
		}

		self.openSpecializationModal = function(data) {
			self.showInputModal("Add Specialization",
				"You are adding a Specialization to the Ability <i>" + data.name + "</i>.", "Specialization name", 
				function(input) {
					var ability = _.findWhere(self.characterSheet.abilities(), {id: data.id});
					if(ability) {
						ability.specialization = input;
						self.updateExperienceSpent();
						self.characterSheet.abilities.refresh();
					}
				}
			);
		}
		
		self.showConfirmModal = function(title, body, callback) {
			self.modal.mode(1);
			self.modal.title(title);
			self.modal.body(body);
			self.modal.callback = callback;
			$('#main-modal').foundation('reveal', 'open');
		}

		self.showElderModal = function(discipline) {
			self.elderModal.discipline(discipline);
			self.elderModal.name("");
			self.elderModal.description("");
			$("#elder-modal").foundation('reveal', 'open');
		}


		self.showRitualModal = function() {
			self.ritualModal.type("discipline");
			self.ritualModal.name("");
			self.ritualModal.description("");
			$("#ritual-modal").foundation('reveal', 'open');
		}

		self.showComboModal = function() {
			self.comboModal.name("");
			self.comboModal.description("");
			self.comboModal.option1(null);
			self.comboModal.option2(null);
			self.comboModal.option3(null);
			$("#combo-modal").foundation('reveal', 'open');
		}

		self.addElderPower = function() {
			self.characterSheet.elderPowers.push({
				id: self.getElderPowerId(), 
				discipline: self.elderModal.discipline().id, 
				name: self.elderModal.name(), 
				description: self.elderModal.description()
			});
			self.updateExperienceSpent();				
			$("#elder-modal").foundation('reveal', 'close');
		}

		self.addCustomRitual = function() {
			var newRitual = {
				id: self.getCustomRitualId(), 
				type: self.ritualModal.type(), 
				name: self.ritualModal.name(), 
				description: self.ritualModal.description()
			};
			self.rulebook.customRituals.push(newRitual);
			self.newRituals.push(newRitual);
			self.characterSheet.rituals.push(newRitual.id);
			self.updateExperienceSpent();
			$("#ritual-modal").foundation('reveal', 'close');
		}

		self.addComboDiscipline = function() {
			self.characterSheet.comboDisciplines.push({ 
				id: self.getComboDisciplineId(), 
				option1: self.comboModal.option1(), 
				option2: self.comboModal.option2(), 
				option3: self.comboModal.option3(),
				name: self.comboModal.name(), 
				description: self.comboModal.description() 
			});
			self.updateExperienceSpent();				
			$("#combo-modal").foundation('reveal', 'close');
		}

		var clanOptionMap = {
			"Brujah": "brujah",
			"Caitiff": "caitiff",
			"Malkavian": "malkavian",
			"Toreador": "toreador",
			"Ventrue": "ventrue",
			"Lasombra": "lasombra",
			"Ravnos": "ravnos",
			"Daughters of Cacophony": "daughters",
			"Followers of Set": "setites",
			"Giovanni": "giovanni",
			"Tremere": "tremere"
		}

		self.newRituals = [];

		self.buildCharacterSheet = function() {
			var out = {
				name: self.characterSheet.name(),
				sect: {
					selected: self.characterSheet.sect.selected() ? self.characterSheet.sect.selected().id : null,
					displaying: self.characterSheet.sect.displaying() ? self.characterSheet.sect.displaying().id : null
				},
				clan: {
					selected: self.characterSheet.clan.selected()  ? self.characterSheet.clan.selected().id : null,
					displaying: self.characterSheet.clan.displaying() ? self.characterSheet.clan.displaying().id : null
				},
				nature: self.characterSheet.nature() ? self.characterSheet.nature().id : null,
				willpower: {
					traits: self.characterSheet.willpower.traits(),
					dots: self.characterSheet.willpower.dots()
				},
				attributes: _.object(["physicals", "mentals", "socials"], self.characterSheet.attributes()),
				abilities: _.map(self.characterSheet.abilities(), function(ability) {
					return { 
						"id": ability.id, 
						"count": ability.count, 
						"name": ability.name, 
						"specialization": ability.specialization 
					};
				}),
				disciplines: _.map(self.characterSheet.disciplines(), function(discipline) {
					return { 
						"id": discipline.id, 
						"count": discipline.count, 
						"path": discipline.path 
					};
				}),
				rituals: self.characterSheet.rituals(),
				backgrounds: _.map(self.characterSheet.backgrounds(), function(background) {
					return { 
						"id": background.id, 
						"count": background.count, 
						"description": (background.description && background.description.length) > 0 ? background.description : null 
					};
				}),
				path: self.characterSheet.path() ? self.characterSheet.path().id : null,
				virtues: self.characterSheet.virtues(),
				derangements: _.map(self.characterSheet.derangements(), function(derangement) {
					return { 
						id: derangement.data.id, 
						description: (derangement.description && derangement.description.length > 0) ? derangement.description : null 
					};
				}),				merits: _.map(self.characterSheet.merits(), function(merit) {
					return { 
						id: merit.data.id, 
						description: (merit.description && merit.description.length > 0) ? merit.description : null 
					};
				}),
				flaws: _.map(self.characterSheet.flaws(), function(flaw) {
					return { 
						id: flaw.data.id, 
						description: (flaw.description && flaw.description.length > 0) ? flaw.description : null 
					};
				}),
				hasDroppedMorality: self.characterSheet.hasDroppedMorality(),
				elderPowers: self.characterSheet.elderPowers(),
				comboDisciplines: self.characterSheet.comboDisciplines(),
				clanOptions: []
			}

			if(self.characterSheet.clan.selected()) {
				if(self.characterSheet.clan.selected().name == "Toreador") {
					out.clanOptions = [];
					//god toreador suck
					if(self.clanOptionValues.toreador[0]() == 'Crafts' || self.clanOptionValues.toreador[0]() == 'Perform') {
						out.clanOptions.push(self.clanOptionValues.toreador[0]() + ": " + self.clanOptionValues.toreador[2]());
					} else {
						out.clanOptions.push(self.clanOptionValues.toreador[0]());
					}

					if(self.clanOptionValues.toreador[1]() == 'Crafts' || self.clanOptionValues.toreador[1]() == 'Perform') {
						out.clanOptions.push(self.clanOptionValues.toreador[1]() + ": " + self.clanOptionValues.toreador[3]());
					} else {
						out.clanOptions.push(self.clanOptionValues.toreador[1]());
					}				
				} else {
					out.clanOptions = self.clanOptionValues[clanOptionMap[self.characterSheet.clan.selected().name]];
				}
			}
			return out;
		}

		self.showMalkavianDerangementDescription = function() {
			var opt = self.clanOptionValues.malkavian[0]();
			if(opt) {
				var derangement = self.getDerangementByName(opt);
				self.showModal(derangement.name, derangement.description);
			} else {
				self.showModal("Malkavian Derangements", 
				"When you've selected a Derangement, click on this question mark to learn more about it. \
				Feel free to select a new Derangement if it's not to your liking!");
			}
		}
		var startedSaving = false;
		self.save = function(review, cont) {
			if(startedSaving) return;
			startedSaving = true;
			$.ajax({
				url: "/characters/save",
				type: 'post',
				data: {
					id: preloaded.characterId, //get past auth
					characterId: preloaded.characterId,
					sheet: self.buildCharacterSheet(),
					comment: self.versionComment(),
					review: review ? 1 : 0,
					"continue": cont ? cont : 0,
					newRituals: self.newRituals
				},
				success: function(data) {
					if(data.success) {
						if(data.mode == 1) {
							toastr.success("Character saved.");
						} else {
							document.location = "/dashboard";
						}
						startedSaving = false;
					} else {
						if(data.mode == 0) {
							self.showModal("Something went wrong.", "<p>" + data.message + "</p>");
						} else if (data.mode == 2) {
							self.showModal("Could not submit character.", "<p>" + data.message + "</p>");	
						}
						startedSaving = false;
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					console.log("Failed to load rulebook", xhr.status, thrownError);	
					startedSaving = false;
					self.showModal("Unable to save.", "An error occured: " + thrownError);
				}
			});
		}
		//Load in the rulebook data
		$.ajax({
			url: "/rulebook/" + preloaded.characterId,
			dataType: 'json',
			success: function(data) {
				self.rulebook.sects(data.sects);
				self.rulebook.clans(data.clans);
				self.rulebook.natures(data.natures);
				self.rulebook.abilities(data.abilities);
				self.rulebook.disciplines(data.disciplines);
				self.rulebook.backgrounds(data.backgrounds);
				self.rulebook.paths(data.paths);
				self.derangements(data.derangements);
				self.rulebook.merits(data.merits);
				self.rulebook.flaws(data.flaws);
				self.rulebook.rituals(data.rituals);
				self.rulebook.customAbilities(data.custom_abilities);
				self.rulebook.elderPowers(data.elder_powers);
				self.rulebook.comboDisciplines(data.combo_disciplines);
				self.rulebook.customRituals(data.custom_rituals);

				self.isStoryteller(preloaded.isStoryteller);

				if(preloaded.cData) {
					self.editingId(preloaded.cData.id);
					self.editingVersion(preloaded.editingVersion);
					self.approvedVersion(preloaded.cData.approved_version);
					self.characterSheet.hasDroppedMorality(preloaded.cData.version.hasDroppedMorality == 1);
					
					if(self.approvedVersion() == 0) {
						console.log('Enabling subscribers');
						self.characterSheet.clan.selected.subscribe(function(oldValue) { self.lockAndClearClanOptions(); }, this, 'beforeChange');
	
						self.characterSheet.clan.selected.subscribe(function(newValue) { self.unlockAndUpdateClanOptions(); });
	
						self.characterSheet.sect.selected.subscribe(function(oldValue) { self.lockAndClearSectOptions(); }, this, 'beforeChange');
						
						self.characterSheet.sect.selected.subscribe(function(newValue) { self.unlockAndUpdateSectOptions(); });
	
						for(var key in self.clanOptionValues) {
							for(var i in self.clanOptionValues[key]) {
								self.clanOptionValues[key][i].subscribe(function(oldValue){ 
									console.log("Update ID", update++); self.lockAndClearClanOptions(); 
								}, this, 'beforeChange');
								self.clanOptionValues[key][i].subscribe(function(newValue){ 
									console.log("Update ID", update++);  self.unlockAndUpdateClanOptions(); 
								});
							}
						}
						self.clanOptionValues.caitiff[0].subscribe(function(newValue) {
							if(newValue != "" && (newValue == self.clanOptionValues.caitiff[1]() || newValue == self.clanOptionValues.caitiff[2]())) {
								self.showModal("Cannot select the same discipline.", "Caitiff must have three distinct disciplines.");
								self.clanOptionValues.caitiff[0]("");
							}							
						});
						self.clanOptionValues.caitiff[1].subscribe(function(newValue) {
							if(newValue != "" && (newValue == self.clanOptionValues.caitiff[0]() || newValue == self.clanOptionValues.caitiff[2]())) {
								self.showModal("Cannot select the same discipline.", "Caitiff must have three distinct disciplines.");
								self.clanOptionValues.caitiff[1]("");
							}							
						});
						self.clanOptionValues.caitiff[2].subscribe(function(newValue) {
							if(newValue != "" && (newValue == self.clanOptionValues.caitiff[0]() || newValue == self.clanOptionValues.caitiff[1]())) {
								self.showModal("Cannot select the same discipline.", "Caitiff must have three distinct disciplines.");
								self.clanOptionValues.caitiff[2]("");
							}							
						});
					}
				
					if(preloaded.cData.sect) {
						self.activeSect(self.getSectById(preloaded.cData.sect.sect_id));
						self.characterSheet.sect.selected(self.activeSect());
						self.characterSheet.sect.displaying(self.getSectById(preloaded.cData.sect.hidden_id));
					}
					if(preloaded.cData.clan) {
						var activeClan = self.getClanById(preloaded.cData.clan.clan_id);
						self.activeClan({ Value: activeClan.id });
						self.characterSheet.clan.selected(activeClan);
						self.characterSheet.clan.displaying(self.getClanById(preloaded.cData.clan.hidden_id));
					}
					
					if(preloaded.cData.nature) {
						self.characterSheet.nature(self.getNatureById(preloaded.cData.nature.nature_id));
						self.activeNature(self.characterSheet.nature());
					}

					self.characterSheet.willpower.traits(parseInt(preloaded.cData.willpower.traits, 10));
					self.characterSheet.willpower.dots(parseInt(preloaded.cData.willpower.dots, 10));

					self.characterSheet.attributes([
						preloaded.cData.attributes.physicals, 
						preloaded.cData.attributes.mentals, 
						preloaded.cData.attributes.socials
					]);

					for(var i = 0; i < preloaded.cData.abilities.length; i++) {
						var ability = preloaded.cData.abilities[i];
						var ability_definition = self.getAbilityById(ability.ability_id ) || self.getCustomAbilityById(ability.ability_id);
						var newItem = {id: ability.ability_id, count: ability.amount, name: ability_definition.name };
						if(ability.specialization) newItem.specialization = ability.specialization;
						self.characterSheet.abilities.push(newItem);
					}

					for(var i = 0; i < preloaded.cData.disciplines.length; i++) {
						var discipline = preloaded.cData.disciplines[i];
						self.characterSheet.disciplines.push({	
							id: discipline.discipline_id, count: discipline.ranks, 
							name: self.getDisciplineById(discipline.discipline_id).name, 
							path: discipline.path_id
						});
					}

					for(var i = 0; i < preloaded.cData.rituals.length; i++) {
						self.characterSheet.rituals.push(preloaded.cData.rituals[i].ritual_id);
					}

					for(var i = 0; i < preloaded.cData.backgrounds.length; i++) {
						var background = preloaded.cData.backgrounds[i];
						var existingBackground = _.findWhere(self.characterSheet.backgrounds(), {
							id: background.background_id, 
							description: background.description ? background.description : ""
						});
						console.log(self.characterSheet.backgrounds())
						if(existingBackground) {
							existingBackground.count = Number(background.amount);
							self.characterSheet.backgrounds.refresh();
						} else {	
							self.characterSheet.backgrounds.push({ 	
								id: background.background_id, 
								count: Number(background.amount), 
								name: self.getBackgroundById(background.background_id).name, 
								description: background.description
							});
						}
					}
					if(preloaded.cData.path) {
						self.characterSheet.path(self.getPathById(preloaded.cData.path.path_id));
						self.characterSheet.virtues([	
							parseInt(preloaded.cData.path.virtue1, 10), 
							parseInt(preloaded.cData.path.virtue2, 10), 
							parseInt(preloaded.cData.path.virtue3, 10), 
							parseInt(preloaded.cData.path.virtue4, 10)
						]);
					}

					for(var i = 0; i < preloaded.cData.derangements.length; i++) {
						var derangement = preloaded.cData.derangements[i];
						self.characterSheet.derangements.push({
							data: self.getDerangementById(derangement.derangement_id), 
							description: derangement.description 
						});
					}

					for(var i = 0; i < preloaded.cData.merits.length; i++) {
						var merit = preloaded.cData.merits[i];
						self.characterSheet.merits.push({ data: self.getMeritById(merit.merit_id), description: merit.description });
					}

					for(var i = 0; i < preloaded.cData.flaws.length; i++) {
						var flaw = preloaded.cData.flaws[i];
						self.characterSheet.flaws.push({ data: self.getFlawById(flaw.flaw_id), description: flaw.description });
					}
					if(activeClan) {
						var clanName = activeClan.name;
						switch(clanOptionMap[clanName]) {
							case "toreador":
								if(preloaded.cData.clanOptions.option1.indexOf("Perform") != -1 || preloaded.cData.clanOptions.option1.indexOf("Crafts") != -1) {
									var parts = preloaded.cData.clanOptions.option1.split(": ");
									self.clanOptionValues.toreador[0](parts[0]);
									self.clanOptionValues.toreador[2](parts[1]);
								} else {
									self.clanOptionValues.toreador[0](preloaded.cData.clanOptions.option1);
								}
								if(preloaded.cData.clanOptions.option2.indexOf("Perform") != -1 || preloaded.cData.clanOptions.option2.indexOf("Crafts") != -1) {
									var parts = preloaded.cData.clanOptions.option2.split(": ");
									self.clanOptionValues.toreador[1](parts[0]);
									self.clanOptionValues.toreador[3](parts[1]);
								} else {
									self.clanOptionValues.toreador[1](preloaded.cData.clanOptions.option2);
								}
								self.clearClanOptions();
								self.updateClanOptions();
								break;
							case null:
								break;
							default:
								var clanMapping = clanOptionMap[clanName];
								if(preloaded.cData.clanOptions.option1) self.clanOptionValues[clanMapping][0](preloaded.cData.clanOptions.option1);
								if(preloaded.cData.clanOptions.option2) self.clanOptionValues[clanMapping][1](preloaded.cData.clanOptions.option2);
								if(preloaded.cData.clanOptions.option3) self.clanOptionValues[clanMapping][2](preloaded.cData.clanOptions.option3);
								self.clearClanOptions();
								previousMalkavianData = null;
								self.updateClanOptions();
						}				
					}

					for(var i = 0; i < preloaded.cData.elderPowers.length; i++) {
						var elderPower = preloaded.cData.elderPowers[i];
						var elderPowerData = self.getElderPowerById(elderPower.elder_id);
						self.characterSheet.elderPowers.push({ 
							id: elderPowerData.id, 
							discipline: elderPowerData.discipline_id, 
							name: elderPowerData.name, 
							description: elderPowerData.description 
						});
					}
					for(var i = 0; i < preloaded.cData.comboDisciplines.length; i++) {
						var comboDiscipline = preloaded.cData.comboDisciplines[i];
						var comboDisciplineData = self.getComboDisciplineById(comboDiscipline.combo_id);
						self.characterSheet.comboDisciplines.push({ 
							id: comboDiscipline.id, 
							option1: comboDisciplineData.option1, 
							option2: comboDisciplineData.option2, 
							option3: comboDisciplineData.option3, 
							name: comboDisciplineData.name, 
							description: comboDisciplineData.description 
						});
					}					

					self.characterSheet.name(preloaded.cData.name);
					self.versionComment(preloaded.newVersion ? "" : preloaded.cData.version.comment);
					self.experienceSpent(preloaded.experienceSpent);
					self.totalExperience(preloaded.experienceTotal);
				}

				

				$(".load-curtain").addClass("curtain-fall");
				setTimeout(function() { $(".load-curtain").remove()}, 1500);				
				
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log("Failed to load rulebook", xhr.status, thrownError);
				$(".load-text").html("Failed to load rulebook.<br><span class='load-subtext'>Please try again later.</span>");
			}
		});

	}
	var vm = new chargenVM();
	ko.applyBindings(vm);
	
	ko.bindingHandlers.option = {
	    update: function(element, valueAccessor) {
	       var value = ko.utils.unwrapObservable(valueAccessor());
	       ko.selectExtensions.writeValue(element, value);   
	    }        
	};