<?
	$user = Auth::user();
	$is_st = $user->isStoryteller();
?>
@extends('layout')
@section('title', 'Character Generator')

@section('includes')
<link rel="stylesheet" type="text/css" href="/css/generator.css">
@stop

@section('content')
<div class="load-curtain">
	<div class="load-text"><span class="load-text-content"></span><br>
		<div class="anim"></div>
	</div>
</div>
<div id="main-modal" class="reveal-modal" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
  <h2 id="modalTitle" data-bind="text: $root.modal.title"></h2>
  <div data-bind="html: $root.modal.body"></div>
  <!-- ko if: $root.modal.mode() == 1 -->
  	<hr>
  	<button class="button small" data-bind="click: function() { $('#main-modal').foundation('reveal', 'close'); }">No</button>
  	<button class="button success small" data-bind="click: function() { $root.modal.callback(); $('#main-modal').foundation('reveal', 'close'); }">Yes</button>
  <!-- /ko -->
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>

<div id="input-modal" class="reveal-modal" data-reveal aria-labelledby="inputModalTitle" aria-hidden="true" role="dialog">
  <h2 id="inputModalTitle" data-bind="text: $root.inputModal.title"></h2>
  <div data-bind="html: $root.inputModal.body"></div>
  <input type="text" data-bind="value: $root.inputModal.value, attr: {'placeholder': $root.inputModal.placeholder() }" />
  <hr>
  <button class="button small" data-bind="click: function() { $root.inputModal.callback($root.inputModal.value()); $('#input-modal').foundation('reveal', 'close'); }">Submit</button>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div id="combo-modal" class="reveal-modal" data-reveal aria-labelledby="comboModalTitle" aria-hidden="true" role="dialog">
  <h2 id="comboModalTitle">Combo Discipline</h2>
  <label for="combo-name">Name (Required)
 	 	<input type="text" name="combo-name" data-bind="value: $root.comboModal.name" />
  </label>
  <label for="combo-opt-1">Discipline 1 (Required)
  		<select data-bind="groupedOptions: { groups: { coll: $root.listAllDisciplines }, optionsCaption: '- Please select -'}, value: $root.comboModal.option1"></select>
  </label>
  <label for="combo-opt-1">Discipline 2 (Required)
  		<select data-bind="groupedOptions: { groups: { coll: $root.listAllDisciplines }, optionsCaption: '- Please select -'}, value: $root.comboModal.option2"></select>
  </label>
  <label for="combo-opt-1">Discipline 3
  		<select data-bind="groupedOptions: { groups: { coll: $root.listAllDisciplines }, optionsCaption: '- Please select -'}, value: $root.comboModal.option3"></select>
  </label>
  <label for="combo-desc">Description
 	 <textarea name="combo-desc" data-bind="value: $root.comboModal.description"></textarea>
  </label>
  <hr>
  <button class="button small" data-bind="click: $root.addComboDiscipline">Submit</button>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div id="elder-modal" class="reveal-modal" data-reveal aria-labelledby="elderModalTitle" aria-hidden="true" role="dialog">
  <h2 id="comboModalTitle">Elder Discipline</h2>
  <div><b>Discipline: </b><span data-bind="text: $root.elderModal.discipline() ? $root.elderModal.discipline().name : 'None'"></span></div>
  <label for="elder-name">Name
 	 <input type="text" data-bind="value: $root.elderModal.name" />
  </label>
  <label for="elder-desc">Description
 	 <textarea data-bind="value: $root.elderModal.description"></textarea>
  </label>
  <hr>
  <button class="button small" data-bind="click: $root.addElderPower">Submit</button>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div id="ritual-modal" class="reveal-modal" data-reveal aria-labelledby="ritualModalTitle" aria-hidden="true" role="dialog">
  <h2 id="ritualModalTitle">Custom Ritual</h2>
  <div>
  	<b>Type: </b>
  	<select data-bind="options: $root.validRitualOptions, value: $root.ritualModal.type">
  	</select>
  </div>
  <label for="ritual-name">Name
 	 <input type="text" data-bind="value: $root.ritualModal.name" />
  </label>
  <label for="ritual-desc">Description
 	 <textarea data-bind="value: $root.ritualModal.description"></textarea>
  </label>
  <hr>
  <button class="button small" data-bind="click: $root.addCustomRitual">Submit</button>
  <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div class="icon-bar vertical five-up builder-nav fixed hide-for-small-only">
	<a class="item" data-bind="click: function() { $root.activeTab('sect') }"><label>Sect</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('clan') }"><label>Clan</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('nature') }"><label>Nature</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('attributes') }"><label>Attributes</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('abilities') }"><label>Abilities</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('disciplines') }"><label>Disciplines</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('rituals') }"><label>Rituals</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('backgrounds') }"><label>Backgrounds</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('paths') }"><label>Path and <br>Virtues</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('derangements') }"><label>Derangements</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('merits') }"><label>Merits and<br>Flaws</label></a>
	<a class="item" data-bind="visible: $root.isStoryteller(), click: function() { $root.activeTab('storyteller') }"><label>Storyteller<br>Options</label></a>
	<a class="item" data-bind="click: function() { $root.activeTab('finish') }"><label>Finish and<br>Submit</label></a>
