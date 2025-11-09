<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController; // Make sure this is imported
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
	return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// This is the group we want!
Route::middleware('auth')->group(function () {
	Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
	Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

	// --- ADD OUR TODO ROUTES IN HERE ---
	Route::get('/todo', [TodoController::class, 'index'])->name('task_list');
	Route::get('/todo/add', [TodoController::class, 'create'])->name('add_task');
	Route::post('/todo/add', [TodoController::class, 'store']);
});

require __DIR__.'/auth.php';