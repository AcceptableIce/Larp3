<?php

class ApplicationSetting extends Eloquent {
	protected $table = 'application_settings';
	
	public function createForm() {
		$field = $this->type;
		$value = $this->value;
		if($field == "textarea") return "<textarea name='application-setting-$this->id'>$value</textarea>";
		if($field == "checkbox") {
			return "<div class='switch app-setting-option-switch'> <input id='application-setting-$this->id' name='application-setting-$this->id' type='checkbox' ".($value ? 'checked' : '').">".
					"<label for='application-setting-$this->id'></label></div>";
		}
		return "<input type='$field' name='application-setting-$this->id' value='$value'/>";
	}
	
	public static function get($key) {
		return ApplicationSetting::where('name', $key)->first()->value;
	}
}

?>