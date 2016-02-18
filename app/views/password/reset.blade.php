@extends('layout')
@section('title', 'Reset Password')
@section('content') 
<div class="row">
	<div class="small-12 columns">
		<h2>Reset Password</h2>
		@if(Session::has('error'))
			{{ Session::get('error') }}
		@endif 
		<form action="{{ action('RemindersController@postReset') }}" method="POST">
			
			<input type="hidden" name="token" value="{{ $token }}">
		    <label for="email">
		    	Email
		    	<input type="email" name="email" id="email">
		    </label>
		    <label for="password">
				Password
				<input type="password" name="password">
		    </label>
		    <label for="password_confirmation">
		    	Confirm Password
		    	<input type="password" name="password_confirmation">
		    </label>
		    <input type="submit" class="button submit" value="Reset Password">
		</form>
	</div>
</div>
@stop