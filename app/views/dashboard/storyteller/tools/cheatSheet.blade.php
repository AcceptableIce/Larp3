<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Storyteller Cheatsheet - Carpe Noctem</title>
		<link href='http://fonts.googleapis.com/css?family=Cabin:300,400,700,400italic' rel='stylesheet' type='text/css'>
		<link href="/css/foundation.css" rel="stylesheet" type="text/css">
		<link href="/css/normalize.css" rel="stylesheet" type="text/css">
		<link href="/css/fontello.css" rel="stylesheet" type="text/css">
		<link href="/css/stCheatSheet.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<? 
			$data = File::get(app_path()."/config/cheatSheet.json");
			$settings = $data ? json_decode($data) : null;
			if(!$settings) {
				die("There is no cheat sheet definition. <a href='/dashboard/storyteller/manage/cheatsheet'>Create one now.</a>");
			}
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
				<a href="/dashboard/storyteller/manage/cheatsheet" class="hide-for-print">
					<button class="manage-link button tiny">Edit Settings</button>
				</a>
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
					@if($index % 2 == 0)
						<div class="character-row">
					@endif
					<div class="character-box">
						<b>{{$c->name}}</b>
						@if($settings->showClan) 
							[{{$c->clan()->first()->definition->name}}] 
						@endif 
						({{$c->owner->username}})<br>
						@if($settings->showPath)
							<div class="character-info">
								{{str_replace(["The Path of the", "The Path of"], "", $c->path()->first()->definition->name)}} 
								({{$c->path()->first()->virtue3}})
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
	</body>
</html>
