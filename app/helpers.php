<?php
if (!class_exists("Helpers")) {
class Helpers {
	static function timestamp($timestamp) {
		$user = Auth::user();
		if($user) {
			$value = $user->getSettingValue('Timestamp Style');
			if($value && $value == "exact") return $timestamp->setTimezone('America/Chicago')->format('F jS g:i A');
		}
		return $timestamp->diffForHumans();
	}
	
	static function nl_join(array $list, $conjunction = 'and') {
		$last = array_pop($list);
		if ($list) {
			return implode(', ', $list) . ' ' . $conjunction . ' ' . $last;
			}
	  return $last;
	}
	
	static function extractByKey($key, $collection) {
		$list = []; 
		foreach($collection as $u) $list[] = $u->$key;
		return $list;
	}
	
	public static	$rulebook_items = [
		"sects" => "RulebookSect",
		"clans" => "RulebookClan",
		"disciplines" => "RulebookDiscipline",
		"discipline_powers" => "RulebookDisciplineRank",
		"discipline_paths" => "RulebookDisciplinePath",
		"abilities" => "RulebookAbility",
		"backgrounds" => "RulebookBackground",
		"merits" => "RulebookMerit",
		"flaws" => "RulebookFlaw",
		"derangements" => "RulebookDerangement",
		"rituals" => "RulebookRitual",
		"lores" => "RulebookLore",
		"natures" => "RulebookNature",
		"paths" => "RulebookPath",
		"questionnaire" => "RulebookQuestionnaire",
		"storyteller_options" => "RulebookStorytellerOption",
		"elder_powers" => "RulebookElderPower",
		"combo_disciplines" => "RulebookComboDiscipline"
	];
}
}
