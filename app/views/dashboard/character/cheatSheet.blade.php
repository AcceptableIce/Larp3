<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Character Reference for {{$character->name}} - Carpe Noctem</title>
		<link href='http://fonts.googleapis.com/css?family=Cabin:300,400,700,400italic' rel='stylesheet' type='text/css'>
		<link href="/css/foundation.css" rel="stylesheet" type="text/css">
		<link href="/css/normalize.css" rel="stylesheet" type="text/css">
		<link href="/css/fontello.css" rel="stylesheet" type="text/css">
		<link href="/css/characterSheet.css" rel="stylesheet" type="text/css" />
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
	</body>
</html>
