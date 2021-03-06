<?php

class RulebookSect extends Eloquent {
	protected $table = 'rulebook_sects';
	protected $fillable = ['name', 'description', 'common_clans', 'uncommon_clans'];
	
	public function commonClans() {
		$clan_ids = explode(",", $this->common_clans);
		$clans = [];
		foreach($clan_ids as $id) {
			$clans[] = RulebookClan::find($id);
		}
		return $clans;
	}
	
	public function uncommonClans() {
		$clan_ids = explode(",", $this->uncommon_clans);
		$clans = [];
		foreach($clan_ids as $id) {
			$clans[] = RulebookClan::find($id);
		}
		return $clans;
	}
}

?>