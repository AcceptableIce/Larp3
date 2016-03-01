@extends('layout')
@section('title', 'Contact the Storytellers - Carpe Noctem')
@section('includes') 
<style type="text/css">
	.message {
		height: 200px;
	}
</style>
@stop
@section('content')
<div class="row">
	<div class="small-12 columns">
		<h1>Contact the Storytellers</h1>
		@if(isset($response))
			<p>Thank you for contacting the Storytellers. We will try to get back to you as quickly as possible.</p>
		@else
			@if(Auth::user())
			<p>Since you are already logged in, you can contact the Storytellers through any of the forums in the "Contact the Storytellers" category. 
				In these boards, only you and the Storytellers can read the messages you post.</p>
			@else
			<p>Fill out the fields below to contact the storytellers.</p>
			@foreach ($errors->all() as $error)
				{{$error}}<br>
			@endforeach
			<form method="post" action="/contact/send">
				<label for="name">Name
					<input type="text" name="name" />
				</label>
				<label for="email">Email
					<input type="text" name="email" />
				</label>			
				<label for="subject">Subject
					<input type="text" name="subject" />
				</label>
				<label for="message">Message
					<textarea type="text" name="message" class="message"></textarea>
				</label>
				<hr>
				<input type="submit" class="button success" value="Send Message" />
			</form>
			@endif
		@endif
	</div>
</div>
@stop