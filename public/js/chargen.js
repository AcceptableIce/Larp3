/** Larp3 Charater Gen - @AcceptableIce **/

// Create a blank "character sheet" that we can modify

var characterSheet = {
	sect: {
		selected: undefined,
		displaying: undefined
	},
	clan: {
		selected: undefined,
		displaying: undefined
	},
	nature: undefined,
	abilities: [],
	disciplines: [],
	rituals: [],
	backgrounds: [],
	path: undefined,
	virtues: [1,1,1,1],
	derangements: [],
	merits: [],
	flaws: [],
	hasDroppedMorality: false
}

//Alas, if only Object.Observe was in ECMA6

var RitualList = React.createClass({
	getInitialState: function() {
		return { data: [] }	
	},
	render: function() {
		//Check for Tremere or Necro
		var isNecro = false;
		var canUseRituals = false;
		if(characterSheet.clan.displaying) {
			for(var i in characterSheet.clan.displaying.disciplines) {
				var discipline = characterSheet.clan.displaying.disciplines[i];
				if(discipline.name === "Necromancy") isNecro = true;
			}
			canUseRituals = characterSheet.clan.displaying.name === "Tremere" || isNecro;
		}
		var subsection = <p>You cannot use rituals.</p>
		if(canUseRituals) {
			subsection = <p>You can use rituals!</p>
		}
		return (
			<div className="ritual-list">
				<a name="rituals"></a>
				<h3 data-magellan-destination="rituals">Rituals</h3>
					{subsection}
				<hr/>
			</div>
		)
	}
	
});
var ritualObj = React.render(<RitualList />, document.getElementById("ritual-list"));

//This one is complex. Hold on to your hats.

var DisciplineList = React.createClass({
	getInitialState: function() {
		return { data: [], selected: undefined, chosen: [] } 
	},
	setSelected: function(item) {
		this.setState({ selected: item });
	},
	purchaseRank: function(item) {
		var chosenList = this.state.chosen;
		var exists = false;
		for(var i in chosenList) {
			if(chosenList[i].name === item.name) {
				if(chosenList[i].count) {
					chosenList[i].count++;
				} else {
					chosenList[i].count = 1;
				}
				exists = true;
			}
		}
		if(!exists) {
			item.count = 1;
			chosenList.push(item);
		}
		this.setState({ chosen: chosenList });
		characterSheet.disciplines = chosenList;
		recalculateExperience(); 
		ritualObj.forceUpdate(); //Update the ritual object, since perhaps we just bought Necro
	},
	removeRank: function(item) {
		var chosenList = this.state.chosen;
		for(var i in chosenList) {
			if(chosenList[i].name === item.name) {
				chosenList[i].count--;
				if(chosenList[i].count== 0) {
					delete chosenList[i];
				}
			}
		}
		this.setState({ chosen: chosenList });
		characterSheet.disciplines = chosenList;
		recalculateExperience(); 		
	},
	render: function() {
		//If we haven't selected a clan, display a warning
		if(!characterSheet.clan.selected) {
			return (
				<div id="discipline-list">
					<a name="disciplines"></a>
					<h3 data-magellan-destination="disciplines">Disciplines</h3>
					<h3 className="subheader">No clan selected.</h3>
					<hr />
				</div>
			)
		}

		//This is inefficient but I don't know a better way
		var setSelected = this.setSelected;
		var inClan = this.state.data.map(function(item, index) {
			var inClan = false;
			for(var i = 0; i < characterSheet.clan.selected.disciplines.length; i++) {
				if(characterSheet.clan.selected.disciplines[i] === item.name) {
					return (
						<DisciplineList.Item key={item.name} data={item} index={index} onClick={setSelected} options={item.values} />
					)
				}
			}
		});


		var offClan = this.state.data.map(function(item, index) {
			var inClan = false;
			for(var i = 0; i < characterSheet.clan.selected.disciplines.length; i++) {
				if(characterSheet.clan.selected.disciplines[i] === item.name) {
					inClan = true;
				}
			}
			if(!inClan) {
				return (
					<DisciplineList.Item key={item.name} data={item} index={index} onClick={setSelected}  options={item.values} />
				)
			}
		});

		var info = "";
		if(this.state.selected) {
			var count = 0;
			for(var i in this.state.chosen) {
				var item = this.state.chosen[i];
				if(item.name == this.state.selected.name) count = item.count;
			}
			info = <DisciplineList.Info data={this.state.selected} count={count} onClick={this.purchaseRank} />
		}
		
		var disciplineList = [];
		for(var i in this.state.chosen) {
			var item = this.state.chosen[i];
			//Find the discipline's data
			disciplineList.push(<DisciplineList.SelectedItem data={item} header={true} />);
			for(j = 0; j < item.count; j++) {
				disciplineList.push(<DisciplineList.SelectedItem data={item.ranks[j]} discipline={item} removable={j == item.count - 1} onClick={this.removeRank} header={false} />);
			}
		}
		
		return (
			<div id="discipline-list">
				<a name="disciplines"></a>
				<h3 data-magellan-destination="disciplines">Disciplines</h3>
				<div className="row">
					<div className="small-4 columns">
						<ul className="discipline-list accordion" data-accordion>
							<li className="discipline-sublist accordion-navigation">
								<a href="#discipline-panel-1" className="clan-discipline-subitem">Clan Disciplines</a>
								<div id="discipline-panel-1"className="content clan-discipline-sublist-options">{inClan}</div>
							</li>
							<li className="discipline-sublist accordion-navigation">
								<a href="#discipline-panel-2" className="off-clan-discipline-subitem">Off-Clan Disciplines</a>
								<div id="discipline-panel-2"  className="content clan-discipline-sublist-options">{offClan}</div>
							</li>	
						</ul>
					</div>
					<div className="small-4 columns">
						{info}
					</div>
					<div className="small-4 columns">
						<h4>My Disciplines</h4>
						{disciplineList}
					</div>
				</div>
				<hr/>

			</div>
		)
	}
});


