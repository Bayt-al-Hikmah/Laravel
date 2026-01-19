<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authcontroller;
use App\Http\Controllers\taskcontroller;
use App\Http\Controllers\usercontroller;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/register', [authcontroller::class, 'register']);
    Route::post('/auth/login', [authcontroller::class, 'login']);
});
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/tasks', [taskcontroller::class, 'index']);
    Route::post('/tasks', [taskcontroller::class, 'store']);
    Route::put('/tasks/{task_id}', [taskcontroller::class, 'update']);
    Route::delete('/tasks/{task_id}', [taskcontroller::class, 'destroy']);

    Route::get('/user', [usercontroller::class, 'index']);
    Route::patch('/user', [usercontroller::class, 'update_password']);
    Route::put('/user', [usercontroller::class, 'update_profile']);
    Route::get('/auth/logout', [authcontroller::class, 'logout']);
});