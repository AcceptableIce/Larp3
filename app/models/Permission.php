<?
class Permission extends Eloquent {
	protected $table = 'user_permissions';
	public function definition() {
		return $this->hasOne('PermissionDefinition', 'id', 'permission_id');
	}
}
