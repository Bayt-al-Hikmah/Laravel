@extends('layouts.base')
@section('content')

<div class="container">
	<h1>Add a New Task</h1>
	<form method="POST" action="{{ route('add_task') }}">
		@csrf <div>
			<label for="title">Title</label>
			<input type="text" name="title" id="title" value="{{ old('title') }}">
			@error('title')
				<div class="error-message">{{ $message }}</div>
			@enderror
		</div>
		
		<div>
			<label for="description">Description</label>
			<textarea name="description" id="description">{{ old('description') }}</textarea>
			@error('description')
				<div class="error-message">{{ $message }}</div>
			@enderror
		</div>

		<button type="submit" class="btn">Save Task</button> <a href="{{ route('task_list') }}" class="btn back">Back to List</a>
	</form>
	
</div>
@endsection