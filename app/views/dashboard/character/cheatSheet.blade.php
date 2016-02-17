<? $character = Character::find($character_id); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>@yield('title')</title>
		<link href='http://fonts.googleapis.com/css?family=Cabin:300,400,700,400italic' rel='stylesheet' type='text/css'>
		<link href="/css/foundation.css" rel="stylesheet" type="text/css">
		<link href="/css/normalize.css" rel="stylesheet" type="text/css">
		<link href="/css/fontello.css" rel="stylesheet" type="text/css">
		<link href="/css/site.css" rel="stylesheet" type="text/css">
		<style type="text/css">
			* {
				-webkit-print-color-adjust: exact;
			}
			
			html {
				font-size: 0.7em;
			}
			h1, h2, h3, h4, h5, h6, .character-box, .character-row, .path-box {
				font-family: "Cabin", Helvetica, Tahoma, sans-serif;
			}
			
			.character-row    { page-break-inside:avoid; page-break-after:auto }

			ul, li {
				font-size: 0.9em;
			}

			.merit-list {
				font-size: 1.0em;
				margin: 0 0;
			}

			.merit-list li {
				list-style: none;
			}

			.merit-list li:before {
				content: '-';
				display: inline-block;
				margin: 0 5px;
			}

			.path-box {
				width: 48%;
				float: left;
			}
			.path-box:nth-child(2n+1) {
				float: right;
			}

			.small-12 {
				clear: both;
			}

			.character-row {
				display: flex;
			}

			.character-box {
				width: 50%;
				vertical-align: top;
				padding: 1em 1em;
				-webkit-box-sizing: border-box;
				box-sizing: border-box;
				display: inline-block;
				float: left;
			}

			.character-row .character-box{
				border-bottom: 1px solid #c0c0c0;
			}

			.character-listing .character-row:first-of-type .character-box {
				border-top: 1px solid #c0c0c0;
			}

			.character-row .character-box:first-child {
				border-left: 1px solid #c0c0c0;
			}

			.character-row .character-box {
				border-right: 1px solid #c0c0c0;
			}


			.character-row:nth-child(2n) .character-box:nth-child(2n), .character-row:nth-child(2n+1) .character-box:nth-child(2n+1) {
				background-color: #f0f0f0;
			}

			.description {
				font-size: 0.9em;
				padding-left: 25px;
				margin-top: -3px;
				margin-bottom: 2px;
				line-height: 12px;
			}

			.manage-link {
				position: absolute;
				left: 250px;
			}
			.red, .red:hover {
				background-color: rgba(255, 0, 0, 0.2);
			}
			.blue, .blue:hover {
				background-color: rgba(0, 0, 255, 0.2);
			}
			.yellow, .yellow:hover {
				background-color: rgba(255, 255, 0, 0.4);
			}
			.orange, .orange:hover {
				background-color: rgba(255, 128, 0, 0.4);
			}
			.pink, .pink:hover {
				background-color: rgba(255, 0, 255, 0.3);
			}
			.green, .green:hover {
				background-color: rgba(0, 255, 0, 0.3);
			}
			.purple, .purple:hover {
				background-color: rgba(128, 0, 128, 0.3);
			}
			.none, .none:hover {
				background-color: rgba(255, 255, 255, 0);
			}
			@media print {
				.character-row:nth-child(2n) .character-box:nth-child(2n), .character-row:nth-child(2n+1) .character-box:nth-child(2n+1) {
					background-color: #f0f0f0 !important;
					-webkit-print-color-adjust: exact;
				}
				.small-6 {
				    width: 50%;
				}
			}



		</style>
	</head>
	<body>			
		<? $elders = $character->elderPowers()->get(); ?>
		
		<div class="row left">
			<div class="small-12">
				<h2>Disciplines</h2>
				@foreach($character->disciplines($version)->with('definition')->get()->sortBy('definition.name') as $discipline)
					<? $path = $discipline->path_id ? RulebookDisciplinePath::find($discipline->path_id) : null; 
						$ranks = $path ? $path->ranks()->get() : $discipline->definition->ranks()->get();
					?>
					<h4>
						{{$discipline->definition->name}}@if($discipline->path_id): 
						<span class="discipline-path-name">{{$path->name}}</span>@endif
					</h4>
					@for($i = 0; $i < $discipline->ranks; $i++)
						<b>{{$ranks[$i]->name}}</b><br>
						{{$ranks[$i]->description}}<br>
					@endfor
					@foreach($elders as $elder)
						@if($elder->definition->discipline_id == $discipline->definition->id)
							<b>{{$elder->definition->name}}</b><br>
							{{$elder->definition->description}}<br>
						@endif
					@endforeach
					<hr>
				@endforeach
				@if($character->rituals($version)->count() > 0)				
					<h2>Rituals</h2>
					@foreach($character->rituals($version)->with('definition')->get()->sortBy('definition.name') as $ritual)
						<b>{{$ritual->definition->name}}</b><br>
						{{$ritual->definition->description}}<br>
					@endforeach
				@endif
				@if($character->merits($version)->count() > 0)
					<h2>Merits</h2>
					@foreach($character->merits($version)->with('definition')->get()->sortBy('definition.name') as $merit)
						<b>{{$merit->definition->name}}</b><br>
						{{$merit->definition->description}}<br>
					@endforeach
					<hr>
				@endif
				@if($character->flaws($version)->count() > 0)
					<h2>Flaws</h2>
					@foreach($character->flaws($version)->with('definition')->get()->sortBy('definition.name') as $flaw)
						<b>{{$flaw->definition->name}}</b><br>
						{{$flaw->definition->description}}<br>
					@endforeach
					<hr>
				@endif
				
				@if($character->derangements($version)->count() > 0)				
					<h2>Derangements</h2>
					@foreach($character->derangements($version)->with('definition')->get()->sortBy('definition.name') as $derangement)
						<b>{{$derangement->definition->name}}</b><br>
						{{$derangement->definition->description}}<br>
					@endforeach
				@endif
			</div>
		</div>
		<script src="/js/jquery-1.11.2.min.js"></script>
		<script src="/js/knockout.js"></script>
		<script src="/js/foundation.min.js"></script>
		<script src="/js/vendor/modernizr.js"></script>
		<script>  $(document).foundation(); //Initialize Foundation </script>
		@yield('script')
	</body>
</html>
