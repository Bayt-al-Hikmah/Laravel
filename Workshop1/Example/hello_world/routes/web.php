<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', [HelloController::class, 'showHello'])->name('hello');

Route::get('/hello/{name}', [HelloController::class, 'personalGreeting'])
     ->name('personal_greeting');