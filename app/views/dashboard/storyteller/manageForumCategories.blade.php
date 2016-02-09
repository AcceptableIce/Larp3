@extends('dashboard/storyteller')
@section('title', 'Manage Positions')
@section('storyteller-script')
	self.showDeletes = ko.observable(true);
@stop
@section('storyteller-content')
<div class="row left">
	<h2>Forum Categories</h2>
	<button class="button tiny delete-toggle" style="left: 350px;" data-bind="click: function() { $root.showDeletes(!$root.showDeletes()) }, text: $root.showDeletes() ? 'Enable Deleting' : 'Disable Deleting'"></button>
	<p>	Deleting a category will unassociate all forums which belong to it.<br>
		 Unassociated forums shouldn't show up, but it's not perfectly reliable. It's safer to mark them as trashed as well.
	</p>
	<table class="responsive">
		<thead>
			<th>Category ID</th>
			<th>Name</th>
			<th>Order</th>
			<th></th>
		</thead>
		<tbody>
			@foreach(ForumCategory::orderBy('display_order')->get() as $d)
			<tr>
				<td>{{$d->id}}</td>
				<td>{{$d->name}}</td>
				<td><form method="post" action="/dashboard/storyteller/manage/forums/categories/update">
					<input type="hidden" name="id" value="{{$d->id}}" />
					<input type="text" name="order" class="small-input category-display-order" value="{{$d->display_order}}" />
					<input type="submit" class="button save-category tiny" value="Save" />
				</form></td>
				<td><form method="post" action="/dashboard/storyteller/manage/forums/categories/remove">
						<input type="hidden" name="delete_id" value="{{$d->id}}" />
						<input type="submit" class="button alert tiny" value="Delete Category" data-bind="disable: $root.showDeletes" />	
				</form></td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<form method="post" action="/dashboard/storyteller/manage/forums/categories/create" class="panel">
		<h4>Add Category</h5>
		<label>Category Name</label>
		<input type="text" name="name" />
		<input type="submit" class="button small" value="Add Category" />
	</form>
</div>
@stop
@stop