</div>
<ul class="side-nav builder-nav small-builder-nav fixed show-for-small-only">
	<li><a class="item" data-bind="click: function() { $root.activeTab('sect') }"><label>Sect</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('clan') }"><label>Clan</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('nature') }"><label>Nature</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('attributes') }"><label>Attributes</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('abilities') }"><label>Abilities</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('disciplines') }"><label>Disciplines</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('rituals') }"><label>Rituals</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('backgrounds') }"><label>Backgrounds</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('paths') }"><label>Path and <br>Virtues</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('derangements') }"><label>Derangements</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('merits') }"><label>Merits and<br>Flaws</label></a></li>
	<li><a class="item" data-bind="visible: $root.isStoryteller(), click: function() { $root.activeTab('storyteller') }"><label>Storyteller<br>Options</label></a></li>
	<li><a class="item" data-bind="click: function() { $root.activeTab('finish') }"><label>Finish and<br>Submit</label></a></li>
</div>
<div class="panel callout radius experience-popup">
	<!-- ko if: totalExperience() >= experienceSpent() -->
	You have <b data-bind="text: totalExperience() - experienceSpent()"></b> experience.
	<!-- /ko -->
	<!-- ko if: totalExperience() < experienceSpent() -->
	<span class="over-cap">You are <b data-bind="text: experienceSpent() - totalExperience()"></b> experience over capacity.</span>
	<!-- /ko -->
