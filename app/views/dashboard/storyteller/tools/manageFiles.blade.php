@extends('dashboard/storyteller')
@section('title', 'Uploaded Files')
@section('dashboard-style')
	.upload-box {
		background-color: rgba(220, 220, 220, 0.5);
		height: 100px;
		border: 1px dashed #c0c0c0;
		color: #666;
		text-align: center;
		font-size: 1.5em;
		line-height: 100px;
		margin-bottom: 15px;
		box-sizing: border-box;
	}

	.file-upload-submit {
		clear: both;
	}
@stop
@section('storyteller-script')
@stop
@section('storyteller-content')
<div class="row left">
	@if($mode == "manage")
	<h2>Uploaded Files</h2>
	<table class="responsive">
		<thead>
			<th>Actions</th>
			<th>Name</th>
			<th>Read Permission</th>
			<th>Created By</th>
			<th>Last Updated</th>
		</thead>
		<tbody>
			@foreach(FileUpload::all() as $upload)
			<tr>
				<td>
					<a href="/dashboard/storyteller/manage/files/{{$upload->id}}/edit"><i class="icon-pencil"></i></a>
					<a href="/dashboard/storyteller/manage/files/{{$upload->id}}/delete"><i class="icon-trash"></i></a>
				</td>
				<td><a href="/uploads/{{$upload->url}}">{{$upload->name}}</a></td>
				<td>{{$upload->read_permission ? $upload->readPermission->name : ""}}</td>
				<td>{{$upload->createdBy->username}}</td>
				<td>{{$upload->updated_at->diffForHumans()}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<a href="/dashboard/storyteller/manage/files/new"><button class="button success">New File</button></a>
	@else
	<? $file = isset($id) ? FileUpload::find($id) : null; ?>
	<h2>Upload a New File</h2>
	<form method="post" action="/dashboard/storyteller/manage/files/upload" enctype="multipart/form-data">
	<div class="row">
		<div class="small-2 columns">
			<label for="name" class="inline right">Name</label>
		</div>
		<div class="small-10 columns">
			<input type="text" name="name" value="{{$file ? $file->name : ''}}" />
		</div>
	</div>
	<div class="row">
		<div class="small-2 columns">
			<label for="permission" class="inline right">Read Permission</label>
		</div>
		<div class="small-10 columns">
			<select name="permission">
				<option value="-1"></option>
				@foreach(PermissionDefinition::all() as $p)
				<option value="{{$p->id}}" {{$file && $file->read_permission == $p->id ? 'selected' : ''}}>{{$p->name}}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="row">
		<div class="small-2 columns">
			<label for="fileUpload" class="inline right">File</label>
		</div>
		<div class="small-10 columns">
			<input type="file" name="fileUpload" />
			@if($file)<p>Not selecting a file will keep the already-uploaded file.</p>@endif
		</div>
	</div>
	@if($file)<input type="hidden" name="file_id" value="{{$file->id}}" />@endif	
	<input type="submit" class="button success right file-upload-submit" value="Upload File" />
	</form>
	@endif
</div>
<script src="/js/dropzone.js"></script>
@stop
@stop