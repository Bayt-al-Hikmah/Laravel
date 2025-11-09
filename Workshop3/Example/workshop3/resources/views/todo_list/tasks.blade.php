@extends('layouts.base')
@section('content')

<div class="container">
	<h1>My To-Do List</h1>
	<a href="{{ route('add_task') }}" class="btn">Add New Task</a>
	
	<div class="task-list">
		@forelse ($tasks as $task)
			<div class="@if ($task->done) done @endif">
				<strong>{{ $task->title }}</strong><br>
				{{ $task->description }}
			</div>
		@empty
			<div>No tasks added yet. Start by creating one!</div>
		@endforelse
	</div>
</div>
@endsection