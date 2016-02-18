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
	</head>
	<body>
		<? echo View::make('partials/characterSheet', ['character' => Character::find($character_id), 'version' => $version])->render(); ?>	
	</body>
</html>