DisciplineList.Item = React.createClass({
	handleClick: function(data) {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="ability-item" onClick={this.handleClick}>{this.props.data.name}</div>
		)
	}
})

DisciplineList.SelectedItem = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.discipline);	
	},
	render: function() {
		if(this.props.header) {
			return ( 
				<div className="discipline-selected-header"><b>{this.props.data.name}</b></div>
			);
		} else {
			var removeButton = this.props.removable ? <div className="remove-button" onClick={this.handleClick}>&times;</div> : <div className="discipline-selected-spacer"></div>;

			return (
				<div className="discipline-selected-item">
					{removeButton} {this.props.data.name}
					
				</div>	
			);
		}
	}
})

DisciplineList.Info = React.createClass({
	handleClick: function(data) {
		this.props.onClick(this.props.data);
	},
	render: function() {
		var nextLevel = this.props.data.ranks[this.props.count]; //woo 0 based
		var nextLevelInfo = "You have reached the maximum level for this discipline.";
		if(nextLevel) {
			nextLevelInfo = <div className="next-level-info">The next ability is <b>{nextLevel.name}</b>.<br />
				<i>{nextLevel.description}</i><br />
				<input type="button" className="button small" onClick={this.handleClick} value={"Purchase " + nextLevel.name} />
			</div>
		}
		return (
			<div className="discipline-info">
				<b><i>{this.props.data.name}</i></b><br />
				Retests with {this.props.data.retest}<br />
				You have {this.props.count} levels of {this.props.data.name}.<br /><br />
				{nextLevelInfo}
				
			</div>
		)
	}
});


//This has to go here. It's sad but it's true.
var disciplineObj = React.render(<DisciplineList />, document.getElementById("discipline-list"));

var ClanList = React.createClass({
	getInitialState: function() {
		return { data: [], selected: undefined, chosen: undefined, displaying: undefined } 
	},
	handleClick: function(item) {
		this.setState({ selected: item });
	},
	handleClanSelection: function(item) {
		characterSheet.clan.selected = item;
		this.setState({ chosen: item });
		disciplineObj.forceUpdate();
		ritualObj.forceUpdate(); //Update the ritual object, since perhaps we just took Tremere
		if(!this.state.displaying) {
			characterSheet.clan.displaying = item;
			this.setState({ displaying: item });			
		}
		recalculateExperience(); 
	},
	handleClanDisplaySelection: function(item) {
		characterSheet.clan.displaying = item;
		this.setState({ displaying: item });
		if(!this.state.chosen) {
			characterSheet.clan.selected = item;
			this.setState({ chosen: item });
			disciplineObj.forceUpdate();
			ritualObj.forceUpdate(); //Update the ritual object, since perhaps we just took Tremere
		}
		recalculateExperience(); 
	},
	render: function() {
		//If we haven't selected a sect, display a warning
		if(!characterSheet.sect.selected) {
			return (
				<div id="clan-list">
					<a name="clans"></a>
					<h3 data-magellan-destination="clans">Clans</h3>
					<h3 className="subheader">No sect selected.</h3>
					<hr />
				</div>
			)
		}
		var onClick = this.handleClick;
		items = [
			<ClanList.Sublist key="common_clans" name="Common Clans" index="0" onClick={onClick} options={characterSheet.sect.selected.common_clans} />,
			<ClanList.Sublist key="uncommon_clans" name="Uncommon Clans" index="1" onClick={onClick} options={characterSheet.sect.selected.uncommon_clans} />
		];
		/*var items = characterSheet.sect.selected.clans.map(function(item, index) {
			return (
				<ClanList.Sublist key={item.name} name={item.name} index={index} onClick={onClick} options={item.options} />
			)
		});*/
		var info = this.state.selected ? <ClanList.Info data={this.state.selected}  onDisplayClick={this.handleClanDisplaySelection} onSubmit={this.handleClanSelection} /> : "";

		return (
			<div id="clan-list">
				<a name="clan"></a>
				<h3 data-magellan-destination="clan">Clan { this.state.chosen ? "(" + this.state.chosen.name + ")" : "" } { this.state.displaying ? "(Displaying as " + this.state.displaying.name + ")" : "" }</h3>
				<div className="row">
					<div className="small-4 columns">
						<ul className="clan-list accordion" data-accordion>
						{items}
						</ul>
					</div>
					<div className="small-8 columns">
						{info}
					</div>
				</div>
				<hr/>

			</div>
		)
	}
});

