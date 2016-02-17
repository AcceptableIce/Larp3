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
		<? 
			$data = File::get(app_path()."/config/cheatSheet.json");
			$settings = $data ? json_decode($data) : null;
			if(!$settings) die("There is no cheat sheet definition. <a href='/dashboard/storyteller/manage/cheatsheet'>Create one now.</a>");
			$characters = Character::activeCharacters()->orderBy('name')->get(); 
			$unique_paths = [];
			foreach($characters as $c) {
				if(array_search($c->path()->first()->path_id, $unique_paths) === false) $unique_paths[] = $c->path()->first()->path_id;
			}
			sort($unique_paths);

			function printMerit($merit, $description, $settings) {
				foreach($settings->merits as $m) {
					if($merit->id == $m->id) printRow($merit, $m, $description);
				}
			}

			function printFlaw($flaw, $description, $settings) {
				foreach($settings->flaws as $m) {
					if($flaw->id == $m->id) printRow($flaw, $m, $description);
				}
			}

			function printDerangement($derangement, $settings) {
				foreach($settings->derangements as $m) {
					if($derangement->id == $m->id) printRow($derangement, $m);
				}
			}

			function printRow($definition, $setting, $description = null) {
				if($setting->display == "on") {
					echo "<li class='$setting->highlight'>$definition->name";
					if($description != null) echo "<div class='description'>$description</div>";
					echo "</li>";
				}
			}


		?>
		<div class="row left">
			<div class="small-12">
				<a href="/dashboard/storyteller/manage/cheatsheet" class="hide-for-print"><button class="manage-link button tiny">Edit Settings</button></a>
				<h4>Paths and Sins</h4>
					@foreach($unique_paths as $p)
					<div class="path-box">
						<? $path = RulebookPath::find($p); ?>
						<b>{{$path->name}}</b><br>
						@foreach($path->sins() as $index => $sin)
						{{5 - ($index)}}: {{$sin}}<br>
						@endforeach
					</div>
					@endforeach
			</div>
			<div class="small-12 character-listing">
				<h4>Characters</h4>
				@foreach($characters as $index => $c)
				@if($index % 2 == 0)<div class="character-row">@endif
				<div class="character-box">
					<b>{{$c->name}}</b>
					@if($settings->showClan) [{{$c->clan()->first()->definition->name}}] @endif 
					({{$c->owner->username}})<br>
					@if($settings->showPath)
						<div class="character-info">
							{{str_replace(["The Path of the", "The Path of"], "", $c->path()->first()->definition->name)}} ({{$c->path()->first()->virtue3}})
						</div>
					@endif
					@if($settings->showGeneration)
						<? $gen = $c->backgrounds()->whereHas('definition', function($q) { $q->where('name', 'Generation'); })->first(); ?>
						<div class="character-info">Generation: {{$gen ? 13 - $gen->amount : 13}}</div>
					@endif
					@if($settings->showVentrueRestriction && $c->clan()->first()->definition->name == "Ventrue")			
						<div class="character-info">Ventrue Restriction: {{$c->clanOptions()->first()->option1}}</div>
					@endif					
					<div class="row">
						<div class="small-6 columns">
							<ul class="merit-list">
							@foreach($c->merits()->get() as $m)
								{{printMerit($m->definition, $m->description, $settings)}}
							@endforeach
							</ul>
						</div>
						<div class="small-6 columns">
							<ul class="merit-list">
							@foreach($c->flaws()->get() as $m)
								{{printFlaw($m->definition, $m->description, $settings)}}
							@endforeach
							</ul>
							@if($c->derangements()->count() > 0) 
							<ul class="merit-list">
							@foreach($c->derangements()->get() as $d)
								{{printDerangement($d->definition, $settings)}}
							@endforeach
							</ul>	
							@endif
						</div>							
					</div>
				</div>
				@if($index % 2 == 1)</div>@endif
				@endforeach
		</div>
		<script src="/js/jquery-1.11.2.min.js"></script>
		<script src="/js/knockout.js"></script>
		<script src="/js/foundation.min.js"></script>
		<script src="/js/vendor/modernizr.js"></script>
		<script>  $(document).foundation(); //Initialize Foundation </script>
		@yield('script')
	</body>
</html>
