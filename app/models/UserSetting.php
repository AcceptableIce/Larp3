<?php

class UserSetting extends Eloquent {
	protected $table = 'user_settings';
	protected $fillable = ['user_id', 'definition_id'];
	
	public function definition() {
		return $this->hasOne('UserSettingDefinition', 'id', 'definition_id');
	}
}

?>