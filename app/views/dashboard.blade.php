@extends('layout')
@section('title', 'Dashboard')
@section('includes') 
<style type="text/css">
.builder-nav {
	overflow-y: auto;
	height: calc(100% - 45px) !important;
	top: 45px;
	height: 100%;
	position: absolute;
}

body {
	overflow-y: hidden;
}

.dash-main {
	width: calc(100% - 110px);
	height: calc(100% - 45px);
	position: absolute;
	top: 45px;
	left: 107px;
	overflow-y: auto;
	box-sizing: border-box;
	padding: 10px 20px;
}

h2.character-title {
	display: inline-block;
}
.button.new-character {
	padding-left: 1.25rem;
	clear: right;
	left: 40px;
	top: -5px;
	position: relative;
}

.sub-nav {
	margin: 0 0;
}

table tr td .button {
	margin-bottom: 0 !important;
}

@yield('dashboard-style');

</style>
@stop
@section('script')
<script type="text/javascript">
var dashboardVM = function() {
	var self = this;
	self.activeTab = ko.observable("characters");

  	//Manage Characters
	@yield('dashboard-script');

	return self; 
}

ko.applyBindings(new dashboardVM());
</script>
@stop
@section('content')
<div class="icon-bar vertical five-up builder-nav">
	<a class="item" data-bind="css: { 'active': $root.activeTab() == 'mail' }" href="/dashboard/mail">
		<? $unread = Auth::user()->unreadMail()->count(); ?>
		@if($unread > 0) 
			<label class="label round radius success">{{$unread}}</label> 
		@endif
		<i class="icon-mail"></i>
		<label class="hide-for-small">Mail</label>
	</a>
	<a class="item" data-bind="css: { 'active': $root.activeTab() == 'characters' }" href="/dashboard/characters">
		<i class="icon-users"></i>
		<label class="hide-for-small">Characters</label>
	</a>
	<a class="item" data-bind="css: { 'active': $root.activeTab() == 'todo' }" href="/dashboard/todo">
		<? $todo = ForumTopicReminder::where('user_id', Auth::user()->id)->count(); ?>
		@if($todo > 0) 
			<label class="label round radius warning">{{$todo}}</label> 
		@endif
		<i class="icon-list"></i>
		<label class="hide-for-small">To-do List</label>
	</a>
	@if(Auth::user()->isStoryteller())
		<a class="item" data-bind="css: { 'active': $root.activeTab() == 'storyteller' }" href="/dashboard/storyteller">
			<i class="icon-tools"></i>
			<label class="hide-for-small">Storyteller<br>Tools</label>
		</a>
	@endif
	<a class="item" data-bind="css: { 'active': $root.activeTab() == 'settings' }" href="/dashboard/settings">
		<i class="icon-cog"></i>
		<label class="hide-for-small">Settings</label>
	</a>
</div>
<div class="dash-main">
	@yield('dashboard-content')
</div>
@stop