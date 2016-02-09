<?php

class UserSettingDefinition extends Eloquent {
	protected $table = 'user_settings_definitions';
	
	public function getSelectOptions($user) {
		$options = [];
		switch($this->name) {
			case "Theme":
				$options = ["default" => "Default", "edgy" => "Darkriven Talonfire"];
				break;
		}
		$out = "";
		foreach($options as $k => $v) {
			$selected = $user->getSettingValue($this->name) == $k ? "selected" : "";
			$out .= "<option value='$k' $selected>$v</option>";
		}
		return $out;
	}
	public function createForm($user) {
		$field = $this->type;
		$value = $user->getSettingValue($this->name);
		if($field == "textarea") return "<textarea name='user-settings-$this->id'>$value</textarea>";
		if($field == "checkbox") {
			return "<div class='switch user-settings-switch'> <input id='user-settings-$this->id' name='user-settings-$this->id' type='checkbox' ".($value ? 'checked' : '').">".
					"<label for='user-settings-$this->id'></label></div>";
		}
		if($field == "select") {
			return "<select name='user-settings-$this->id'>".$this->getSelectOptions($user)."</select>";
		}
		return "<input type='$field' name='user-settings-$this->id' value='$value'/>";
	}
}

?>