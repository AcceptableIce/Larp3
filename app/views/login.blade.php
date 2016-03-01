@extends('layout')
@section('title', 'Login')
@section('includes') 
<style type="text/css">

.name-input {
	font-size: 22px;
	width: 200px;
}

.form-holder {
	width: 400px;
	margin: 0 auto;
	position: relative;
}

.login-subtitle {
	font-size: 3.0em;
	margin-top: 40px;
	margin: 0 auto;
	color: #333;
	text-align: center;
	width: 40%;
}

.form-holder label {
	color: #333;
	text-align: left;
	width: 200px;
	font-size: 1.5em;
	display: block;
	float: left;
}

.popover-content {
	overflow-y: auto;
	height: 310px;
}

.form-holder input:-webkit-autofill {
    -webkit-box-shadow: 0 0 0 1000px #fff inset;
}

.form-holder input[type=text], .form-holder input[type=password] {
	border: none;
	width: 400px;
	height: 50px;
	outline: none;
	border-bottom: 1px solid #ccc;;
	font-size: 2.0em;
	color: #000 !important;
	font-family: Lato, Helevetica, sans-serif;
	padding: 5px 5px;
	box-sizing: border-box;
	outline: none;
	display: block;
	left: 140px;
	float: left;
	margin-bottom: 15px;
	text-align: center;
	box-shadow: none;
}

.mode-switch {
	font-size: 0.5em;
	color: #333;
	text-decoration: underline;
	cursor: pointer;
	text-align: right;
}

.form-holder button[type=submit] {
	float: left;
	width: 50px;
	height: 50px;
	border-radius: 25px;
	position: relative;
	left: 50%;
	margin-left: -25px;
	background-color: #FFF;
	border: 4px solid #333;
	font-size: 2.0em;
	padding: 0 0;
	font-family: 'Source Sans Pro', Helvetica, Arial ,sans-serif;
	line-height: 0;
	color: #333;
	clear: both;
}

.form-holder button[type=submit]:hover {
	color: #fff;
	background-color: #333;

}

.form-holder button[type=submit]:focus {
	outline: none;
}

.error {
	margin-bottom: 5px;
	color: red;
}

.password-forgot {
	text-align: right;
	font-size: 0.8em;
	float: left;
}


</style>
@stop
@section('script')
<script type="text/javascript">
$(function() {
	function loginVM() {
		var self = this;
		<? $mode = Session::get('mode'); ?>
		self.loginMode = ko.observable({{ isset($mode) ? $mode : '0'}});
		self.setLoginMode = function(mode) {
			self.loginMode(mode);
		}
		
		self.showLoginModal = function() {
			$('#main-modal').foundation('reveal', 'open');
		}
	}
	ko.applyBindings(new loginVM());
});
</script>
@stop
@section('content')
<div id="main-modal" class="reveal-modal" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
	<h2 id="modalTitle">Forgot your password?</h2>
	<p>
		Enter your email address and we'll send you a link to reset it.
	</p>
	<form action="{{ action('RemindersController@postRemind') }}" method="POST">
		<input type="email" name="email">
		<input type="submit" class="button success" value="Send Reminder">
	</form>
	<a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<div class="existing-account" data-bind="visible: $root.loginMode() == 0">
	<div class="login-subtitle">Login to an Existing Account
		<div class="mode-switch" data-bind="click: function() { return $root.setLoginMode(1); }">
			...or create a new account.
		</div>
	</div>

	<div class="form-holder">
		{{ Form::open(array('url' => 'login', 'autocomplete' => 'off')) }}
		
			<p class="login-errors"> 
				@if (Session::has('error'))
					{{ trans(Session::get('error')) }}
				@elseif (Session::has('status'))
					An email with the password reset has been sent.
				@endif
				<div class="error">{{ $errors->first('username') }}</div>
				<div class="error">{{ $errors->first('password') }}</div>
				<div class="error">{{ $errors->first('message') }}</div>
			</p>
	
			{{ Form::label('username', 'Username') }}
			{{ Form::text('username', Input::old('username'), array('placeholder' => '')) }}
	
			{{ Form::label('password', 'Password') }}
			{{ Form::password('password') }}
			<div class="password-forgot">
				<a data-bind="click: showLoginModal">Forgot your password?</a>
			</div>
			
			<button type="submit"><i class="icon-right"></i></button>
	
		{{ Form::close() }}
	</div>

</div>
<div class="new-account" data-bind="visible: $root.loginMode() == 1">
	<div class="login-subtitle">Create a New Account
		<div class="mode-switch" data-bind="click: function() { return $root.setLoginMode(0); }">
			...or login to an existing account.
		</div>
	</div>
	<div class="form-holder">
	{{ Form::open(array('url' => 'createAccount', 'autocomplete' => 'off')) }}

		<p class="register-errors">
			<div class="error">{{ $errors->first('register_username') }}</div>
			<div class="error">{{ $errors->first('register_password') }}</div>
			<div class="error">{{ $errors->first('register_password_confirmation') }}</div>
			<div class="error">{{ $errors->first('register_email') }}</div>
	
		</p>

		{{ Form::label('register_username', 'Username') }}
		{{ Form::text('register_username', Input::old('register_username'), array('placeholder' => '')) }}

		{{ Form::label('register_password', 'Password') }}
		{{ Form::password('register_password') }}

		{{ Form::label('register_password_confirmation', 'Verify Password') }}
		{{ Form::password('register_password_confirmation') }}

		{{ Form::label('register_email', 'Email') }}
		{{ Form::text('register_email', Input::old('register_email'), array('placeholder' => '')) }}

		<button type="submit"><i class="icon-check"></i></button>
	{{ Form::close() }}
	</div>

</div>

@stop