ClanList.Info = React.createClass({
	handleClick: function(item) {
		this.props.onSubmit(this.props.data);
	},
	handleDisplayClick: function(item) {
		this.props.onDisplayClick(this.props.data);
	},
	render: function() {
		var disciplines = this.props.data.disciplines.map(function(item, index) {
			return (
				<ClanList.Discipline value={item} />
			)
		});
		return (
			<div className="clan-info row">
				<div className="small-8 columns">
					<b>Clan {this.props.data.name}</b>
					<p>{this.props.data.description}</p>
					<input type="button" className="button small" value={"Select Clan " + this.props.data.name} onClick={this.handleClick}/><br/>
					<input type="button" className="button tiny" value={"Display as Clan " + this.props.data.name} onClick={this.handleDisplayClick}/>

				</div>
				<div className="small-4 columns">
					<b>Disciplines</b>
					{disciplines}
				</div>
				
			</div>
		)
	}
});

ClanList.Discipline = React.createClass({
	render: function() {
		return (
			<div className="clan-discipline">{this.props.value}</div>
		)
	}
})

ClanList.Sublist = React.createClass({
	render: function() {
		var onClick = this.props.onClick;
		var items = this.props.options.map(function(item, index) {
			return (
				<ClanList.Item key={item} value={item} onClick={onClick} />
			)
		});
		return (
			<li className="clan-sublist accordion-navigation">
				<a href={"#clan-panel" + this.props.index } className="clan-subitem">{this.props.name}</a>
				<div id={"clan-panel" + this.props.index } className="content clan-sublist-options">{items}</div>
			</li>
		)
	}
})

ClanList.Item = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.value);	
	},
	render: function() {
		
		return (
			<div id={ "clan-item" + this.props.index } onClick={this.handleClick}>{this.props.value.name}</div>
		)
	}
})

var clanObj = React.render(<ClanList />, document.getElementById("clan-list"));

/** Sects **/

var SectList = React.createClass({
	getInitialState: function() {
		return { data: [], selected: undefined, displaying: undefined } 
	},
	handleClick: function(item) {
		characterSheet.sect.selected = item;
		this.setState({ selected: item.name });	
		if(!this.state.displaying) {
			characterSheet.sect.displaying = item;
			this.setState({ displaying: item.name });	
		}
		clanObj.forceUpdate();
		recalculateExperience(); 
	},
	handleDisplayClick: function(item) {
		characterSheet.sect.displaying = item;
		this.setState({ displaying: item.name });	
		if(!this.state.selected) {
			characterSheet.sect.selected = item;
			this.setState({ selected: item.name });	
			clanObj.forceUpdate();
		}	
		recalculateExperience(); 	
	},
	render: function() {
		//Get variables within the closure
		var selected = this.state.selected;
		var displaying = this.state.displaying;
		var onClick = this.handleClick;
		var onDisplay = this.handleDisplayClick;
		var items = this.state.data.map(function(item, index) {
			return (
				<SectList.Item onClick={onClick} onDisplayRequest={onDisplay} selected={ item.name === selected } displaying={ item.name === displaying} key={item.name} value={item} />
			)
		});
		return (
			<div className="sect-list">
				<a name="sect"></a>
				<h3 data-magellan-destination="sect">Sect { this.state.selected ? "(" + this.state.selected + ")" : "" } {this.state.displaying ? " (Displaying as " + this.state.displaying + ")" : "" }</h3>
				<div className="row">{items}</div>
				<hr />
			</div>
		)
	}
});

SectList.Item = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.value);	
	},
	handleDisplayClick: function() {
		this.props.onDisplayRequest(this.props.value);
	},
	render: function() {
		return (
			<div className="sect-item small-3 columns">
				<i><b>{this.props.value.name}</b></i>
				<p>{this.props.value.description}</p>
				<input type="button" className={"button small" + (this.props.selected ? " disabled" : "")}  onClick={this.handleClick} value={"Select " + this.props.value.name} />
				<input type="button" className={"button tiny " + (this.props.displaying ? " disabled" : "")}  onClick={this.handleDisplayClick} value={"Display as " + this.props.value.name} />
			</div>
		)
	}
})

var NatureList = React.createClass({
	getInitialState: function() {
		return { data: [], selected: undefined }
	},
	handleSelection: function(item) {
		this.setState({ selected: item.name });
		characterSheet.nature = item.name;
		recalculateExperience(); 
	},
	render: function() {
		var onClick = this.handleSelection;
		var selected = this.state.selected;
		var items = this.state.data.map(function(item, index) {
			return (
				<NatureList.Item key={item.name} value={item} selected={item.name === selected} onClick={onClick}/>
			)
		});
		return (
			<div className="nature-list">
				<a name="nature"></a>
				<h3 data-magellan-destination="nature">Nature {this.state.selected ? "(" + this.state.selected + ")" : "" }</h3>
				<ul className="small-block-grid-3">
					{items}
				</ul>
				<hr />
			</div>
		)
	}
});



