@extends('dashboard/storyteller')
@section('title', 'Session Check-In')

@section('storyteller-script')
	self.activeCharacters = ko.observableArray([]);
	@if(isset($session))
		@foreach(Character::activeCharacters()->orderBy('name')->get() as $c)
			@if(GameSessionCheckIn::where(['session_id' => $session->id, 'character_id' => $c->id])->count() == 0)
				self.activeCharacters.push({
					id: {{$c->id}}, 
					name: "{{{$c->name}}}", 
					player: "{{$c->owner->username}}", 
					costume: ko.observable(false) 
				}); 
			@endif
		@endforeach
	@endif

	self.checkCharacterIn = function(data) {
		$.ajax({
			url: "/dashboard/storyteller/session/checkin/{{isset($session) ? $session->id : -1}}/character",
			type: 'post',
			data: {
				id: data.id,
				costume: data.costume()
			},
			success: function(output) {
				toastr.options.showMethod = 'slideDown';
				toastr.options.hideMethod = 'slideUp';
				toastr.options.positionClass = 'toast-top-full-width';
				if(output.success) {
					toastr.success(output.message);
					self.activeCharacters.remove(data);
					var win = window.open("/dashboard/character/" + data.id + "/print",'_blank');
					if(win) {
						win.focus();
					} else {
						toastr.error("Please enable popups to view character sheets.");
					}
				} else {
					toastr.error(output.message);
				}
			}
		});
	}
@endsection
@section('storyteller-content')
<div class="row left">
	@if(isset($session))
		<h2>Check-In for {{$session->date}}</h2>
		<table class="responsive">
			<thead>
				<th>Character</th>
				<th>Player</th>
				<th>Costume?</th>
				<th></th>
			</thead>
			<tbody data-bind="foreach: $root.activeCharacters">
				<tr>
					<td data-bind="text: name"></td>
					<td data-bind="text: player"></td>
					<td>
						<div class="switch costume-switch">
							<input type="checkbox" data-bind="checked: costume, attr: {'id': id+'CostumeSwitch' }">
							<label data-bind="attr: {'for': id+'CostumeSwitch' }"></label>
						</div>
					</td>
					<td>
						<button class="button small success" data-bind="click: $root.checkCharacterIn">Check In</button>
					</td>
				</tr>
			</tbody>
		</table>
	@else
	<h2>Session Check-In</h2>
	<table>
		<thead>
			<th>Session ID</th>
			<th>Date</th>
			<th></th>
		</thead>
		<tbody>
			<? 	$now = new DateTime;
				$now->modify('yesterday midnight');
			?>
			@foreach(GameSession::where('date', '>=', $now)->get() as $d)
			<tr>
				<td>
					{{$d->id}}
				</td>
				<td>
					{{$d->date}}
				</td>
				<td>
					<a href="/dashboard/storyteller/session/checkin/{{$d->id}}">
						<button class="button small success">Check-In Session</button>
					</a>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</div>
@stop
@stop