<?php

class CharacterProperty extends Eloquent {
	
	public function version() {
		return $this->hasOne('CharacterVersion', 'id', 'version_id');
	}

	public function scopeCharacter($query, $character_id) {
		return $query->where('character_id', $character_id);
	}
	public function scopeVersion($query, $version, $action = "=") {
		return $query->with('version')->whereHas('version', function($q) use ($version, $action) { $q->where('characters_versions.version', $action, $version); });
	}

}