NatureList.Item = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.value);	
	},
	render: function() {
		return (
			<li className="nature-item">
				<b>{this.props.value.name}</b>
				<p>{this.props.value.description}</p>
				<input type="button" className={"button small" + (this.props.selected ? " disabled" : "")} value={"Select " + this.props.value.name} onClick={this.handleClick}/>

			</li>
		)
	}
});

var AbilityList = React.createClass({
	getInitialState: function() {
		return { data: [], selectedAbilities: [] } 
	},
	selectAbility: function(ability) {
		var selAbil = this.state.selectedAbilities;
		ability.count = 1;
		selAbil.push(ability);
		this.setState({ selectedAbilities: selAbil });
		characterSheet.abilities = selAbil;
		recalculateExperience(); 
	},
	onMinus: function(ability) {
		var abilityList = this.state.selectedAbilities;
		for(var i = 0; i < abilityList.length; i++) {
			if(abilityList[i].name === ability.name) {
				abilityList[i].count--;
				if(abilityList[i].count === 0) {
					//Remove it
					abilityList.splice(i, 1);
				}
			}
		}
		this.setState({ selectedAbilities: abilityList });
		characterSheet.abilities = abilityList;
		recalculateExperience(); 
	},
	onPlus: function(ability) {
		var abilityList = this.state.selectedAbilities;
		for(var i = 0; i < abilityList.length; i++) {
			if(abilityList[i].name === ability.name) {
				abilityList[i].count++;
				if(abilityList[i].count > 5) {
					abilityList[i].count = 5;
				}
			}
		}
		this.setState({ selectedAbilities: abilityList });
		characterSheet.abilities = abilityList;
		recalculateExperience(); 
	},	
	render: function() {
		var selectAbilityRef = this.selectAbility;
		var onMinusRef = this.onMinus;
		var onPlusRef = this.onPlus;
		var selectedAbilityList = this.state.selectedAbilities;
		var items = this.state.data.map(function(item, index) {
			return (
				<AbilityList.Sublist key={item.name} name={item.name} index={index} abilityList={selectedAbilityList} onClick={selectAbilityRef} options={item.options} />
			)
			
		});
		var selAbilities = this.state.selectedAbilities.map(function(item, index) {
			return (
				<AbilityList.SelectedAbility key={item.name + item.count} data={item} onMinus={onMinusRef} onPlus={onPlusRef} />
			)
		})
		return (
			<div className="ability-list">
				<a name="abilities"></a>
				<h3 data-magellan-destination="abilities">Abilities</h3>
				<div className="row">
					<div className="small-4 column">
						<h4 className="subheader">Available Abilities</h4>
						<ul className="ablity-accordion accordion" data-accordion>
							{items}
						</ul>
					</div>
					<div className="small-8 column">
						<h4 className="subheader">Selected Abilities</h4>
						<ul className="small-block-grid-3">
							{selAbilities}
						</ul>
					</div>
				</div>
				<hr />
			</div>
		)
	}
});

AbilityList.SelectedAbility = React.createClass({
	onMinus: function() {
		this.props.onMinus(this.props.data);
	},
	onPlus: function() {
		this.props.onPlus(this.props.data);
	},
	render: function() {
		return (
			<li className="panel callout ability-list-selected">
				<div className="ability-list-icon plus" onClick={this.onPlus}>+</div>
				<b className="ability-list-selected-name">{this.props.data.name} ({this.props.data.count})</b>
				<div className="ability-list-icon minus" onClick={this.onMinus}>-</div>
			</li>
		)
	}	
});

AbilityList.Sublist = React.createClass({
	handleClick: function(data) {
		this.props.onClick(data);
	},
	render: function() {
		var onClick = this.handleClick; //trickle down like reagan
		var abilityList = this.props.abilityList;
		var items = this.props.options.map(function(item, index) {
			var exists = false;
			for(var i = 0; i < abilityList.length; i++) {
				if(abilityList[i].name === item.name) exists = true;
			}
			if(!exists) {
				return (
					<AbilityList.Item key={item.name} data={item} onClick={onClick} />
				)
			}
		});
		return (
			<li className="ability-sublist accordion-navigation">
				<a href={"#ability-panel" + this.props.index } className="ability-subitem">{this.props.name}</a>
				<div id={"ability-panel" + this.props.index } className="content ability-sublist-options">{items}</div>
			</li>
		)
	}
})

AbilityList.Item = React.createClass({
	handleClick: function(data) {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="ability-item" onClick={this.handleClick}>{this.props.data.name}</div>
		)
	}
})

