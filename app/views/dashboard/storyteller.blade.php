
@extends('dashboard')


@section('dashboard-script')
  self.activeTab("storyteller");
  @yield('storyteller-script')
  $(document).foundation();
@stop

@section('dashboard-style')
	@yield('storyteller-style')
@stop

@section('dashboard-content')
<nav class="top-bar sticky" id="storyteller-nav" data-topbar role="navigation">
    <ul class="title-area">
    	<li class="name"></li>
		<li class="toggle-topbar menu-icon"><a href="#"><span></span></a></li>
    </ul>
    <section class="top-bar-section">
    	<ul class="left">
        	<li class="has-dropdown">
            	<a class="menuItem" href="#">Character Management</a>
				<ul class="dropdown">
					<li>
						<a href="/dashboard/storyteller/characters">List Characters</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/experience/biographies">Biography Experience</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/experience/journal">Journal Experience</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/experience/diablerie">Diablerie Experience</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/stats">Character Statistics</a>
					</li>
            	</ul>
			</li>

			<li class="has-dropdown">
				<a href="#">Session Management</a>
				<ul class="dropdown">
					<li>
						<a href="/dashboard/storyteller/session/checkin">Session Check-In</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/session/experience">Award Experience</a>
					</li>
				</ul>
			</li>
			
			<li class="has-dropdown">
				<a href="#">Influence</a>
				<ul class="dropdown">
					<li>
						<a href="/dashboard/storyteller/influence/tracker">Influence Tracker</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/influence/caps">Influence Caps</a>
					</li>
            	</ul>
			</li>
			
			<li class="has-dropdown">
            	<a href="#">Data Management</a>
				<ul class="dropdown">
					<li>
						<a href="/dashboard/storyteller/rulebook">Edit Rulebook Records</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/manage/positions">Edit Positions</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/manage/forums">Manage Forums</a>
					</li>   
					<li>
						<a href="/dashboard/storyteller/manage/forums/categories">Manage Forum Categories</a>
					</li>    
					<li>
						<a href="/dashboard/storyteller/manage/sessions">Manage Sessions</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/manage/permissions">Manage User Permissions</a>
					</li>                                    
					<li>
						<a href="/dashboard/storyteller/manage/userSettings">Manage User Settings</a>
					</li>
        </ul>
			</li>  
			
			<li class="has-dropdown">
            	<a href="#">Tools</a>
				<ul class="dropdown">
					<li>
						<a href="/dashboard/storyteller/cheatsheet">Cheat Sheet</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/manage/files">Upload Files</a>
					</li>              
					<li>
						<a href="/handbook/Storyteller%20Home">Storyteller Wiki</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/settings/application">Application Settings</a>
					</li>
					<li>
						<a href="/dashboard/storyteller/cache/clear">Clear Cache</a>
					</li>
            	</ul>
			</li>      
    	</ul>
    </section>
</nav>
@yield('storyteller-content')

@stop
@stop
