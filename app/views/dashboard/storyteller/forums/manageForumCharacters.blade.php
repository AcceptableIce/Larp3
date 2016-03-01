@extends('dashboard/storyteller')

@section('storyteller-content')
<div class="row left">
	<h2>Characters Allowed in {{$forum->name}}</h2>
	<table>
		<thead>
			<th>Character Name</th>
			<th>Player Name</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(ForumCharacterPermission::where('forum_id', $forum->id)->get() as $d)
			<tr>
				<td>
					{{$d->character->name}}
				</td>
				<td>
					{{$d->character->owner->username}}
				</td>
				<td>
					<form method="post" action="/dashboard/storyteller/manage/forum/{{$forum->id}}/character/remove">
						<input type="hidden" name="character" value="{{$d->character->id}}" />
						<input type="submit" class="button small alert" value="Remove Permission" />
					</form>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/manage/forum/{{$forum->id}}/character/add" class="panel">
		<h4>Grant Permission</h4>
		<label>Character Name
			<select name="character">
				@foreach(Character::activeCharacters()->whereHas("permittedForums", function($q) use ($forum) { 
					$q->where('forum_id', $forum->id); 
				}, '=', 0)->orderBy('name')->get() as $character)
					<option value="{{$character->id}}">
						{{$character->name}} ({{$character->owner->username}})
					</option>
				@endforeach
			</select>
		</label>
		
		<input type="submit" class="button small" value="Grant" />
	</form>
</div>
@stop
@stop