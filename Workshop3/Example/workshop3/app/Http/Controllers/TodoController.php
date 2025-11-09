<?php
namespace App\Http\Controllers;

use App\Models\Todo;
use App\Http\Requests\StoreTodoRequest;
use Illuminate\Http\Request; // We need this for the auth() helper

class TodoController extends Controller
{

	public function index(Request $request)
	{
		$tasks = $request->user()->tasks()->get();
		return view('todo_list.tasks', ['tasks' => $tasks]);
	}


	public function create()
	{
		return view('todo_list.add_task');
	}

	public function store(StoreTodoRequest $request)
	{
		$request->user()->tasks()->create($request->validated());

		return redirect()->route('task_list');
	}
}