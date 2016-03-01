<?php
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class ForumMail extends Eloquent {
	
    use SoftDeletingTrait;

	protected $table = 'forums_mail';
	protected $fillable = array('from_id', 'to_id', 'title', 'body');
	protected $append = ['read'];
    protected $dates = ['deleted_at'];

	public function from() {
		if($this->from_id == null) return "System Mailer";
		return User::find($this->from_id)->username;
	}

	public function to() {
		return $this->hasOne("User", 'id', 'to_id');
	}
	
	public function read() {
		return $this->received_at != null;
	}

	public function getReadAttribute() {
		return $this->read();
	}

}
