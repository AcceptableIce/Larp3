<? 
	$user = Auth::user(); 
	$class = Helpers::$rulebook_items[$key];
	$name = ucwords(str_replace("_", " ", $key));
?>

@extends('dashboard/storyteller')
@section('title', 'Rulebook Records - '.$name)

@section('storyteller-content')
<div class="row left">
	<div class="small-12 columns">
	  <h3>
		  <a href="/dashboard/storyteller/rulebook">&lt;</a>
			Rulebook Records for {{$name}}
		</h3>
	  <table>
		  <thead>
			  <th></th>
			  <th>Name</th>
			  <th>Created At</th>
			  <th>Updated At</th>
		  </thead>
		  <tbody>
		@foreach($class::all() as $item)
			<tr>
				<td>
					<a href="/dashboard/storyteller/rulebook/{{$key}}/{{$item->id}}">
						<i class="icon-pencil"></i>
					</a>
				</td>
				<td>
					{{$item->name}}
				</td>
				<td>
					{{$item->created_at}}
				</td>
				<td>
					{{$item->updated_at}}
				</td>
			</tr>
		@endforeach
		  </tbody>
	  </table>
	  <a href="/dashboard/storyteller/rulebook/{{$key}}/new">
		  <button class="button small">Create New</button>
	  </a>
	</div>
</div>
@stop
@stop
