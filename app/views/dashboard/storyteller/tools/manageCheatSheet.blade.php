@extends('dashboard/storyteller')
@section('title', 'Manage ST Cheat Sheet')
@section('dashboard-style')
	label.cheat-sheet-label {
		margin-top: -5px;
	}

	.row.merit-section {
		margin-left: 10px;
	}

	.red, .red:hover {
		background-color: #f00;
	}
	.blue, .blue:hover {
		background-color: #00f;
	}
	.yellow, .yellow:hover {
		background-color: #ff0;
	}
	.orange, .orange:hover {
		background-color: #ffa500;
	}
	.pink, .pink:hover {
		background-color: #f0f;
	}
	.green, .green:hover {
		background-color: #0f0;
	}
	.purple, .purple:hover {
		background-color: #a500a5;
	}
	.none, .none:hover {
		background-color: #fff;
	}

	.merit-color-selector {
		width: 70px;
	}
@stop
@section('storyteller-script')
var previous = null;
$(".merit-color-selector").on('click', function () {
        previous = $("option:selected", this).attr("class");
        console.log(previous);
}).change(function(){
    var color = $("option:selected", this).attr("class");
    $(this).removeClass(previous);
    $(this).addClass(color);
});
@stop
@section('storyteller-content')
<? 	$data = File::get(app_path()."/config/cheatSheet.json");
	$settings = $data ? json_decode($data) : null;
	function getMeritData($id, $settings) {
		if(!$settings || !isset($settings->merits)) return null;
		foreach($settings->merits as $s) {
			if($s->id == $id) return $s;
		}
		return null;
	}
	function getFlawData($id, $settings) {
		if(!$settings || !isset($settings->flaws)) return null;
		foreach($settings->flaws as $s) {
			if($s->id == $id) return $s;
		}
		return null;
	}
	function getDerangementData($id, $settings) {
		if(!$settings || !isset($settings->derangements)) return null;
		foreach($settings->derangements as $s) {
			if($s->id == $id) return $s;
		}
		return null;
	}	
