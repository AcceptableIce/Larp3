<?
class HandbookPagePermission extends Eloquent {
	protected $table = 'handbook_pages_permissions';

	public function background() {
		return $this->hasOne('RulebookBackground', 'id', 'background_id');
	}

	public function sect() {
		return $this->hasOne('RulebookSect', 'id', 'sect_id');
	}

	public function clan() {
		return $this->hasOne('RulebookClan', 'id', 'clan_id');
	}

	public function userPermission() {
		return $this->hasOne('PermissionDefinition', 'id', 'permission_id');
	}

	public function hasRestrictions() {
		return $this->background_id != null || $this->sect_id != null | $this->clan_id != null | $this->permission_id != null;
	}
}