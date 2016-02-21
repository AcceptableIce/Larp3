@extends('dashboard')
@section('title', 'Storyteller Mailbox')
@section('dashboard-style')
.dash-main {
	padding: 0 0;
	padding-left: 8px;
	min-width: 840px;
	top: 50px;
}

.toast {
	z-index: 99999999999
}

#message-modal {
	position: fixed;
	left: 50%;
	top: 50%;
	transform: translate(-50%, -50%);
	z-index: 9999;
	visibility: visible;
}

.mail-user-checker {
    width: 100%;
    margin-top: -16px;
    float: left;
    margin-bottom: 10px;
}

.mobile-only {
	display: none;
}

.mail-option-text {
    position: absolute;
    top: 10px;
    font-size: .7em;
    left: 27px;
}

.st-selector {
	width: 170px;
}

@media screen and (max-device-width : 736px) {
	.dash-main {
		padding: 0 0;
		left: -1px;
		min-width: 0px;
	}

	.mobile-hidden {
		display: none;
	}

	.mail-panel-right {
		width: 100%;
		min-width: 0px;
	}

	.mobile-only {
		display: inline-block;
	}

	.mail-panel-left {
		width: 100%
	}
}
@stop
<? $st_mode = isset($mode) && $mode == "all" && Auth::user()->isStoryteller(); ?>
@section('dashboard-script')
	self.activeTab("{{$st_mode ? "storyteller" : "mail"}}");
	self.mailList = ko.observableArray([]);
	self.sentList = ko.observableArray([]);

	self.selectedMailbox = ko.observable("{{$st_mode ? "Storyteller Inbox" : "Inbox"}}");

	self.activeMail = ko.observable();

	self.messageRecipient = ko.observable();
	self.messageSubject = ko.observable();
	
	self.messageRecipient.subscribe(function(newVal) {
		if(newVal.trim().length == 0) {
			$(".send-mail-submit").prop('disabled', true);
			return;
		}
		$.get("/mail/user/lookup/" + newVal, function(data) {
			console.log(data.found);
			if(data.found) {
				$(".mail-user-checker").removeClass("alert");
				$(".mail-user-checker").addClass("success");
				$(".mail-user-checker").html("<i class='icon-check'></i> User found");
				$(".send-mail-submit").prop('disabled', false);
			} else {
				$(".mail-user-checker").removeClass("success");
				$(".mail-user-checker").addClass("alert");
				$(".mail-user-checker").html("<i class='icon-cancel'></i> No user found");			
				$(".send-mail-submit").prop('disabled', true);
			}
		});
	});

	self.previewBody = function(text) {
		return text.substring(0, 140);
	}

	self.activeList = ko.computed(function() {
		switch(self.selectedMailbox()) {
			case "Inbox": 
			case "Storyteller Inbox":
				return self.mailList();
			case "Outbox": return self.sentList();
		}
	});

	tinymce.init({
		selector: "#mail-message",
		plugins: "textcolor link hr image emoticons table preview fullscreen print searchreplace visualblocks code",
		toolbar1: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		toolbar2: "forecolor backcolor emoticons",
		image_advtab: true,
    	forced_root_block : '',
    	statusbar: false,
    	menubar: false
	});

	toastr.options.showMethod = 'slideDown';
	toastr.options.hideMethod = 'slideUp';
	toastr.options.positionClass = 'toast-top-full-width';

	self.showMail = function(mail) {
		if(self.selectedMailbox() == "Inbox") {
			$.ajax({
				url: "/mail/markread",
				type: 'post',
				data: {
					id: mail.id
				},
				success: function(data) {
					mail.read(true);
				}
			});
		}
		self.activeMail(mail);
	}

	$("#message-modal .close-reveal-modal").click(function() { $("#message-modal").hide() });

	self.sendMail = function() {
		$('#message-modal').hide();
		tinyMCE.triggerSave();
		$.ajax({
			url: "/mail/send",
			type: 'post',
			data: {
				to: self.messageRecipient(),
				subject: self.messageSubject(),
				body: tinyMCE.get("mail-message").getContent()
			},
			success: function(data) {

				if(data.success) {
					toastr.success(data.message);
				} else {
					toastr.error(data.message);
				}
			}
		});
	};

	self.deleteMail = function() {
		var activeMail = self.activeMail();
		$.ajax({
			url: "/mail/delete",
			type: 'post',
			data: {
				id: self.activeMail().id
			},
			success: function(data) {
				if(data.success) {
					var newIndex = self.mailList.indexOf(activeMail) + 1;
					if(self.mailList().length > newIndex) {
						self.activeMail(self.mailList()[newIndex]);
					} else {
						self.activeMail(null);
					}
					self.mailList.remove(activeMail);
					//Lazy refresh
					var mailData = self.mailList();
					self.mailList(null);
					self.mailList(mailData);
					toastr.success(data.message);
				} else {
					toastr.error(data.message);
				}
			}, 
			error: function() {
				toastr.error("An unknown error occured. Please try again later.");
			}
		});
	}

	self.showMessageModal = function(name) {
		self.messageRecipient(name);
		self.messageSubject("");
		$(".send-mail-submit").prop('disabled', true);
		$(".mail-user-checker").removeClass("alert");
		$(".mail-user-checker").removeClass("success");
		$(".mail-user-checker").html("<i class='icon-user'></i> Waiting to verify username...");
		if(tinyMCE.get("mail-message")) tinyMCE.get("mail-message").setContent("");
		$('#message-modal').show();
	}
	

	self.reply = function() {
	self.messageRecipient(self.activeMail().from);
		self.messageSubject("RE: " + self.activeMail().title);
		tinyMCE.get("mail-message").setContent("<blockquote>" + self.activeMail().body + "</blockquote><p></p>");
		$('#message-modal').show();
	}
	<? 
	$user = Auth::user();
	if($st_mode) {
		$inbox_results = ForumMail::orderBy('created_at', 'desc')->get();
		$outbox_results = ForumMail::whereNotNull("from_id")->orderBy('created_at', 'desc')->get();
	} else {
		$inbox_results = $user->mail()->orderBy('created_at', 'desc')->get();
		$outbox_results = ForumMail::where('from_id', $user->id)->orderBy('created_at', 'desc')->get();
	}
	?>
	@foreach($inbox_results as $mail)
		self.mailList.push({
			id: {{$mail->id}}, 
			title: "{{{$mail->title}}}", 
			from: "{{$mail->from()}}", 
			time: "{{$mail->created_at->diffForHumans()}}", 
			time_full: "{{$mail->created_at->format('l, F jS Y \a\t g:i A')}}", 
			from_id: "{{$mail->from_id}}", 
			read: ko.observable({{$mail->read() == 1 ? 'true' : 'false'}}), 
			body: {{json_encode(nl2br($mail->body))}} 
		});
	@endforeach

	@foreach($outbox_results as $mail)
		self.sentList.push({
			id: {{$mail->id}}, 
			title: "{{{$mail->title}}}",
			from: "{{$mail->from()}}", 
			to: "{{$mail->to->username}}", 
			time: "{{$mail->created_at->diffForHumans()}}", 
			time_full: "{{$mail->created_at->format('l, F jS Y \a\t g:i A')}}",
			from_id: "{{$mail->from_id}}", read: ko.observable({{$mail->read() == 1 ? 'true' : 'false'}}), 
			body: {{json_encode(nl2br($mail->body))}} 
		});
	@endforeach
	
	@if(Input::get("mailto") != null)
	self.showMessageModal("{{Input::get("mailto")}}");
	@endif
	