?>
<form class="row left" method="post" action="/dashboard/storyteller/manage/cheatsheet/save">
	<h2>Manage Cheat Sheat</h2>
	<div class="row">
		<div class="small-2 columns">
			<label for="clan" class="cheat-sheet-label inline right">Display Clan</label>
		</div>
		<div class="small-10 columns">
			<div class="switch">
			  <input id="clan" name="clan" type="checkbox" {{$settings && isset($settings->showGeneration) && $settings->showClan == "on" ? "checked" : ""}}>
			  <label for="clan"></label>
			</div> 
		</div>
	</div>
	<div class="row">
		<div class="small-2 columns">
			<label for="generation" class="cheat-sheet-label inline right">Display Generation</label>
		</div>
		<div class="small-10 columns">
			<div class="switch">
			  <input id="generation" name="generation" type="checkbox" {{$settings && isset($settings->showGeneration) && $settings->showGeneration == "on" ? "checked" : ""}}>
			  <label for="generation"></label>
			</div> 
		</div>
	</div>
	<div class="row">
		<div class="small-2 columns">
			<label for="ventrue-restriction" class="cheat-sheet-label inline right">Display Ventrue Feeding Restriction</label>
		</div>
		<div class="small-10 columns">
			<div class="switch">
			  <input id="ventrue-restriction" name="ventrue-restriction" type="checkbox" {{$settings && isset($settings->showVentrueRestriction) && $settings->showVentrueRestriction == "on" ? "checked" : ""}}>
			  <label for="ventrue-restriction"></label>
			</div> 
		</div>
	</div>
	<div class="row">
		<div class="small-2 columns">
			<label for="path" class="cheat-sheet-label inline right">Display Path</label>
		</div>
		<div class="small-10 columns">
			<div class="switch">
			  <input id="path" name="path" type="checkbox" {{$settings && isset($settings->showPath) && $settings->showPath == "on" ? "checked" : ""}}>
			  <label for="path"></label>
			</div> 
		</div>
	</div>	
	<div class="row merit-section">
		<h4>Merits</h4>
		@foreach(RulebookMerit::orderBy('name')->get() as $index => $merit)
		@if($index % 2 == 0) <div class="row">@endif
		<div class="small-6 columns">
			<b>{{$merit->name}}</b>
			<? $merit_data = getMeritData($merit->id, $settings);  ?>
			<input type="hidden" name="merits-ids[]" value="{{$merit->id}}" />
			<div class="row">
				<div class="small-2 columns">
					<label for="merit-{{$merit->id}}" class="cheat-sheet-label inline right">Display</label>
				</div>
				<div class="small-4 columns">
					<div class="switch">
					  <input id="merit-{{$merit->id}}" name="merits-enabled-{{$merit->id}}" type="checkbox" {{$merit_data && $merit_data->display == "on" ? "checked" : ""}}>
					  <label for="merit-{{$merit->id}}"></label>
					</div> 
				</div>
				<div class="small-2 columns">
					<label for="merit-color-{{$merit->id}}" class="cheat-sheet-label inline right">Highlight</label>
				</div>
				<? $color_options = ["none", "blue", "red", "yellow", "orange", "purple", "green", "pink"]; ?>
				<div class="small-4 columns">
					<select id="merit-color-{{$merit->id}}" name="merits-highlights-{{$merit->id}}" class="merit-color-selector {{$merit_data ? $merit_data->highlight : ""}}">
						@foreach($color_options as $opt)
						<option value="{{$opt}}" class="{{$opt}}" {{$merit_data && $merit_data->highlight == $opt ? 'selected' : ''}}></option>
						@endforeach
					</select>
				</div>
			</div>			
		</div>
		@if($index % 2 == 1)</div>@endif
		@endforeach
	</div>
	</div>
	<div class="row merit-section">
		<h4>Flaws</h4>
		@foreach(RulebookFlaw::orderBy('name')->get() as $index => $flaw)
		@if($index % 2 == 0) <div class="row">@endif
		<div class="small-6 columns">
			<b>{{$flaw->name}}</b>
			<? $flaw_data = getFlawData($flaw->id, $settings);  ?>
			<input type="hidden" name="flaws-ids[]" value="{{$flaw->id}}" />
			<div class="row">
				<div class="small-2 columns">
					<label for="flaw-{{$flaw->id}}" class="cheat-sheet-label inline right">Display</label>
				</div>
				<div class="small-4 columns">
					<div class="switch">
					  <input id="flaw-{{$flaw->id}}" name="flaws-enabled-{{$flaw->id}}" type="checkbox" {{$flaw_data && $flaw_data->display == "on" ? "checked" : ""}}>
					  <label for="flaw-{{$flaw->id}}"></label>
					</div> 
				</div>
				<div class="small-2 columns">
					<label for="flaw-color-{{$flaw->id}}" class="cheat-sheet-label inline right">Highlight</label>
				</div>
				<? $color_options = ["none", "blue", "red", "yellow", "orange", "purple", "green", "pink"]; ?>
				<div class="small-4 columns">
					<select id="flaw-color-{{$flaw->id}}" name="flaws-highlights-{{$flaw->id}}" class="merit-color-selector {{$flaw_data ? $flaw_data->highlight : ""}}">
						@foreach($color_options as $opt)
						<option value="{{$opt}}" class="{{$opt}}" {{$flaw_data && $flaw_data->highlight == $opt ? 'selected' : ''}}></option>
						@endforeach
					</select>
				</div>
			</div>			
		</div>
		@if($index % 2 == 1)</div>@endif
		@endforeach
	</div>
	</div>
	<div class="row merit-section">
		<h4>Derangements</h4>
		@foreach(RulebookDerangement::orderBy('name')->get() as $index => $derangement)
		@if($index % 2 == 0) <div class="row">@endif
		<div class="small-6 columns">
			<b>{{$derangement->name}}</b>
			<? $derangement_data = getDerangementData($derangement->id, $settings);  ?>
			<input type="hidden" name="derangements-ids[]" value="{{$derangement->id}}" />
			<div class="row">
				<div class="small-2 columns">
					<label for="derangement-{{$derangement->id}}" class="cheat-sheet-label inline right">Display</label>
				</div>
				<div class="small-4 columns">
					<div class="switch">
					  <input id="derangement-{{$derangement->id}}" name="derangements-enabled-{{$derangement->id}}" type="checkbox" {{$derangement_data && $derangement_data->display == "on" ? "checked" : ""}}>
					  <label for="derangement-{{$derangement->id}}"></label>
					</div> 
				</div>
				<div class="small-2 columns">
					<label for="derangement-color-{{$derangement->id}}" class="cheat-sheet-label inline right">Highlight</label>
				</div>
				<? $color_options = ["none", "blue", "red", "yellow", "orange", "purple", "green", "pink"]; ?>
				<div class="small-4 columns">
					<select id="derangement-color-{{$derangement->id}}" name="derangements-highlights-{{$derangement->id}}" class="merit-color-selector {{$derangement_data ? $derangement_data->highlight : ""}}">
						@foreach($color_options as $opt)
						<option value="{{$opt}}" class="{{$opt}}" {{$derangement_data && $derangement_data->highlight == $opt ? 'selected' : ''}}></option>
						@endforeach
					</select>
				</div>
			</div>			
		</div>
		@if($index % 2 == 1)</div>@endif
		@endforeach
	</div>	
<input type="submit" class="button small success" value="Save Changes" />
</form>	
@stop
@stop