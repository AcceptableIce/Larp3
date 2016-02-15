@extends('dashboard/storyteller')
@section('title', 'Award Session Experience')

@section('storyteller-script')
	self.activeCharacters = ko.observableArray([]);
	@if(isset($id))
		<? $session = GameSession::find($id); ?>
		@foreach($session->checkins()->with('character')->get()->sortBy('character.name') as $checkin)
		self.activeCharacters.push(ko.observable(
			{
				id: {{$checkin->character->id}}, 
				name: "{{{$checkin->character->name}}}", 
				player: "{{$checkin->character->owner->username}}", 
				costume: ko.observable({{$checkin->costume ? 'true' : 'false'}}), 
				nomination1: ko.observable({{$checkin->nominated ? 'true' : 'false'}}), 
				nomination2: ko.observable({{$checkin->nominated_twice ? 'true' : 'false'}}), 
				override: ko.observable({{$checkin->bonus}})
			}
		)); 
		@endforeach
	@endif
	
@endsection
@section('storyteller-content')
<div class="row left">
	@if(isset($id))
		<h2>Award Experience for {{$session->date}}</h2>
		<form method="post" action="/dashboard/storyteller/session/experience/{{$id}}/award">
		<table class="responsive">
			<thead>
				<th>Character</th>
				<th>Player</th>
				<th>Costume?</th>
				<th>Nomination 1?</th>
				<th>Nomination 2?</th>
				<th>+/-</th>
				<th><b>Total</b></th>
			</thead>
			<tbody data-bind="foreach: $root.activeCharacters">
				<tr>
					<td>
						<input type="hidden" name="ids[]" data-bind="value: id" />
						<span data-bind="text: name"></span>
					</td>
					<td>
						<span data-bind="text: player"></span>
					</td>					<td>
						<div class="switch">
						  <input type="hidden" name="costumes[]" data-bind="value: costume" />
						  <input type="checkbox" data-bind="checked: costume, attr: {'id': id+'CostumeSwitch' }">
						  <label data-bind="attr: {'for': id+'CostumeSwitch' }"></label>
						</div>
					</td>
					<td>
						<div class="switch">
 						<input type="hidden" name="nom1s[]" data-bind="value: nomination1" />
						  <input type="checkbox" data-bind="checked: nomination1, attr: {'id': id+'Nom1Switch' }">
						  <label data-bind="attr: {'for': id+'Nom1Switch' }"></label>
						</div>
					</td>
					<td>
						<div class="switch">
						  <input type="hidden" name="nom2s[]" data-bind="value: nomination2" />
						  <input type="checkbox" data-bind="checked: nomination2, attr: {'id': id+'Nom2Switch' }">
						  <label data-bind="attr: {'for': id+'Nom2Switch' }"></label>
						</div>
					</td>
					<td><input type="text" name="overrides[]" class="checkin-experience-box" data-bind="value: override" /></td>
					<td style="text-align: center;" data-bind="text: (costume() ? 1 : 0) + (nomination1() ? 1 : 0) + (nomination2() ? 1 : 0) + parseInt(override(), 10) + 1"></td>
				</tr>
			</tbody>
		</table>
		<hr>
		@if(!$session->submitted)
			<input type="submit" name="save" class="button small" value="Save" /> 
			<input type="submit" class="button small success" value="Award Experience" />
		@else
		<p><b>Experience for this session has already been awarded.</b></p>
		@endif
		</form>
	@else
	<h2>Award Experience</h2>
	<table class="responsive">
		<thead>
			<th>Session ID</th>
			<th>Date</th>
			<th>Status</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(GameSession::orderBy('date', 'desc')->get() as $d)
			<tr>
				<td>{{$d->id}}</td>
				<td>{{$d->date}}</td>
				<td>{{$d->submitted ? "Awarded" : ($d->checkins->count() > 0 ? "<b>Pending Approval</b>" : "<i>Incomplete</i>") }}
				<td><a href="/dashboard/storyteller/session/experience/{{$d->id}}"><button class="button small success">Award Experience...</button></a></td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</div>
@stop
@stop