var BackgroundList = React.createClass({
	getInitialState: function() {
		return { data: [], selectedBackgrounds: [] } 
	},
	selectBackground: function(ability) {
		var selBack = this.state.selectedBackgrounds;
		ability.count = 1;
		selBack.push(ability);
		this.setState({ selectedBackgrounds: selBack });
		characterSheet.backgrounds = selBack;
		recalculateExperience(); 
	},
	onMinus: function(background) {
		var backgroundList = this.state.selectedBackgrounds;
		for(var i = 0; i < backgroundList.length; i++) {
			if(backgroundList[i].name === background.name) {
				backgroundList[i].count--;
				if(backgroundList[i].count === 0) {
					//Remove it
					backgroundList.splice(i, 1);
				}
			}
		}
		this.setState({ selectedBackgrounds: backgroundList });
		characterSheet.backgrounds = backgroundList;
		recalculateExperience(); 
	},
	onPlus: function(background) {
		var backgroundList = this.state.selectedBackgrounds;
		for(var i = 0; i < backgroundList.length; i++) {
			if(backgroundList[i].name === background.name) {
				backgroundList[i].count++;
				if(backgroundList[i].count > 5) {
					backgroundList[i].count = 5;
				}
			}
		}
		this.setState({ selectedBackgrounds: backgroundList });
		characterSheet.backgrounds = backgroundList;
		recalculateExperience(); 
	},	
	render: function() {
		var selectedBackgroundRef = this.selectBackground;
		var onMinusRef = this.onMinus;
		var onPlusRef = this.onPlus;
		var selectedBackgroundList = this.state.selectedBackgrounds;
		var items = this.state.data.map(function(item, index) {
			return (
				<BackgroundList.Sublist key={item.name} name={item.name} index={index} backgroundList={selectedBackgroundList} onClick={selectedBackgroundRef} options={item.options} />
			)
			
		});
		var selBackgrounds = this.state.selectedBackgrounds.map(function(item, index) {
			return (
				<BackgroundList.SelectedBackground key={item.name + item.count} data={item} onMinus={onMinusRef} onPlus={onPlusRef} />
			)
		})
		return (
			<div className="background-list">
				<a name="backgrounds"></a>
				<h3 data-magellan-destination="backgrounds">Backgrounds</h3>
				<div className="row">
					<div className="small-4 column">
						<h4 className="subheader">Available Backgrounds</h4>
						<ul className="background-accordion accordion" data-accordion>
							{items}
						</ul>
					</div>
					<div className="small-8 column">
						<h4 className="subheader">Selected Backgrounds</h4>
						<ul className="small-block-grid-3">
							{selBackgrounds}
						</ul>
					</div>
				</div>
				<hr />
			</div>
		)
	}
});

BackgroundList.SelectedBackground = React.createClass({
	onMinus: function() {
		this.props.onMinus(this.props.data);
	},
	onPlus: function() {
		this.props.onPlus(this.props.data);
	},
	render: function() {
		return (
			<li className="panel callout ability-list-selected">
				<div className="ability-list-icon plus" onClick={this.onPlus}>+</div>
				<b className="ability-list-selected-name">{this.props.data.name} ({this.props.data.count})</b>
				<div className="ability-list-icon minus" onClick={this.onMinus}>-</div>
			</li>
		)
	}	
});

BackgroundList.Sublist = React.createClass({
	handleClick: function(data) {
		this.props.onClick(data);
	},
	render: function() {
		var onClick = this.handleClick; //trickle down like reagan
		var backgroundList = this.props.backgroundList;
		var items = this.props.options.map(function(item, index) {
			var exists = false;
			for(var i = 0; i < backgroundList.length; i++) {
				if(backgroundList[i].name === item.name) exists = true;
			}
			if(!exists) {
				return (
					<BackgroundList.Item key={item.name} data={item} onClick={onClick} />
				)
			}
		});
		return (
			<li className="background-sublist accordion-navigation">
				<a href={"#background-panel" + this.props.index } className="background-subitem">{this.props.name}</a>
				<div id={"background-panel" + this.props.index } className="content background-sublist-options">{items}</div>
			</li>
		)
	}
})

BackgroundList.Item = React.createClass({
	handleClick: function(data) {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="background-item" onClick={this.handleClick}>{this.props.data.name}</div>
		)
	}
});

var TraitList = React.createClass({
	getInitialState: function() {
		return { traits: [3, 3, 3] }
	},
	render: function() {
		var physicalTraits = [];
		var mentalTraits = [];
		var socialTraits = [];
		for(var i = 0; i < 13; i++) {
			physicalTraits.push(<TraitList.TraitBox key={i} index={i} type="physical" filled={i < this.state.traits[0] } at={this.state.traits[0]} />)
		}

		return (
			<div className="attribute-list">
				<a name="attributes"></a>
				<h3 data-magellan-destination="attributes">Attributes</h3>
				<div className="row">
					<div className="small-12 column">
						<p>	A character's attributes are split into three categories: physical, mental, and social traits. 
						   	At character creation, each category must be specfied as of primary, secondary, or tertiary importance. 
						   	You get 15 points to spend. We recommend seven free traits in your primary category, five in your secondary, and three in your tertiary. 
						   	Additional traits can be purchased for one experience point each.</p>
						<div className="attribute-row">
							<h4>Physical</h4>
							<div className="trait-row">{physicalTraits}</div>

							<h4>Mental</h4>
							<div className="trait-row">{mentalTraits}</div>

							<h4>Social</h4>
							<div className="trait-row">{socialTraits}</div>
						</div>
					</div>
				</div>
				<hr />
			</div>
		)
	}
});

