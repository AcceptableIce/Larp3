<?php

class RulebookStorytellerOption extends Eloquent {
	protected $table = 'rulebook_storyteller_options';
	protected $fillable = ['name', 'description', 'type', 'options', 'position'];
	public function createForm($character) {
		$field = $this->type;
		$value = $character->getOptionValue($this->name);
		if($field == "textarea") return "<textarea name='storyteller-option-$this->id'>$value</textarea>";
		if($field == "checkbox") {
			return 	"<div class='switch st-option-switch'>".
							"<input id='storyteller-option-$this->id' name='storyteller-option-$this->id' type='checkbox' ".($value ? 'checked' : '').">".
							"<label for='storyteller-option-$this->id'></label></div>";
		}
		return "<input type='$field' name='storyteller-option-$this->id' value='$value'/>";
	}
}

?>