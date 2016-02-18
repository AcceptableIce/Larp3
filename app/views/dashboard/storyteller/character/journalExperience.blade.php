@extends('dashboard/storyteller')
@section('title', 'Journal Experience')
@section('storyteller-content')
<? 
$months = [];
for ($i = 8; $i >= 0; $i--) {
    $months[] = strtotime( date( 'Y-m-01' )." -$i months");
} 
?>

<div class="row left">
	<h2>Journal Experience</h2>
	<p>
		Clicking on one of buttons below will award experience for that month.
		Any box with a checkmark in it has already been awarded.<br> 
		<b>Journal experience cannot be taken back.</b>
	</p>
	<table class="journal-grid responsive">
		<thead>
			<th></th>
			@foreach($months as $m)
				<th>{{date("m/y", $m)}}</th>
			@endforeach
		</thead>
		<tbody>
			@foreach(Character::activeCharacters()->orderBy('name')->get() as $c)
				<tr>
					<td>
						{{$c->name}}
					</td>
					@foreach($months as $m) 
						<td>
							@if(CharacterJournalExperience::where('character_id', $c->id)
								->whereRaw('MONTH(date) = ?', [date('m', $m)])
								->whereRaw('YEAR(date) = ?', [date('Y', $m)])->exists())
								<i class="icon-check"></i>
							@else
								<form action="/dashboard/storyteller/experience/journal/award" method="post">
									<input type="hidden" name="month" value="{{$m}}" />
									<input type="hidden" name="id" value="{{$c->id}}" />
									<label for="submit{{$c->id}}-{{$m}}" class="button tiny submit-journal success">
										<i class='icon-plus'></i>
									</label>
									<input id="submit{{$c->id}}-{{$m}}" type="submit" value="Submit" class="hidden" />
								</form>
							@endif
						</td>
					@endforeach
				</tr>
			@endforeach
		</tbody>
	</table>

</div>
@stop
@stop