TraitList.TraitBox = React.createClass({
	render: function() {
		var displayCount = this.props.index === this.props.at - 1 ? this.props.at : "";
		return (
			<div className={"trait-box " + this.props.type + (this.props.filled ? " filled" : "")}>
				{displayCount}
			</div>
		)
	}
});

var PathList = React.createClass({
	getInitialState: function() {
		return { data: [], selected: undefined, chosen: undefined, statCounts: [1, 1, 1, 1] };
	},
	clickPath: function(data) {
		this.setState({ selected: data });
	},
	selectPath: function(data) {
		this.setState({ chosen: data });
		//Update character sheet
		characterSheet.path = data;
		recalculateExperience(); 
	},
	modifyVirtue: function(place, amount) {
		var counts = this.state.statCounts;
		if(place === 2) {
			//Trying to tick morality
			if(characterSheet.hasDroppedMorality) {
				if(amount == -1 && counts[place] != 1) {
					console.log("Alert: Cannot drop morality twice.")
					return;
				} else {
					characterSheet.hasDroppedMorality = false;
				}
			} else {
				if(amount == 1) {
					console.log("Alert: Can only raise morality if it was previously dropped.");
					return;
				} else {
					characterSheet.hasDroppedMorality = true;
				}
			}
		}
		counts[place] += amount;
		if(counts[place] < 1) counts[place] = 1;
		if(counts[place] > 5 ) counts[place] = 5;
		//Recalculate morality
		counts[2] = Math.ceil((counts[0] + counts[1]) / 2) - (characterSheet.hasDroppedMorality ? 1 : 0) ;
		this.setState({ statCounts: counts });
		//Update character sheet
		characterSheet.virtues[place] = counts[place];
		recalculateExperience(); 
	},
	render: function() {
		var ref = this;
		var paths = this.state.data.map(function(item, index) {
			return (
				<PathList.Path key={item.name} data={item} onClick={ref.clickPath}/>
			)
			
		});
		var info = this.state.selected ? <PathList.Info data={this.state.selected} onClick={this.selectPath} /> : ""
		var virtues = this.state.chosen ? <PathList.Virtues data={this.state.chosen} statCounts={this.state.statCounts} onClick={this.modifyVirtue} /> : <b className="subheader">No path selected</b>
		return (
			<div className="path-list">
				<a name="path"></a>
				<h3 data-magellan-destination="path">Path and Virtues {this.state.chosen ? "(" + this.state.chosen.name + ")" : ""}</h3>
				<div className="row">
					<div className="small-4 column">
						<h4 className="subheader">Available Morality Paths</h4>
						{paths}
					</div>
					<div className="small-4 column">
						{info}
					</div>
					<div className="small-4 column">
						<h4 className="subheader">Virtues</h4>
						{virtues}
					</div>
				</div>
				<hr />
			</div>

		)
	}
})

PathList.Virtues = React.createClass({
	onPlus: function(val) {
		this.props.onClick(val, 1);
	},
	onMinus: function(val) {
		this.props.onClick(val, -1);
	},
	render: function() {
		var ref = this;
		return (
			<div id="virtue-list">
				<p>	At character creation, you have seven points to spread among your three virtues and Morality will be the average of the first two. 
					Thereafter, you can purchase Morality traits for two experience and Virtue traits for three. </p>
				<div className="panel virtue-item">
					<div className="ability-list-icon plus" onClick={function() { ref.onPlus(0) } }>+</div>
					<b className="ability-list-selected-name">{this.props.data.stats[0]} ({this.props.statCounts[0]})</b>
					<div className="ability-list-icon minus" onClick={function() { ref.onMinus(0) } }>-</div>
				</div>
				<div className="panel virtue-item">
					<div className="ability-list-icon plus" onClick={function() { ref.onPlus(1) } }>+</div>
					<b className="ability-list-selected-name">{this.props.data.stats[1]} ({this.props.statCounts[1]})</b>
					<div className="ability-list-icon minus" onClick={function() { ref.onMinus(1) } }>-</div>
				</div>
				<div className="panel virtue-item">
					<div className="ability-list-icon plus" onClick={function() { ref.onPlus(2) } }>+</div>
					<b className="ability-list-selected-name">{this.props.data.stats[2]} ({this.props.statCounts[2]})</b>
					<div className="ability-list-icon minus" onClick={function() { ref.onMinus(2) } }>-</div>
				</div>
				<div className="panel virtue-item">
					<div className="ability-list-icon plus" onClick={function() { ref.onPlus(3) } }>+</div>
					<b className="ability-list-selected-name">{this.props.data.stats[3]} ({this.props.statCounts[3]})</b>
					<div className="ability-list-icon minus" onClick={function() { ref.onMinus(3) } }>-</div>
				</div>												
			</div>
		)
	}
});

PathList.Info = React.createClass({
	handleClick: function () {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="path-info">
				<h4>{this.props.data.name}</h4>
				<i>{this.props.data.description}</i><br />
				<p><b>Sins:</b><br />
				5.{this.props.data.sins[0]}<br />
				4.{this.props.data.sins[1]}<br />
				3.{this.props.data.sins[2]}<br />
				2.{this.props.data.sins[3]}<br />
				1.{this.props.data.sins[4]}</p>
				<input type="button" className="button small" value={"Select " + this.props.data.name} onClick={this.handleClick} />
			</div>
		)
	}
});

