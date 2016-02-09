<?php
class MailController extends BaseController {
	public function markRead() {
		$mail = ForumMail::find(Input::get('id')); 
		if($mail->to_id == Auth::user()->id) {
			$mail->received_at = new DateTime;
			$mail->save();
			return Response::json(['success' => true, 'message' => 'Successfully marked as read.']);
		} else {
			return Response::json(['success' => false, 'message' => 'Mail does not belong to authenticated user.']);
		}
	}

	public function markAllRead() {
		foreach(ForumMail::where('to_id', Auth::user()->id)->whereNull('received_at')->get() as $mail) {
			$mail->received_at = new DateTime;
			$mail->save();
		}
		return Redirect::to('/dashboard/mail');
	}

	public function sendMessage() {
		$to_name = Input::get('to');
		$title = Input::get('subject');
		$body = Input::get('body');
		$to_user = User::where('username', 'LIKE', $to_name)->first();
		if(isset($to_user)) {
			$to_user->sendMessage(Auth::user()->id, $title, $body);
			return Response::json(['success' => true, 'message' => 'Message sent!']);
		} else {
			return Response::json(['success' => false, 'message' => 'Could not find user.']);			
		}
	}

	public function deleteMessage() {
		$mail = ForumMail::find(Input::get('id'));
		if($mail && $mail->to_id == Auth::user()->id) {
			$mail->delete();
			return Response::json(['success' => true, 'message' => 'Message deleted.']);			
		} else {
			return Response::json(['success' => false, 'message' => 'Could not delete message.']);
		}
	}
	
	public function lookupUser($name) {
		return Response::json(['found' => User::where('username', 'LIKE', $name)->count() > 0]);
	}


}