</div>
<div class="editor-row">
	<div class="small-12">
		<div id="sect-list" data-bind="visible: $root.activeTab() == 'sect'">
			<h3>Sects</h3>
			<div class="row">
				<div class="small-12 medium-3 medium-push-9 columns panel callout">
					<label>Selected</label> 
					<h5><span data-bind="text: characterSheet.sect.selected() ? characterSheet.sect.selected().name : 'None'"></span></h5>
					<label>Masquerading as</label> 
					<h5><span data-bind="text: characterSheet.sect.displaying() ? characterSheet.sect.displaying().name : 'None'"></span></h5>
				</div>
				<div class="small-12 medium-3 medium-pull-3 columns">
					<h5>Available Sects</h5>
					<select data-bind="options: rulebook.sects, optionsText: 'name', value: activeSect">
					</select>
				</div>
				<div class="small-12 small-push-12 medium-6 medium-pull-3 columns">
					<div data-bind="with: activeSect">
						<b data-bind="text: name"></b><br>
						<i data-bind="html: description"></i><br><br>
						<input type="button" class="button medium" data-bind="value: 'Select ' + name, click: $root.selectSect" />
						<input type="button" class="button small info" data-bind="value: 'Masquerade as ' + name, click: $root.masqueradeSect" />

					</div>
				</div>

			</div>
		</div>
		<div id="clan-list" data-bind="visible: $root.activeTab() == 'clan'">
			<h3>Clans</h3>
			<h3 class="subheader" data-bind="visible: characterSheet.sect.selected() === undefined">No sect selected.</h3>
			<div class="row" data-bind="with: characterSheet.sect.selected">
				<div class="small-12 medium-3 medium-push-9 columns panel callout">
					<label>Selected</label>
					<h5><span data-bind="text: $root.characterSheet.clan.selected() ? $root.characterSheet.clan.selected().name : 'None'"></span></h5>
					<label>Masquerading as</label> 
					<h5><span data-bind="text: $root.characterSheet.clan.displaying() ? $root.characterSheet.clan.displaying().name : 'None'"></span></h5>
					<hr>
					<div data-bind="visible: $root.hasClanOptions">
						<h5>Clan Options</h5>
						<div data-bind="if: $root.showClanOption('Brujah')">
							Select Influence:<br>
							<select data-bind="value: $root.clanOptionValues.brujah[0], enable: $root.approvedVersion() == 0"> 
								<option></option> 
								<option>Politics</option> 
								<option>University</option> 
								<option>Neighborhood</option> 
							</select>
						</div>
						<div data-bind="if: $root.showClanOption('Caitiff')">
							Select Disciplines:<br>
							<div data-bind="foreach: [0,1,2]">
								<select data-bind="value: $root.clanOptionValues.caitiff[$index()], enable: $root.approvedVersion() == 0"> 
									<option></option> 
									<option>Animalism</option> 
									<option>Auspex</option> 
									<option>Celerity</option> 
									<option>Dominate</option> 
									<option>Fortitude</option>
									<option>Obfuscate</option>
									<option>Potence</option> 
									<option>Presence</option> 
								</select><br>
							</div>
						</div>
						<div data-bind="if: $root.showClanOption('Tremere')">
							Select Discipline: 
							<select data-bind="value: $root.clanOptionValues.tremere[0], enable: $root.approvedVersion() == 0"> 
								<option>Thaumaturgy</option> 
								<option>Countermagic</option> 
							</select>
						</div>						
						<div data-bind="if: $root.showClanOption('Malkavian')">
							Select Derangement: <i class="icon-help-circled discipline-help" data-bind="click: $root.showMalkavianDerangementDescription"></i> 
							<select data-bind='value: $root.clanOptionValues.malkavian[0], enable: $root.approvedVersion() == 0'>
								<option></option>
								@foreach(RulebookDerangement::all() as $d)
								<option>{{$d->name}}</option>
								@endforeach
							</select><br>
							Select Discipline: 
							<select data-bind="value: $root.clanOptionValues.malkavian[1], enable: $root.approvedVersion() == 0"> 
								<option></option> 
								<option>Dementation</option> 
								<option>Dominate</option> 
							</select>
						</div>
						<div data-bind="if: $root.showClanOption('Toreador')">
							Select Abilities:
							<div data-bind="foreach: [0,1]">
								<select data-bind="value: $root.clanOptionValues.toreador[$index()], enable: $root.approvedVersion() == 0">
									<option></option>
									<option>Academics</option>
									<option>Crafts</option>
									<option>Perform</option>
									<option>Subterfuge</option>
								</select>
								<div data-bind="if: $root.clanOptionValues.toreador[$index()]() == 'Crafts' || $root.clanOptionValues.toreador[$index()]() == 'Perform'">
									<input type="text" data-bind="value: $root.clanOptionValues.toreador[$index() + 2], enable: $root.approvedVersion() == 0" placeholder="Describe..." />
								</div>
							</div>
						</div>
						<div data-bind="if: $root.showClanOption('Ventrue')">
							Enter Specific Prey:<br>
							<input type="text" data-bind="value: $root.clanOptionValues.ventrue[0], enable: $root.approvedVersion() == 0" /><br>
							Select Influence:
							<select data-bind="value: $root.clanOptionValues.ventrue[1], enable: $root.approvedVersion() == 0">
								<option></option>
								<option>Finance</option>
								<option>High Society</option>
								<option>Politics</option>
							</select>
						</div>
						<div data-bind="if: $root.showClanOption('Lasombra')">
							Select Influence:<br>
							<select data-bind="value: $root.clanOptionValues.lasombra[0], enable: $root.approvedVersion() == 0">
								<option></option>
								<option>Church</option>
								<option>Politics</option>
								<option>Underworld</option>
							</select>
						</div>
						<div data-bind="if: $root.showClanOption('Ravnos')">
							Enter Signature Crime:<br>
							<input type="text" data-bind="value: $root.clanOptionValues.ravnos[0], enable: $root.approvedVersion() == 0" /><br>
							Select Influence:
							<select data-bind="value: $root.clanOptionValues.ravnos[1], enable: $root.approvedVersion() == 0" >
								<option></option>
								<option>Neighborhood</option>
								<option>Transportation</option>
							</select>
						</div>
						<div data-bind="if: $root.showClanOption('Daughters of Cacophony')">
							Select Option:<br>
							<select data-bind="value: $root.clanOptionValues.daughters[0], enable: $root.approvedVersion() == 0">
								<option></option>
								<option>High Society (Influence)</option>
								<option>Performance (Ability)</option>
							</select>
						</div>	
						<div data-bind="if: $root.showClanOption('Followers of Set')">
							Select Influence:<br>
							<select data-bind="value: $root.clanOptionValues.setites[0], enable: $root.approvedVersion() == 0" >
								<option></option>
								<option>Politics</option>
								<option>Neighborhood</option>
								<option>Underworld</option>
							</select>
						</div>		
						<div data-bind="if: $root.showClanOption('Giovanni')">
							Select Influences:<br>
							<div data-bind="foreach: [0,1]">
								<select data-bind="value: $root.clanOptionValues.giovanni[$index()], enable: $root.approvedVersion() == 0">
									<option></option>
									<option>Finance</option>
									<option>Health</option>
								</select>
							</div>
						</div>			
					</div>
				</div>
				<div class="small-12 medium-3 medium-pull-3 columns">
					<h5>Available Clans</h5>
					<select data-bind="groupedOptions: { groups: { coll: $root.availableClans }, optionsCaption: '- Please select -', value: $root.activeClan }"></select>
				</div>
				<div class="small-12 small-push-12 medium-6 medium-pull-3 columns">
					<div class="callout panel" data-bind="visible: $root.approvedVersion() > 0 && !$root.isStoryteller()"><b>You cannot change your clan after character creation.</b></div>
					<div data-bind="with: $root.getClanById($root.activeClan() ? $root.activeClan().Value : -1)">
						<b data-bind="text: name"></b><br>
						<b>Advantages:</b>
						<div data-bind="html: advantages.replace('\n', '<br><br>')"></div><br>
						<b>Disadvantages:</b>
						<div data-bind="text: disadvantages"></div><br>
						<input type="button" class="button medium" data-bind="value: 'Select Clan ' + name, click: $root.selectClan, visible: ($root.approvedVersion() == 0 || $root.isStoryteller())" />
						<input type="button" class="button small info" data-bind="value: 'Masquerade as Clan ' + name, click: $root.masqueradeClan" />
					</div>
				</div>
				
			</div>
		</div>
		<div id="nature-list"  data-bind="visible: $root.activeTab() == 'nature'">
			<h3>Nature and Willpower</h3>
			<div class="row">
				<div class="panel info radius">
				  <p>Your nature is a one-word description of your character that simply lets the Storytellers know exactly what motivates your character.</p>
				</div>
				<div class="small-12 medium-3 medium-push-9 columns panel callout">
					<label>Selected</label>
					<h5><span data-bind="text: $root.characterSheet.nature() ? $root.characterSheet.nature().name : 'None'"></span></h5>
				</div>
				<div class="small-12 medium-3 medium-pull-3 columns">
				  	<h5>Available Natures</h5>
				  	<select data-bind="options: rulebook.natures, optionsText: 'name', value: activeNature">
				  	</select>
				</div>
				<div class="small-12 small-push-12 medium-6 medium-pull-3 columns">
					<div data-bind="with: $root.activeNature">
						<b data-bind="text: name"></b><br>
						<i data-bind="text: description"></i><br><br>
						<input type="button" class="button medium" data-bind="value: 'Select ' + name, click: $root.selectNature" /><br>
					</div>
				</div>

			</div>
			<hr>
			<div class="row">
				<h4>Willpower</h4>
					<div class="panel info radius">At character creation, you can purchase an additional dot AND a trait for three experience points.</div>
					<div class="willpower-option">
						<label>Willpower Traits </label> 
						<div class="button tiny success" data-bind="click: function() { tickWillpower(-1, 'traits') } ">-</div>
						<span data-bind="text: $root.characterSheet.willpower.traits()"></span>
						<div class="button tiny success" data-bind="visible: $root.isStoryteller(), click: function() { tickWillpower(1, 'traits') } ">+</div>
					</div>
					<div class="willpower-option">
						<label>Willpower Dots </label> 
						<div class="button tiny success" data-bind="click: function() { tickWillpower(-1, 'dots') } ">-</div>
						<span data-bind="text: $root.characterSheet.willpower.dots()"></span>
						<div class="button tiny success" data-bind="click: function() { tickWillpower(1, 'dots') } ">+</div>
					</div>
			</div>
		</div>
		<div id="attribute-list" data-bind="visible: $root.activeTab() == 'attributes'">
			<h3>Attributes</h3>
			<div class="row">
				<div class="small-12 columns">
					<div class="panel info radius" data-bind="visible: $root.approvedVersion() == 0">
					  <p>	<span class="hide-for-small">A character's attributes are split into three categories: physical, mental, and social traits. 
							At character creation, each category must be specfied as of primary, secondary, or tertiary importance.</span>
							   	You get 15 points for free.
							<span class="hide-for-small">We recommend seven free traits in your primary category, five in your secondary, and three in your tertiary.</span>
							   	Additional traits can be purchased for one experience point each.</p>
							<b>You have <span data-bind="text: Math.max(0, 15 - $root.attributePointsSpent())"></span> free points remaining.</b>
					</div>
					<div class="small-12">
						<div class="row hide-for-small">
							<div class="small-11 small-offset-1 columns" data-bind="foreach: $root.getTraitLoop()">
								<div class="trait-numerical-label" data-bind="text: $data + 1"></div>
							</div>
						</div>
						<div class="row">
							<div class="small-12 medium-1 columns">
								<b class="trait-label">Physicals</b>
							</div>
							<div class="small-12 medium-11 columns" data-bind="foreach: $root.getTraitLoop()">
								<div class="trait-box physical" data-bind="click: function() { $root.setAttribute(0, $data) }, css: {'filled': $data < $root.characterSheet.attributes()[0]}, text: ($data == $root.characterSheet.attributes()[0] - 1) ? $root.characterSheet.attributes()[0] : ''"></div>
							</div>
						</div>
						<div class="row">
							<div class="small-12 medium-1 columns">
								<b class="trait-label">Mentals</b>
							</div>
							<div class="small-12 medium-11 columns" data-bind="foreach: $root.getTraitLoop()">
								<div class="trait-box mental" data-bind="click: function() { $root.setAttribute(1, $data) }, css: {'filled': $data < $root.characterSheet.attributes()[1]}, text: ($data == $root.characterSheet.attributes()[1] - 1) ? $root.characterSheet.attributes()[1] : ''"></div>
							</div>
						</div>
						<div class="row ">
							<div class="small-12 medium-1 columns">
								<b class="trait-label">Socials</b>
							</div>
							<div class="small-12 medium-11 columns" data-bind="foreach: $root.getTraitLoop()">
								<div class="trait-box social" data-bind="click: function() { $root.setAttribute(2, $data) }, css: {'filled': $data < $root.characterSheet.attributes()[2]}, text: ($data == $root.characterSheet.attributes()[2] - 1) ? $root.characterSheet.attributes()[2] : ''"></div>
							</div>
						</div>
					</div>
				</div>							
			</div>
		</div>
		<div id="ability-list" data-bind="visible: $root.activeTab() == 'abilities'">
			<a name="abilities"></a>
			<h3>Abilities</h3>

			<div class="row">
				<div class="small-12 panel info radius" data-bind="visible: $root.approvedVersion() == 0">
					<p>	At character creation, you get five dots of Abilities for free. 
						<b >You have <span data-bind="text: Math.max(0, 5 - $root.abilityPointsSpent())"></span> free points remaining.</b>
					</p>
				</div>
				<div class="small-12 medium-8 medium-push-4 columns" data-bind="foreach: characterSheet.abilities">
					<div class="panel callout ability-list-selected">
						<div class="ability-list-icon plus" data-bind="click: function() { $root.tickAbility($data, 1) }">+</div>
						<b class="ability-list-selected-name" data-bind="text: name + ' (' + count + ')'"></b>
						<div class="ability-list-icon minus" data-bind="click: function() { $root.tickAbility($data, -1) }">-</div>
						<div class="specialization-block">
							<div data-bind="if: $data.specialization">
								<div class="specialization-data" data-bind="text: 'Specialization: ' + specialization"></div>
							</div>
							<div data-bind="if: !$data.specialization">
								<button class="button tiny specialization-button" data-bind="click: $root.openSpecializationModal">Add Specialization</button>
							</div>
						</div>
					</div>
				</div>
				<div class="small-12 medium-4 medium-pull-8 columns">
					<h5>Available Abilities</h5>
					<select data-bind="groupedOptions: { groups: { coll: $root.availableAbilities }, optionsCaption: '- Please select -', value: $root.activeAbility }"></select><br><br>
					<!-- ko if: $root.activeAbility -->
					<input type="button" class="button small" data-bind="click: function() { addAbility($root.activeAbility().Value) }, 
																		 value: 'Add ' + getAbilityById($root.activeAbility().Value).name" />
					<!-- /ko -->
					<br><br>
					<h5>Custom Ability</h5>
					<p>If you'd like an ability that isn't covered above, enter it here. Be prepared to describe to the Storytellers how and when this ability applies.</p>
					<input type="text" data-bind="value: $root.customAbility" placeholder="Ability Name..." /> 
					<button class="button small" data-bind="click: function() { $root.addCustomAbility($root.customAbility()) }">Add Custom Ability</button>

				</div>
			</div>
		</div>
		
		<div id="discipline-list"  data-bind="visible: $root.activeTab() == 'disciplines'">
			<h3>Disciplines</h3>
			<h3 class="subheader" data-bind="visible: characterSheet.clan.selected() === undefined">No clan selected.</h3>
			<div class="row" data-bind="visible: characterSheet.clan.selected() !== undefined">
				<div class="panel info radius">
					<p>At character creation, you get three in-clan basic disciplines for free. You can buy additional basic disciplines for three experience points or intermediates for six. Some out-of-clan disciplines cost an additional point, and you must specify who taught you these disciplines.</p>
				</div>
				<div class="small-12 medium-3 medium-push-9 columns panel callout">
					<div data-bind="foreach: characterSheet.disciplines">
						<!-- ko if: path > 0-->
						<h5 data-bind="text: $root.getDisciplinePathById(id, path).name"></h5>
						<!-- /ko -->
						<!-- ko if: path == 0 || path == null -->
						<h5 data-bind="text: name"></h5>
						<!-- /ko -->
						<div data-bind="foreach: $root.getDisciplineList(id, path > 0 ? path : null)">
							<!-- ko if: $index() ==  $root.getDisciplineList($parent.id, $parent.path > 0 ? $parent.path : null).length - 1 -->
							<div class="remove-button" data-bind="click: function() { $root.removeDisciplineRank($parent.id, $parent.path) } ">&times;</div>
							<!-- /ko -->
							<!-- ko if: $index() !=  $root.getDisciplineList($parent.id, $parent.path > 0 ? $parent.path : null).length - 1 -->
							<div class="discipline-selected-spacer"></div>
							<!-- /ko -->
							<div class="discipline-selected-item">
								<div class="discipline-selected-name" data-bind="text: name"></div>
								<i class="icon-help-circled discipline-help" data-bind="click: function() { $root.showModal(name, description); }"></i>
							</div><br>
						</div>
					</div>
					<!-- ko if: characterSheet.comboDisciplines().length > 0 -->
						<h5>Combinations</h5>
						<div data-bind="foreach: characterSheet.comboDisciplines">
							<div class="remove-button" data-bind="click: function() { $root.removeComboDiscipline(id) } ">&times;</div>
							<div class="discipline-selected-item">
								<div class="discipline-selected-name" data-bind="text: name"></div>
								<i class="icon-help-circled discipline-help" data-bind="click: function() { $root.showModal(name, description); }"></i>
							</div>
						</div>
					<!-- /ko -->
				</div>				
				<div class="small-12 medium-3 medium-pull-3 columns">
					<h5>Available Disciplines</h5>
					<select data-bind="groupedOptions: { groups: { coll: $root.availableDisciplines }, optionsCaption: '- Please select -', value: $root.activeDiscipline }"></select>
					<!-- ko if: $root.hasPaths() -->
					<br><br><h5>Paths of <span data-bind="text: $root.activeDiscipline().Text"></span></h5>
					<select data-bind="groupedOptions: { groups: { coll: $root.availablePathDisciplines }, optionsCaption: '- Please select -', value: $root.activeDisciplinePath }"></select>
					<!-- /ko -->
					<button class="button small" data-bind="click: $root.showComboModal">Create Combo Discipline</button>

				</div>
				<div class="small-12 small-push-12 medium-6 medium-pull-3 columns">
					<!-- ko if: !$root.hasPaths() -->
					<div data-bind="with: getDisciplineById($root.activeDiscipline() ? $root.activeDiscipline().Value : -1)">
						<b data-bind="text: name"></b><br>
						Retests with <i><span data-bind="text: retest"></span></i><br>
						You have <span data-bind="text: $root.getCharacterDiscipline(id) ? $root.getCharacterDisciplineRanks(id) : 'no'"></span> ranks of <span data-bind="text: name"></span>.<br><br>
						<div data-bind="with: $root.getNextDisciplineRank(id)">
							The next rank is <b data-bind="html: name"></b>.<br>
							<i data-bind="html: description"></i><br>
							<input type="button" class="button small" data-bind="value: 'Purchase ' + name, click: function() { $root.purchaseDiscipline($parent.id) }" /><br>
						</div>
						<div data-bind="visible: $root.getNextDisciplineRank(id) === undefined">
							You have reached the maximum rank of <span data-bind="text: name"></span>.
							<!-- ko if: $root.getDisciplineList(id, null).length != 6 -->
								<button class="button small" data-bind="click: $root.showElderModal">Create Elder Power</button>
							<!-- /ko -->
						</div>
					</div>
					<!-- /ko -->
					<!-- ko if: $root.hasPaths() && $root.activeDiscipline() && $root.activeDisciplinePath() -->
					<div data-bind="with: $root.getDisciplinePathById($root.activeDiscipline().Value, $root.activeDisciplinePath().Value)">
						<b data-bind="text: $root.getDisciplineById($root.activeDiscipline().Value).name"></b>: <i data-bind="text: name"></i><br>
						Retests with <i><span data-bind="text: $root.getDisciplineById($root.activeDiscipline().Value).retest"></span></i><br><br>
						<div data-bind="with: $root.getNextDisciplineRank($root.activeDiscipline().Value, id)">
							The next rank is <b data-bind="html: name"></b>.<br>
							<i data-bind="html: description"></i><br>
							<input type="button" class="button small" data-bind="value: 'Purchase ' + name, click: function() { $root.purchaseDiscipline($root.activeDiscipline().Value, $root.activeDisciplinePath().Value) }" /><br>
						</div>
						<div data-bind="visible: $root.getNextDisciplineRank($root.activeDiscipline().Value, id) === undefined">
							You have reached the maximum rank of <span data-bind="text: name"></span>.
						</div>
					</div>
					<!-- /ko -->
				</div>
			</div>
		</div>
		
		<div id="ritual-list"  data-bind="visible: $root.activeTab() == 'rituals'">
			<div className="ritual-list">
				<a name="rituals"></a>
				<h3 data-magellan-destination="rituals">Rituals</h3>
					<div data-bind="visible: !$root.canUseRituals()">
						<div class="panel callout radius">
							<p>Only those characters who posses either Thaumaturgy or Necromancy have access to rituals.</p>
						</div>
					</div>
					<div class="row" data-bind="visible: $root.canUseRituals()">
						<div class="small-4 columns">
							<b>Available Rituals</b>
							<select data-bind="groupedOptions: { groups: { coll: $root.availableRituals }, optionsCaption: '- Please select -', value: $root.activeRitual }"></select>
							<button class="button small success" data-bind="click: $root.showRitualModal">Add Custom Ritual</button>
						</div>
						<div class="small-4 columns">
							<div data-bind="with: activeRitualData">
								<b data-bind="html: name"></b><br>
								<i data-bind="html: description"></i><br>
								<input type="button" class="button small" data-bind="click: function() { $root.addRitual($data.id) }, 																						 value: 'Add ' + name" />
							</div>
						</div>
						<div class="small-4 columns panel callout">
							<h5>Selected Rituals</h5>
							<div data-bind="foreach: $root.characterSheet.rituals">
								<div class="remove-button" data-bind="click: function() { $root.removeRitual($data) } ">&times;</div>
								<div class="discipline-selected-item" data-bind="text: $root.getRitualById($data).name"></div><br>
							</div>
						</div>
					</div>
				<hr>
			</div>
		</div>
		
		<div id="background-list" data-bind="visible: $root.activeTab() == 'backgrounds'">
			<h3>Backgrounds</h3>
			<div class="row">
				<div class="panel info radius" data-bind="visible: $root.approvedVersion() == 0">
					<p>	At character creation, you get five dots of Backgrounds for free. 
						Each dot in a Background costs 1 Experience except for Generation. 
						<span class="hide-for-small">The first two dots of Generation cost 1 Experience, the third costs 2 Experience, the fourth costs 4 Experience, 
							and the fifth costs 8 Experience, for a total of 16 Experience to reach 5 Dots of Generation.</span>
						<b >You have <span data-bind="text: Math.max(0, 5 - $root.backgroundPointsSpent())"></span> free points remaining.</b>
				</p>
				</div>
				<div class="small-12 medium-8 medium-push-4 columns" data-bind="foreach: characterSheet.backgrounds">
					<div class="panel callout ability-list-selected">
						<div class="ability-list-icon plus" data-bind="click: function() { $root.tickBackground($data, 1) }">+</div>
						<b class="ability-list-selected-name" data-bind="text: $root.backgroundName($data)"></b>
						<div class="ability-list-icon minus" data-bind="click: function() { $root.tickBackground($data, -1) }">-</div>
					</div>
				</div>				
				<div class="small-12 medium-4 medium-pull-8 columns">
					<b>Available Backgrounds</b>
					<select data-bind="groupedOptions: { groups: { coll: $root.availableBackgrounds }, optionsCaption: '- Please select -', value: $root.activeBackground }">
			
					</select><br><br>
					<!-- ko if: $root.activeBackground -->
					<input type="button" class="button small" data-bind="click: function() { addBackground($root.activeBackground().Value) }, 
																		 value: 'Add ' + getBackgroundById($root.activeBackground().Value).name" />
					<!-- /ko -->

				</div>

			</div>
		</div>
		
		<div id="path-list"  data-bind="visible: $root.activeTab() == 'paths'">
			<h3>Paths</h3>
			<div class="row">
				<div class="panel info radius">
					<p>A character's path defines its sins. We strongly recommend that the majority of players stay on the Path of Humanity, but you can change it with ST approval.</p>
				</div>
				<div class="small-12 medium-4 columns">
					<h5>Available Paths</h5>
					<select data-bind="value: $root.activePath, options: rulebook.paths, optionsText: 'name'">
					</select>
					<div class="panel callout">
						<h4 class="subheader">Virtues</h4>
						<div data-bind="with: characterSheet.path">
							<b data-bind="text: 'You are on the ' + name"></b><br>
							<p>At character creation, you have seven points to spread among your three virtues and Morality will be the average of the first two. Thereafter, you can purchase Morality traits for two experience and Virtue traits for three.</p>
							<div data-bind="visible: $root.approvedVersion() == 0">
								<b>You have <span data-bind="text: Math.max(0, 7 - $root.virtuePointsSpent())"></span> free points remaining.</b>
							</div>
							<div class="panel virtue-item">
								<div class="ability-list-icon plus" data-bind="click: function() { $root.onVirtuePlus(0) }">+</div>
								<b class="ability-list-selected-name" data-bind="text: stats[0] + ' (' + $root.characterSheet.virtues()[0] + ')'"></b>
								<div class="ability-list-icon minus" data-bind="click: function() { $root.onVirtueMinus(0) }">-</div>
							</div>
							<div class="panel virtue-item">
								<div class="ability-list-icon plus" data-bind="click: function() { $root.onVirtuePlus(1) }">+</div>
								<b class="ability-list-selected-name" data-bind="text: stats[1] + ' (' + $root.characterSheet.virtues()[1] + ')'"></b>
								<div class="ability-list-icon minus" data-bind="click: function() { $root.onVirtueMinus(1) }">-</div>
							</div>
							<div class="panel virtue-item">
								<div class="ability-list-icon plus" data-bind="click: function() { $root.onVirtuePlus(2) }">+</div>
								<b class="ability-list-selected-name" data-bind="text: stats[2] + ' (' + $root.characterSheet.virtues()[2] + ')'"></b>
								<div class="ability-list-icon minus" data-bind="click: function() { $root.onVirtueMinus(2) }">-</div>
							</div>
							<div class="panel virtue-item">
								<div class="ability-list-icon plus" data-bind="click: function() { $root.onVirtuePlus(3) }">+</div>
								<b class="ability-list-selected-name" data-bind="text: stats[3] + ' (' + $root.characterSheet.virtues()[3] + ')'"></b>
								<div class="ability-list-icon minus" data-bind="click: function() { $root.onVirtueMinus(3) }">-</div>
							</div>
							<p class="small-desc">If you lost a virtue point, and would like to immediately repurchase it, tell the Storytellers.</p>
						</div>
						<!-- ko if: characterSheet.path() === undefined -->
							<p>No path selected.</p>
						<!-- /ko -->
					</div>
					
				</div>
				<div class="small-12 medium-8 columns">
					<div class="path-info" data-bind="with: $root.activePath">
						<h4 data-bind="text: name"></h4>
						<input type="button" class="button small" data-bind="click: $root.selectPath, value: 'Select ' + name" />
						<div class="path-description" data-bind="html: description"></div>
						<p><h5>Sins</h5>
						<div data-bind="foreach: sins">
							<div data-bind="text: (5 - $index()) + '. ' + $data"></div>
						</div></p>
					</div>
				</div>
			
			</div>		
		</div>
		<div id="derangement-list" data-bind="visible: $root.activeTab() == 'derangements'">
			<h3 >Derangements</h3>
			<div class="row">
				<div class="panel info radius">
					<p>At character generation, each Derangement is equivalent to a single 2-Point Flaw.</p>
				</div>
				<div class="small-12 medium-4 medium-push-8 column panel callout">
					<h5>Selected Derangements</h5>
					<div data-bind="foreach: $root.characterSheet.derangements">
						<div class="remove-button" data-bind="click: function() { $root.removeDerangement($data) } ">&times;</div>
						<div class="discipline-selected-item" data-bind="text: $root.getDerangementById(data.id).name"></div><br>
						<div class="merit-description" data-bind=" visible: $data.description, text: $data.description"></div>
					</div>
				</div>
				<div class="small-12 medium-4 medium-pull-4 column">
					<h5>Available Derangements</h5>
					<select data-bind="options: availableDerangements, value: activeDerangement, optionsText: 'name', optionsValue: 'id'">
					</select>										
				</div>
				<div class="small-12 medium-4 medium-pull-4 column">
					<div data-bind="with: activeDerangementData">
						<b data-bind="text: name"></b><br>
						<i data-bind="text: description"></i><br>
						<input type="button" class="button small" data-bind="click: function() { $root.addDerangement($root.activeDerangement()) }, 
																		 value: 'Add ' + name" />
					</div>
				</div>

			</div>

		</div>
		<div id="merit-and-flaw-list" data-bind="visible: $root.activeTab() == 'merits'">
			<h3>Merits and Flaws</h3>
			<div class="row">
				<div class="panel info radius">
					<p>At character creation, you can pick up to seven points worth of Merits and seven points of Flaws. Thereafter, you can purchase Merits or buy-off flaws for twice the base cost of the merit (or flaw) and ST approval.</p>
				</div>
				<div class="small-12 medium-4 medium-push-8 column panel callout">
					<h5>Selected Merits</h5>
					<div data-bind="foreach: $root.characterSheet.merits">
						<div class="remove-button" data-bind="click: function() { $root.removeMerit($data) } ">&times;</div>
						<div class="discipline-selected-item" data-bind="text: '(' + data.cost + ') ' + data.name"></div>
						<div class="merit-description" data-bind=" visible: $data.description, text: $data.description"></div>
						<br>
					</div>
				</div>
				<div class="small-12 medium-4 medium-pull-4 column">
					<h5>Available Merits</h5>
						<select data-bind="groupedOptions: { groups: { coll: $root.availableMerits }, optionsCaption: '- Please select -', value: $root.activeMerit }"></select>			
				</div>
				<div class="small-12 medium-4 medium-pull-4 column">
					<div data-bind="with: activeMerit">
						<b data-bind="text: Name"></b><br>
						<i data-bind="html: $root.getMeritById(Value).description"></i><br>
						<input type="button" class="button small" data-bind="click: function() { $root.addMerit($root.activeMerit()) }, 
																		 value: 'Add ' + Name" />						
					</div>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="small-12 medium-4 medium-push-8 column panel callout">
					<h5>Selected Flaws</h5>
					<div data-bind="foreach: $root.characterSheet.flaws">
						<div class="remove-button" data-bind="click: function() { $root.removeFlaw($data) } ">&times;</div>
						<div class="discipline-selected-item" data-bind="text: '(' + data.cost + ') ' + data.name"></div><br>
						<div class="merit-description" data-bind=" visible: $data.description, text: $data.description"></div>
					</div>
				</div>				
				<div class="small-12 medium-4 medium-pull-4 column">
					<h5>Available Flaws</h5>
					<select data-bind="groupedOptions: { groups: { coll: $root.availableFlaws }, optionsCaption: '- Please select -', value: $root.activeFlaw }"></select>	
				</div>
				<div class="small-12 medium-4 medium-pull-4 column">
					<div data-bind="with: activeFlaw">
						<b data-bind="text: Text"></b><br>
						<i data-bind="html: $root.getFlawById(Value).description"></i><br>
						<input type="button" class="button small" data-bind="click: function() { $root.addFlaw($root.activeFlaw()) }, 
																		 value: 'Add ' + Text" />						
					</div>
				</div>

			</div>
		</div>
		@if(Auth::user()->isStoryteller())
		<div id="storyteller-list" data-bind="visible: $root.activeTab() == 'storyteller'">
			<h3>Storyteller Options</h3>
			<p>Storyteller Options are not versioned.</p>
			<div class="row left">
				<div class="small-12 columns">
					@if(isset($character_id))
					  <? $character = Character::find($character_id); ?>
					  <form method="post" action="/generator/{{$character_id}}/options/save">
					  @foreach(RulebookStorytellerOption::all() as $definition)
					  	<label for="storyteller-option-{{$definition->id}}">{{$definition->name}}
						  	{{$definition->createForm($character)}}
						  	<p class="setting-description">{{$definition->description}}</p>
					  	</label>
					  @endforeach
					  <hr>
					  <input type="submit" class="button success" value="Save Options" />
					  </form>
					@else
						<p>Storyteller Options can only be applied to saved characters.</p>
					@endif
				</div>
			</div>
		</div>	
		@endif
		<div id="finish-list"  data-bind="visible: $root.activeTab() == 'finish'">
			<h3>Finish and Submit</h3>
			<div class="row">
				<div class="small-12 columns">
					<div class="row">
						<div class="small-4 medium-2 columns">
				          <label for="char-name" class="right inline">Character Name</label>
				        </div>
				        <div class="small-8 medium-10 columns">
				          <input type="text" id="char-name" data-bind="value: characterSheet.name">
				        </div>
					</div>						
				</div>
			</div>
			<div class="row">
				<div class="small-12 columns">
					<div class="row">
						<div class="small-4 medium-2 columns">
				          <label for="commit-message" class="right inline">Change Description</label>
				        </div>
				        <div class="small-8 medium-10 columns">
							<textarea id="commit-message" placeholder="Write any additional information you think the STs should know." data-bind="value: $root.versionComment"></textarea>	        
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="small-12 medium-6 columns">
					<div class="panel callout save-exit-prompt">
						<b>Save and Exit</b>
						<p class="save-instructions">If you want to save your work and exit the builder, click the button below. This will not send the changes you've made to the storytellers, 
							but they will appear on your printed sheet,so please finalize your character as quickly as possible. Only use this button if you want to 
							return to this system later to continue working on your character.</p>
						<a class="button small" data-bind="click: function() { save(false); }">Save</a>
					</div>
				</div>
				<div class="small-12 medium-6 columns">
					<div class="panel callout save-exit-prompt">
						<b>Finalize Changes</b>
						<p class="save-instructions">	To send your changes to the storytellers for review, click this button. You will not be able to make any more changes to your character 
														until after the STs have a chance to review and discuss your changes. You'll get a PM on the forum with more information when they do so.</p>
						<a class="button small" data-bind="click: function() { save(true); }">Send for Review</a>

					</div>
				</div>	
			</div>
			@if(isset($character_id))
			<div class="row">
				<div class="small-12 columns">
					<div class="panel callout">
						<b>Reset Changes</b>
						<p>	If you don't like the changes you've made, you can roll back your character to the most recently approved version. 
							That said, if you haven't saved any changes since your character was last approved, you can just refresh the page.</p>
						<form action="/generator/{{$character_id}}/reset" method="post">
							<input type="hidden" name="characterId" value="{{$character_id}}" />
							<input type="submit" class="button small" value="Reset Changes" />
						</form>
					</div>
				</div>
  			</div>
  			@endif
		</div>
	</div>
</div>

@stop
@section('script')
<script src="/js/foundation/foundation.tooltip.js"></script>
<script src="/js/foundation/foundation.magellan.js"></script>
<script src="/js/foundation/foundation.accordion.js"></script>
<script src="/js/knockout-groupedOptions.js"></script>
<script src="http://underscorejs.org/underscore.js"></script>
<script type="text/javascript">
	var preloaded = {
		userId: {{Auth::user()->id}},
		isStoryteller: {{Auth::user()->isStoryteller() ? "true" : "false"}},
		characterId: {{isset($character_id) ? $character_id : -1}}
	}
	<? if(isset($character_id)) {
		$character = Character::find($character_id);
		if($character->in_review) {
			echo 'document.location = "/dashboard";';
		} ?>
		preloaded.editingVersion = {{$character->approved_version}};
		preloaded.newVersion = {{$character->activeVersion() == $character->latestVersion()->version ? "true" : "false"}};
		preloaded.experienceSpent = {{$character->getExperienceCost($character->latestVersion()->version)}};
		preloaded.experienceTotal =	10 + {{$character->experience}};
		preloaded.cData = {{json_encode($character->getVersion($character->latestVersion()->version, false))}};

	<? } ?>
</script>
<script src="/js/generator.js"></script>
@stop