PathList.Path = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="path-item" onClick={this.handleClick}>{this.props.data.name}</div>
		)
	}

});

var DerangementList = React.createClass({
	getInitialState: function() {
		return { data: [], selected: undefined, chosen: [] }
	},
	handleSelection: function(data) {
		this.setState({ selected: data });
	},
	handleChoice: function(data) {
		var chosenList = this.state.chosen;
		var found = false;
		for(var i in chosenList) {
			if(chosenList[i].name == data.name) {
				chosenList.splice(i, 1);
				found = true;
			}
		}
		if(!found) chosenList.push(data);
		this.setState({ chosen: chosenList, selected: undefined });
		characterSheet.derangements = chosenList;
		recalculateExperience();
	},
	render: function() {
		var ref = this;
		var list = this.state.data.map(function(item, index) {
			//Check if the derangement has already been selected
			var found = false;
			for(var i in ref.state.chosen) {
				if(ref.state.chosen[i].name == item.name) {
					found = true;
				}
			}
			if(found) return "";
			return (
				<DerangementList.Item key={item.name} data={item} onClick={ref.handleSelection} />
			)
		});

		var chosenList = this.state.chosen.map(function(item, index) {
			return (
				<DerangementList.Chosen key={item.name} data={item} onClick={ref.handleSelection} />
			)
		});

		var info = this.state.selected ? <DerangementList.Info data={this.state.selected} chosenList={this.state.chosen} onClick={this.handleChoice} /> : "";
		return (
			<div className="derangement-list">
				<a name="derangements"></a>
				<h3 data-magellan-destination="derangements">Derangements</h3>
				<div className="row">
					<div className="small-4 column">
						<h4 className="subheader">Available Derangements</h4>
						{list}
					</div>
					<div className="small-4 column">
						{info}
					</div>
					<div className="small-4 column">
						<h4 className="subheader">Selected Derangements</h4>
						{chosenList}
					</div>
				</div>
				<hr />
			</div>
		)
	}

});

DerangementList.Item = React.createClass({ 
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="derangement-item" onClick={this.handleClick}>
				{this.props.data.name}
			</div>
		)
	}
});

DerangementList.Info = React.createClass({ 
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		//Should it be a select or a remove?
		var found = false;
		for(var i in this.props.chosenList) {
			if(this.props.chosenList[i].name == this.props.data.name) {
				found = true;
			}
		}
		var buttonActionText = found ? "Remove " + this.props.data.name : "Select " + this.props.data.name;
		return (
			<div className="derangement-info">
				<h4>{this.props.data.name}</h4>
				<p>{this.props.data.description}</p>
				<input type="button" className="button small" onClick={this.handleClick} value={buttonActionText} />
			</div>
		)
	}
});

DerangementList.Chosen = React.createClass({ 
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="derangement-chosen" onClick={this.handleClick}>
				{this.props.data.name}
			</div>
		)
	}
});

var MeritAndFlawList = React.createClass({
	getInitialState: function() {
		return { merits: [], flaws: [], viewingMerit: undefined, viewingFlaw: undefined, selectedMerits: [], selectedFlaws: [] }
	},
	viewMerit: function(data) {
		this.setState({ viewingMerit: data });
	},
	selectMerit: function(data) {
		var chosenList = this.state.selectedMerits;
		var found = false;
		for(var i in chosenList) {
			if(chosenList[i].name == data.name) {
				chosenList.splice(i, 1);
				found = true;
			}
		}
		if(!found) chosenList.push(data);
		this.setState({ selectedMerits: chosenList, viewingMerit: undefined });
		characterSheet.merits = chosenList;
		recalculateExperience(); 
	},
	viewFlaw: function(data) {
		this.setState({ viewingFlaw: data });
	},
	selectFlaw: function(data) {
		var chosenList = this.state.selectedFlaws;
		var found = false;
		for(var i in chosenList) {
			if(chosenList[i].name == data.name) {
				chosenList.splice(i, 1);
				found = true;
			}
		}
		if(!found) chosenList.push(data);
		this.setState({ selectedFlaws: chosenList, viewingFlaw: undefined });
		characterSheet.flaws = chosenList;
		recalculateExperience(); 
	},
	render: function() {
		var ref = this;
		var count = 0;
		var meritList = this.state.merits.map(function(item, index) {
			return (
				<MeritAndFlawList.MeritSublist key={item.name} name={item.name} index={count++} meritList={ref.state.selectedMerits} onClick={ref.viewMerit} options={item.options} />
			)
		});

		var selectedMeritList = this.state.selectedMerits.map(function(item, index) {
			return (
				<MeritAndFlawList.SelectedMerit key={item.name} data={item} onClick={ref.viewMerit} />
			)
		});
		var meritInfo = this.state.viewingMerit ? <MeritAndFlawList.MeritInfo data={this.state.viewingMerit} meritList={this.state.selectedMerits} onClick={this.selectMerit} /> : "";

		var flawList = this.state.flaws.map(function(item, index) {
			return (
				<MeritAndFlawList.MeritSublist key={item.name} name={item.name} index={count++} meritList={ref.state.selectedFlaws} onClick={ref.viewFlaw} options={item.options} />
			)
		});

		var selectedFlawList = this.state.selectedFlaws.map(function(item, index) {
			return (
				<MeritAndFlawList.SelectedMerit key={item.name} data={item} onClick={ref.viewFlaw} />
			)
		});
		var flawInfo = this.state.viewingFlaw ? <MeritAndFlawList.MeritInfo data={this.state.viewingFlaw} meritList={this.state.selectedFlaws} onClick={this.selectFlaw} /> : "";

		return (
			<div className="merit-and-flaw-list">
				<a name="merits-and-flaws"></a>
				<h3 data-magellan-destination="merits-and-flaws">Merits and Flaws</h3>
				<div className="row">
					<div className="small-4 column">
						<h4 className="subheader">Available Merits</h4>
						<ul className="merit-accordion accordion" data-accordion>
							{meritList}
						</ul>
					</div>
					<div className="small-4 column">
						{meritInfo}
					</div>
					<div className="small-4 column">
						<h4 className="subheader">Selected Merits</h4>
						{selectedMeritList}
					</div>
				</div>
				<div className="merit-divider"></div>
				<div className="row">
					<div className="small-4 column">
						<h4 className="subheader">Available Flaws</h4>
						<ul className="merit-accordion accordion" data-accordion>
							{flawList}
						</ul>						
					</div>
					<div className="small-4 column">
						{flawInfo}
					</div>
					<div className="small-4 column">
						<h4 className="subheader">Selected Flaws</h4>
						{selectedFlawList}
					</div>
				</div>
				<hr />
			</div>
		)
	}
});

