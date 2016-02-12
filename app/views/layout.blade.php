<? $authed = Auth::check(); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
		<title>@yield('title') - Carpe Noctem</title>
		<link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,400italic,700italic' rel='stylesheet' type='text/css'>
		<link href="/css/normalize.css" rel="stylesheet" type="text/css">
		<link href="/css/fontello.css" rel="stylesheet" type="text/css">	
		<link href="/css/foundation.css" rel="stylesheet" type="text/css">		
		<link href="/css/responsive-tables.css" rel="stylesheet" type="text/css">
		<link href="/css/toastr.min.css" rel="stylesheet" type="text/css">
		<link href="/css/site.css" rel="stylesheet" type="text/css">
				@yield('includes')

		<?
		if($authed) {
			$user = Auth::user();
			$theme = $user->getSettingValue("Theme");
			if($theme) echo "<link href='/css/themes/$theme.css' rel='stylesheet' type='text/css'>";
		}
		?>
	</head>
	<body>
		<nav id="primary-nav" class="top-bar" data-topbar role="navigation">
			<ul class="title-area">
				<li class="name"><h1><a href="/">Carpe Noctem</a></h1></li>
				<li class="toggle-topbar menu-icon"><a href="#"><span></span></a></li>
			</ul>
			<section class="top-bar-section">
				<ul class="left">
					<li><a href="/forums">Forums</a></li>
					<li><a href="/roster">Roster</a></li>	
					<li><a href="/calendar">Calendar</a></li>	
					<li><a href="/handbook">Handbook</a></li>
					<li><a href="/influence">Influences</a></li>					
					@if(!$authed)<li><a href="/contact">Contact the Storytellers</a></li>@endif
				</ul>
				@if($authed)
				 <ul class="right">
				 	<li data-tooltip class="hide-for-small" title="Report Bug"><a href="/forums/35/post"><i class="icon-bug"></i></a></li>
		      		<li class="has-dropdown">
		        		<a href="#">Hello, {{@Auth::user()->username}}
		        		<? $unread = Auth::user()->unreadMail()->count(); ?>
						@if($unread > 0) <label class="label round radius success topbar-mail-alert">{{$unread}}</label> @endif
	</a>
		        		<ul class="dropdown">
		          			<li><a href="/dashboard">Dashboard</a></li>
		          			<li><a href="/logout">Logout</a></li>
		        		</ul>
		     		</li>
		    	</ul>
		    	@else
		    	<ul class="right">
		    		<li><a href="/login">Login</a></li>
		    	</ul>
		    	@endif
			</section>
		</nav>
		<div class="main">
			@yield('content')
		</div>
		@if($authed && Auth::user()->hasPermission('View Debug Data')) <div class="page-build-time">Page built in {{round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"])*1000, 3)}}ms</div>@endif
		<script src="/js/jquery-1.11.2.min.js"></script>
		<script src="/js/tinymce/tinymce.min.js"></script>
		<script src="/js/toastr.min.js"></script>
		<script src="/js/knockout.js"></script>
		<script src="/js/foundation.min.js"></script>
		<script src="/js/responsive-tables.js"></script>
		<script src="/js/vendor/modernizr.js"></script>
		<script> $(document).foundation(); //Initialize Foundation </script>
		@yield('script')
	</body>
</html>