@stop
@section('dashboard-content')
<div id="message-modal" class="reveal-modal"aria-labelled by="messageModalTitle" aria-hidden="true" role="dialog">
	<h2 id="messageModalTitle">New Message</h2>
  
	<input type="text" class="message-to" placeholder="To" data-bind="value: $root.messageRecipient, " />
	<div class="mail-user-checker label">
		<i class="icon-user"></i> 
		Waiting to verify username...
	</div>
  
	<input type="text" class="message-title" placeholder="Subject" data-bind="value: $root.messageSubject" />
	<textarea class="message-body" id="mail-message" placeholder="Type your message here..."></textarea>

	<hr>
	<button class="button small right send-mail-submit" data-bind="click: $root.sendMail" disabled>Send</button>
	<a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div class="mail-panel-left" data-bind="css: {'mobile-hidden': $root.activeMail}">
	<div class="mail-options mail-left-options">
		@if($st_mode)
			<select class="mailbox-selector st-selector" data-bind="value: $root.selectedMailbox">
				<option value="Inbox">All</option>
				<option value="Outbox">No System Messages</option>
			</select>		
		@else
			<select class="mailbox-selector" data-bind="value: $root.selectedMailbox">
				<option value="Inbox">Inbox</option>
				<option value="Outbox">Outbox</option>
			</select>
		@endif
		<a class="mail-option mark-all-read-option" href="/mail/markallread" title="Mark all read" 
		   data-bind="visible: $root.selectedMailbox() == 'Inbox'">
			<i class="icon-box"></i>
		</a>
	</div>
	<div class="mail-listing" data-bind="foreach: $root.activeList">
		<div class="mail-item" data-bind="click: $root.showMail, css: { 'active': $root.activeMail() == $data }">
			<div class="mail-read" data-bind="css: {'unread': !read()}"></div>
			<div class="mail-sender" 
				data-bind="text: $root.selectedMailbox().indexOf('Inbox') !== -1 ? $data.from : $data.to">
			</div>
			<div class="mail-time" data-bind="text: time"></div>
			<div class="mail-title" data-bind="html: title"></div>
			<div class="mail-preview" data-bind="html: $root.previewBody(body)"></div>
		</div>
	</div>
</div>

<div class="mail-panel-right">
	<div class="mail-options">
		<a class="mail-option mobile-only" href="#" title="Back" data-bind="click: function() { $root.activeMail(null) }">
			<i class="icon-left"></i>
			<span class="mail-option-text">Back</span>
		</a>
		<a class="mail-option" href="#" title="Compose new message" data-bind="click: function() { $root.showMessageModal('') }">
			<i class="icon-plus"></i>
		</a>		
		<a class="mail-option" href="#" title="Reply to message" 
		   data-bind="css: {'disabled': !$root.activeMail()}, click: $root.reply, visible: $root.selectedMailbox() == 'Inbox'">
			<i class="icon-reply"></i>
		</a>
		<a class="mail-option" href="#" title="Trash this message" 
		   data-bind="css: {'disabled': !$root.activeMail()}, click: $root.deleteMail, visible: $root.selectedMailbox() == 'Inbox' ">
			<i class="icon-trash"></i>
		</a>		
	</div>
	<div class="displaying-mail" data-bind="with: $root.activeMail">
		<h3 data-bind="text: title"></h3>
		<div class="displaying-mail-from" data-bind="text: $root.selectedMailbox() == 'Inbox' ? 'From ' + from : 'To ' + to"></div>		
		<div class="displaying-mail-time" data-bind="text: time + ' at ' + time_full"></div>
		<hr>
		<div class="displaying-mail-body" data-bind="html: body"></div>
	</div>
	<div class="no-mail" data-bind="if: !$root.activeMail()">
		<div class="no-mail-icon">
			<i class="icon-mail"></i><br>
			No mail selected.
		</div>
	</div>
</div>
@stop
@stop