MeritAndFlawList.SelectedMerit = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="merit-item" onClick={this.handleClick}>{this.props.data.cost}: {this.props.data.name}</div>
		)		
	}
});

MeritAndFlawList.MeritInfo = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		var exists = false;
		for(var i = 0; i < this.props.meritList.length; i++) {
			if(this.props.meritList[i].name === this.props.data.name) exists = true;
		}
		var buttonActionText = exists ? "Remove " : "Select ";
		return (
			<div className="merit-info">
				<h4>{this.props.data.name}</h4>
				<b>{this.props.data.cost} Experience</b>
				<p>{this.props.data.description}</p>
				<input type="button" className="button small" onClick={this.handleClick} value={ buttonActionText + this.props.data.name } />
			</div>
		)
	}
});

MeritAndFlawList.MeritSublist = React.createClass({
	handleClick: function(data) {
		this.props.onClick(data);
	},
	render: function() {
		var ref = this;
		var items = this.props.options.map(function(item, index) {
			var exists = false;
			for(var i = 0; i < ref.props.meritList.length; i++) {
				if(ref.props.meritList[i].name === item.name) exists = true;
			}
			if(!exists) {
				return (
					<MeritAndFlawList.MeritItem key={item.name} data={item} onClick={ref.handleClick} />
				)
			}
		});
		return (
			<li className="merit-sublist accordion-navigation">
				<a href={"#merit-panel" + this.props.index } className="merit-subitem">{this.props.name}</a>
				<div id={"merit-panel" + this.props.index } className="content merit-sublist-options">{items}</div>
			</li>
		)
	}
})

MeritAndFlawList.MeritItem = React.createClass({
	handleClick: function() {
		this.props.onClick(this.props.data);
	},
	render: function() {
		return (
			<div className="merit-item" onClick={this.handleClick}>{this.props.data.cost}: {this.props.data.name}</div>
		)
	}
})

var sectObj = React.render(<SectList />, document.getElementById("sect-list"));
var natureObj = React.render(<NatureList />, document.getElementById("nature-list"));
var abilityObj = React.render(<AbilityList />, document.getElementById("ability-list"));
var traitObj = React.render(<TraitList />, document.getElementById("attributes-list"));
var backgroundObj = React.render(<BackgroundList />, document.getElementById("background-list"));
var pathObj = React.render(<PathList />, document.getElementById("path-list"));
var derangementObj = React.render(<DerangementList />, document.getElementById("derangement-list"));
var meritsObj = React.render(<MeritAndFlawList />, document.getElementById("merit-and-flaw-list"));

//Load in the rulebook data
$.ajax({
	url: "/rulebook",
	dataType: 'json',
	success: function(data) {
		sectObj.setState( { data: data.sects });
		clanObj.setState( { data: data.clans });
		natureObj.setState( { data: data.natures });
		abilityObj.setState( { data: data.abilities });
		disciplineObj.setState( { data: data.disciplines });
		backgroundObj.setState( { data: data.backgrounds });
		pathObj.setState( { data: data.paths });	
		derangementObj.setState( { data: data.derangements });
		meritsObj.setState({ merits: data.merits, flaws: data.flaws });
		ritualObj.setState({ data: data.rituals });
		$(document).foundation();
	},
	error: function(xhr, ajaxOptions, thrownError) {
		console.log("Failed to load rulebook", xhr.status, thrownError);
	}
});