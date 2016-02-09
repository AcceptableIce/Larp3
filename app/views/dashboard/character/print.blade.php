<!DOCTYPE html>

<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Character Sheet for {{Character::find($character_id)->name}} - Carpe Noctem</title>
		<link href='http://fonts.googleapis.com/css?family=Cabin:300,400,700,400italic' rel='stylesheet' type='text/css'>
		<link href="/css/foundation.css" rel="stylesheet" type="text/css">
		<link href="/css/normalize.css" rel="stylesheet" type="text/css">
		<link href="/css/fontello.css" rel="stylesheet" type="text/css">
		<style type="text/css">
			* {
				font-family: Cabin, Tahoma, sans-serif !important;
			}
		</style>
		@yield('includes')
	</head>
		<? echo View::make('partials/characterSheet', ['character' => Character::find($character_id), 'version' => $version])->render(); ?>	
		<script src="/js/jquery-1.11.2.min.js"></script>
		<script src="/js/knockout.js"></script>
		<script src="/js/foundation.min.js"></script>
		<script src="/js/vendor/modernizr.js"></script>
		<script>  $(document).foundation(); //Initialize Foundation </script>
		@yield('script')
	</body>
</html>


