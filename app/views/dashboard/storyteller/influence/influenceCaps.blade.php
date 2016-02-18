@extends('dashboard/storyteller')
@section('title', 'Influence Caps')
@section('dashboard-style') 
@endsection
@section('storyteller-script')
	self.showDeletes = ko.observable(true);
@endsection
@section('storyteller-content')

<div class="row left">
	<h2>Influence Caps</h2>
	<button class="button tiny delete-toggle" data-bind="click: function() { $root.showDeletes(!$root.showDeletes()) }, 
				   text: $root.showDeletes() ? 'Enable Deleting' : 'Disable Deleting'"></button>
	<form method="post" action="/dashboard/storyteller/influence/caps/update">
		<table class="influence-table responsive">
			<thead>
				<th>Field</th>
				<th>Capacity</th>
				<th>Delta</th>
				<th></th>
			</thead>
			<tbody>
				@foreach(InfluenceCap::with('definition')->get()->sortBy('definition.name') as $cap)
					<tr>
						<td>
							<input type="hidden" name="influences[]" value="{{$cap->id}}" />
							{{$cap->definition->name}}
						</td>
						<td>
							<input type="text" class="small-input" name="capacities[]" value="{{$cap->capacity}}" />
						</td>
						<td>
							<select name="deltas[]">
								@foreach(['+','','-'] as $v)
									<option value="{{$v}}" {{$v == $cap->delta ? 'selected' : ''}}>{{$v}}</option>
								@endforeach
							</select>
						</td>
						<td>
							<form method="post" action="/dashboard/storyteller/influence/caps/remove">
								<input type="hidden" name="delete_id" value="{{$cap->id}}" />
								<input type="submit" class="button alert tiny" value="Delete Field" data-bind="disable: $root.showDeletes" />	
							</form>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		<input type="submit" class="button small" value="Save Changes" />
	</form>
	<form method="post" action="/dashboard/storyteller/influence/caps/add" class="panel">
		<h4>Add Influence Field</h4>
		<label for="background">Background</label>
		<select name="background">
			@foreach(RulebookBackground::where('group', 'Influence')->get() as $bg)
				@if(!InfluenceCap::where('background_id', $bg->id)->exists())
					<option value="{{$bg->id}}">{{$bg->name}}</option
				>@endif
			@endforeach
		</select>
		<input type="submit" class="button small success" value="Create Field" />
	</form>
</div>
@stop
@stop