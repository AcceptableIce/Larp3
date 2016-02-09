@extends('dashboard/storyteller')
@section('title', 'Manage Sessions')
@section('storyteller-content')
<div class="row left">
	<h2>Upcoming Sessions</h2>
	<table>
		<thead>
			<th>Session ID</th>
			<th>Date</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(GameSession::where('date', '>=', new DateTime)->get() as $d)
			<tr>
				<td>{{$d->id}}</td>
				<td>{{$d->date}}</td>
				<td>
					<form method="post" action="/dashboard/storyteller/manage/sessions/delete">
						<input type="hidden" name="id" value="{{$d->id}}" />
						<input type="submit" class="button small alert" value="Delete Session" />
					</form>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/manage/sessions/create" class="panel">
		<h4>Add Session</h5>
		<label>Session Date (MM/DD/YYYY)</label>
		<input type="text" name="date" />
		<input type="submit" class="button small" value="Add Session" />
	</form>
</div>
<div class="row left">
<h2>Past Sessions</h2>
	<table>
		<thead>
			<th>Session ID</th>
			<th>Date</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(GameSession::where('date', '<', new DateTime)->get() as $d)
			<tr>
				<td>{{$d->id}}</td>
				<td>{{$d->date}}</td>
				<td>
					<form method="post" action="/dashboard/storyteller/manage/sessions/delete">
						<input type="hidden" name="id" value="{{$d->id}}" />
						<input type="submit" class="button small alert" value="Delete Session" />
					</form>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@stop
@stop