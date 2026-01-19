<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateTask;
use App\Http\Requests\CreateTask;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class taskcontroller extends Controller{
    public function index(Request $request):AnonymousResourceCollection{
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return TaskResource::collection($request->user()->tasks()->simplePaginate(10));
    }

    public function store(CreateTask $request){
        $validated = $request->validated();
        $task = $request->user()->tasks()->create($validated);
        return new TaskResource($task);
    }

    public function update(UpdateTask $request, $task_id){
        $validated = $request->validated();
        $task = Task::findOrFail($task_id);
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update(['state' =>$validated['state']]);
         return  response()->json(['message' => 'Task Updated'], 201);
    }

    public function destroy(Request $request,  $task_id){
        $task = Task::findOrFail($task_id);
        if ($request->user()->id !== $task->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}