<?php

class FileUpload extends Eloquent {
	protected $table = "file_uploads";

	public function readPermission() {
		return $this->hasOne('PermissionDefinition', 'id', 'read_permission');
	}

	public function createdBy() {
		return $this->hasOne('User', 'id', 'created_by');
	}
}