<? 
	$user = Auth::user(); 
	$class = Helpers::$rulebook_items[$key];
	$name = ucwords(str_replace("_", " ", $key));
	if(!isset($id)) {
		$id = "new";
		$object = new $class();
	} else {
		$object = $class::find($id);
	}
	$fields = $object->getFillable();
?>

@extends('dashboard/storyteller')
@section('title', 'Editing '.$object->name.' ('.$name.')');
@section('storyteller-style')
textarea {
	height: 200px;
}
@stop
@section('storyteller-content')
<div class="row left">
	<div class="small-12 columns">
	  <h3>
		  <a href="/dashboard/storyteller/rulebook/{{$key}}">&lt;</a>
		  Edit {{$id != 'new' ? $object->name: 'New'}} ({{$name}})
		</h3>
	  <form action="/dashboard/storyteller/rulebook/{{$key}}/{{$id}}/edit" method="POST">
		  @foreach(DB::select("SHOW COLUMNS FROM ".$object->getTable()) as $property)
		  	<? $fieldName = $property->Field; ?>
				@if(in_array($fieldName, $fields))
					<h5>{{ucwords(str_replace("_", " ", $fieldName))}} - {{$property->Type}}</h5>
			
					@if(strrpos($property->Type, "varchar") !== false)
						<input type="text" name="{{$property->Field}}" value="{{$id != 'new' ? $object->$fieldName : ''}}" />	
					@elseif($property->Type == "text")
						<textarea name="{{$property->Field}}">{{$id != 'new' ? $object->$fieldName : ''}}</textarea>
					@elseif(strrpos($property->Type, "tinyint") !== false)
						<div class="switch">
							<input id="{{$property->Field}}" name="{{$property->Field}}" type="checkbox" {{$id != 'new' && $object->$fieldName ? "checked" : ""}}>
							<label for="{{$property->Field}}"></label>
						</div> 
					@elseif(strrpos($property->Type, "int") !== false)
						<input type="number" name="{{$property->Field}}" value="{{$id != 'new' ? $object->$fieldName : ''}}" />
					@else
						Type not found.
					@endif
				@endif
		  @endforeach
		  <br><button type="submit" class="button medium success">Save</button>
	  </form>
	  <form action="/dashboard/storyteller/rulebook/{{$key}}/{{$id}}/delete" method="POST">
	  	<button type="submit" class="button medium alert">Delete</button>
	  </form>
	</div>
</div>
@stop
@stop
