@extends('dashboard')
@section('dashboard-script')
	self.activeTab("characters");
@stop
@section('dashboard-content')
<? $character = Character::find($character_id); ?>
<div class="row left">
	<div class="small-12 columns">
		<h2 class="character-title">Lores</h2>
		@foreach($character->backgrounds->whereHas('definition', function($q) { $q->where('group', 'Lores'); })->get() as $category)
			<h4>{{$category->definition->name}}</h4>
			@for($i = 1; $i <= $category->amount; $i++)
				<b>{{$i}}</b>
				<ul>
					@foreach(RulebookLore::where(['background_id' => $category->definition->id, 'level' => $i])->get() as $item)
					<li>{{stripslashes($item->description)}}</li>
					@endforeach
				</ul>
			@endfor
		@endforeach	
	</div>
</div>

@stop
@stop